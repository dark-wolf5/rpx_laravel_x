<?php

namespace App\Http\Controllers\Feedback;

use Auth;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validatedData = $request->validate([
            'feedback_text' => 'required|string|max:1500|min:100',
            'ledger_id' => 'required|exists:loyalty_point_ledger,id'
        ]);

        $user = Auth::user();

        Feedback::create([
            'user_id' => $user->id,
            'feedback_text' => $validatedData['feedback_text'],
            'ledger_record_id' => $validatedData['ledger_id'],
        ]);

        $feedback = ["feedback" => Feedback::where('ledger_record_id', $validatedData['ledger_id'])->first()];

        return response($feedback, 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \App\Models\Feedback $feedback
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Feedback $feedback, Request $request)
    {
        $validatedData = $request->validate([
            'feedback_text' => 'required|string|max:1500|min:100',
        ]);

        $feedback->update([
            'feedback_text' => $validatedData['feedback_text']
        ]);
        $feedback->refresh();

        return response($feedback, 204);
    }

    public function show(Feedback $feedback) {
        return response($feedback, 200);
    }

    public function index(Request $request) {
        $user = Auth::user();

        $feedbackList = $user->business->feedback()->with('user')->orderBy('id', 'DESC')->paginate(20);

        return response($feedbackList, 200);
    }
}
