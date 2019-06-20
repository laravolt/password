<?php

namespace Laravolt\Password;

use Illuminate\Support\Facades\Mail;
use Laravolt\Auth\Notifications\ResetPassword;

trait CanResetPassword
{
    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $email = $this->getEmailForPasswordReset();
        Mail::to($email)->send(new ResetLinkMail($token, $email));
    }
}
