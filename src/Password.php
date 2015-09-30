<?php
namespace Laravolt\Password;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Mail\Mailer;

class Password
{
    protected $passwordBroker;

    protected $mailer;

    protected $newPasswordEmailView;

    protected $resetPasswordEmailView;

    /**
     * Password constructor.
     * @param PasswordBroker $passwordBroker
     * @param Mailer $mailer
     * @param $newPasswordEmailView
     */
    public function __construct(PasswordBroker $passwordBroker, Mailer $mailer, $newPasswordEmailView)
    {
        $this->passwordBroker = $passwordBroker;
        $this->mailer = $mailer;
        $this->newPasswordEmailView = $newPasswordEmailView;
    }

    public function sendResetLink(CanResetPassword $user)
    {
        $response = $this->passwordBroker->sendResetLink(['id' => $user['id']], function (Message $message) {
            $message->subject(trans('passwords.reset'));
        });

        return $response;
    }

    /**
     * @param CanChangePasswordContract $user
     * @param bool|false $mustBeChanged
     * @return bool
     */
    public function sendNewPassword(CanChangePasswordContract $user, $mustBeChanged = false)
    {
        $newPassword = $this->generate();
        $user->setPassword($newPassword, $mustBeChanged);

        $this->emailNewPassword($user, $newPassword);

        return true;
    }

    protected function generate($length = 8)
    {
        return str_random($length);
    }

    protected function emailNewPassword(CanChangePasswordContract $user, $password)
    {
        $view = $this->newPasswordEmailView;
        $this->mailer->send($view, compact('user', 'password'), function($m) use ($user) {
            $m->to($user->getEmailForNewPassword());
        });
    }
}
