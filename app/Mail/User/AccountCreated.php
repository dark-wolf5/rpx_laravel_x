<?php

namespace App\Mail\User;

use App\Models\RpxUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $rpxUser;
    protected $withLink;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, RpxUser $rpxUser, bool $withLink)
    {
        $this->user = $user;
        $this->rpxUser = $rpxUser;
        $this->withLink = $withLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('welcome@rpx.com', 'Rpx.com')
                    ->subject('Welcome to Rpx!')
                    ->markdown('emails.account_created', [
                        'user'        => $this->user,
                        'rpxUser' => $this->rpxUser,
                        'withLink' => $this->withLink
                    ]);
    }
}
