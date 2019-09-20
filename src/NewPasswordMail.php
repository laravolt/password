<?php

namespace Laravolt\Password;

use Illuminate\Mail\Mailable;

class NewPasswordMail extends Mailable
{
    public $password;

    /**
     * ResetLinkMail constructor.
     */
    public function __construct($password)
    {
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject(trans('password::password.new_password_mail_subject'))->view('password::new');
    }
}
