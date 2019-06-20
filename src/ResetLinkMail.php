<?php

namespace Laravolt\Password;

use Illuminate\Mail\Mailable;

class ResetLinkMail extends Mailable
{
    public $token;

    public $email;

    /**
     * ResetLinkMail constructor.
     */
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        return $this->subject(trans('password::password.reset_link_mail_subject'))->view('password::reset');
    }
}
