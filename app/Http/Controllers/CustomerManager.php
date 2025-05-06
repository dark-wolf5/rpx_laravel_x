<?php

namespace App\Http\Controllers;

use App\Models\PromotionMessage;
use Auth;
use App\Mail\BusinessPromotional;
use App\Models\Email;
use App\Models\EmailGroup;
use App\Models\Sms;
use App\Models\SmsGroup;
use App\Jobs\SendMassSms;
use App\Models\Business;
use App\Models\LoyaltyPointBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerManager extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $business = $user->business()->first();
        $customerList = LoyaltyPointBalance::where('from_business', $business->id)->with('user', function ($query) use ($business) {
            $query->join('redeemable_items', 'users.id', '=', 'redeemable_items.redeemer_id')
                ->select("users.*", DB::raw('SUM(redeemable_items.total_spent) as total_spent_sum'))
                ->where('redeemable_items.business_id', $business->id)
                ->whereNull('redeemable_items.reward_id')
                ->groupBy('redeemable_items.redeemer_id');
        })->paginate(20);

        return response($customerList);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Send recent guests of the business an SMS message.
     */
    public function sms(Sms $sms, Request $request)
    {
        $validated = $this->validate($request, [
            "sms" => 'required|string|max:320|min:20',
        ]);

        $smsText = $validated['sms'];

        $user = Auth::user();

        $smsGroup = new SmsGroup;
        $smsGroup->body = $smsText;
        $smsGroup->from_id = $user->rpxUser()->first()->id;
        $smsGroup->save();

        try {
            $user->business()->with('recentGuests')->each(function (Business $business) use ($smsText, $sms, $request, $smsGroup) {
                $smsGroup->total = $business->recentGuests->count();
                $smsGroup->save();

                $business->recentGuests->each(function (LoyaltyPointBalance $lpBalance) use ($smsText, $sms, $request, $business, $smsGroup) {
                    $user = $lpBalance->user()->first();
                    $rpxUser = $user->rpxUser()->first();
                    $phoneNumber = $rpxUser->phone_number;
                    if (! is_null($phoneNumber) && $rpxUser->sms_opt_in === 1) {
                        $sms = $sms->createNewSms($user, $business, $smsGroup);
                        SendMassSms::dispatch($user, $business->name, $sms, $smsGroup, $business->from_number)
                            ->onQueue(config('rpx.sms.queue'));
                    } else {
                        Log::info(
                            "[CustomerManager]-[SendMassSms]: Message Failed" .
                            ", User ID: " . $user->id .
                            ", Business: " . $business->name .
                            ", Opted-in: " . $rpxUser->sms_opt_in .
                            ", Error: No User Phone Number"
                        );
                    }
                });
            });

            return response([
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            Log::info(
                '[CustomerManager]-[sendSms]: User ID: '. $user->id .
                ', Error: ' . $e->getCode() .
                ', Message: ' . $e->getMessage() .
                ', Could not send message.'
            );

            return response([
                'success' => false,
                'error' => $e->getCode(),
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Retrieve group of SMS sent to recent guests.
     */
    public function smsGroupList(SmsGroup $smsGroup, Request $request) {
        $user = Auth::user();

        $smsList = SmsGroup::where('from_id', $user->business()->first()->id)->orderBy('id', 'DESC')->paginate(20);

        return response($smsList);
    }

    public function getPromotion(Request $request) {
        $user = Auth::user();
        $business = $user->business;

        $pm = PromotionMessage::where('business_id', $business->id)->first();
        if (is_null($pm)) {
            $pm = new PromotionMessage();
        }

        return response($pm, 200);
    }

    public function sendPromotion(Request $request) {
        $validated = $request->validate([
            'message' => 'required|string'
        ]);

        $user = Auth::user();
        $business = $user->business;

        $promotionMessage = PromotionMessage::where('business_id', $business->id)->first();
        if (!is_null($promotionMessage)) {
            $promotionMessage->message = $validated['message'];
            $promotionMessage->save();
        } else {
            $promotionMessage = new PromotionMessage();
            $promotionMessage->message = $validated['message'];
            $promotionMessage->business_id = $business->id;
            $promotionMessage->save();
        }

        return response($promotionMessage, 200);
    }

    /**
     * Send recent guests of the business an e-mail message.
     */
    public function email(Request $request)
    {
        $validated = $this->validate($request, [
            "email_body" => 'required|string|max:1200|min:100',
        ]);

        $emailBody = $validated['email_body'];

        $user = Auth::user();

        $emailGroup = new EmailGroup;
        $emailGroup->email_body = $emailBody;
        $emailGroup->from_id = $user->rpxUser()->first()->id;
        $emailGroup->save();

        try {
            $user->business()->with('recentGuests')->each(function (Business $business) use ($emailBody, $request, $emailGroup) {
                $emailGroup->total = $business->recentGuests->count();
                $emailGroup->save();

                $business->recentGuests->each(function (LoyaltyPointBalance $lpBalance) use ($emailBody, $request, $business, $emailGroup) {
                    $user = $lpBalance->user()->first();
                    $userEmail = $user->email;
                    $businessLink = $this->getBusinessLink($business);
                    if (! is_null($userEmail)) {
                        $email = Email::createNewEmail($user, $business, $emailGroup);
                        $businessPromotional = (
                            new BusinessPromotional(
                                $user->email,
                                $user->id,
                                $user->rpxUser->first_name,
                                $business->name,
                                $emailGroup->email_body,
                                $businessLink,
                                $email,
                                $emailGroup
                            ))->onConnection('redis')
                              ->onQueue('email.miami.fl.1');

                        Mail::to($userEmail)
                            ->queue($businessPromotional);
                    } else {
                        Log::info(
                            "[CustomerManager]-[SendMassEmail]: Message Failed" .
                            ", User ID: " . $user->id .
                            ", Business: " . $business->name .
                            ", Error: No User Email"
                        );
                    }
                });
            });

            return response([
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            Log::info(
                '[CustomerManager]-[sendEmail]: User ID: '. $user->id .
                ', Error: ' . $e->getCode() .
                ', Message: ' . $e->getMessage() .
                ', Could not send message.'
            );

            return response([
                'success' => false,
                'error' => $e->getCode(),
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Retrieve group of SMS sent to recent guests.
     */
    public function emailGroupList(EmailGroup $emailGroup, Request $request) {
        $user = Auth::user();

        $emailList = EmailGroup::where('from_id', $user->business()->first()->id)->orderBy('id', 'DESC')->paginate(20);

        return response($emailList);
    }

    public function getBusinessLink(Business $business) {
        $type = 0;
        switch ($business->rpxUser->user_type) {
            case 1:
                $type = 'place-to-eat';
                break;
            case 2:
                $type = 'shopping';
                break;
            case 3:
                $type = 'events';
                break;
        }

        return 'https://rpx.com/'.$type.'/'.$business->slug.'/'.$business->id;
    }
}
