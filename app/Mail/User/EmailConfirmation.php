<?php

namespace App\Mail\User;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $pin;

    public $lang;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $pin, $lang)
    {
        $this->user = $user;
        $this->pin = $pin;
        $this->lang = $lang;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        App::setLocale($this->lang);

        $subject = 'Confirm your e-mail.';

        $emailFrom = 'welcome@rpx.com';

        return $this->subject($subject)
                    ->from($emailFrom, 'Rpx')
                    ->to($this->user['email'])
                    ->view('emails.users.email_confirmation');
    }
}
