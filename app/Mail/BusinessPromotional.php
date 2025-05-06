<?php

namespace App\Mail;

use App\Models\Email;
use App\Models\EmailGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BusinessPromotional extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public string $userEmail,
        public string $userID,
        public string $firstName,
        public string $businessName,
        public string $emailBody,
        public string $businessLink,
        public Email $email,
        public EmailGroup $emailGroup,
    )
    {
        $this->callbacks = [array($this, 'incrementValues')];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('promotional@rpx.com', 'Rpx.com')
            ->subject('New message from ' . $this->businessName . '!')
            ->markdown('mail.business.promotional', [
                'firstName' => $this->firstName,
                'body' => $this->emailBody,
                'businessName' => $this->businessName,
                'businessLink' => $this->businessLink,
            ]);
    }

    public function incrementValues($message) {
        $email = $this->email;
        // Update SMS message in DB;
        $email->update([
            'sent' => true,
        ]);

        $emailGroup = $this->emailGroup;
        $emailGroup->update([
            'total_sent' => $emailGroup->total_sent + 1,
            'price' => $emailGroup->price + $email->price
        ]);

        Log::info(
            '[BusinessPromotional]-[incrementValues]: Email Sent' .
            ', User ID: '. $email->to_id .
            ', Email: ' . $email->to_email .
            ', Business: ' . $this->businessName,
        );
    }
}
