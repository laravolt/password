<?php
namespace Laravolt\Password;

use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;

class Password
{
    protected $token;

    protected $mailer;

    protected $newPasswordEmailView;

    protected $resetPasswordEmailView;

    /**
     * Password constructor.
     * @param TokenRepositoryInterface  $token
     * @param Mailer $mailer
     * @param $newPasswordEmailView
     */
    public function __construct(TokenRepositoryInterface $token, Mailer $mailer, $newPasswordEmailView)
    {
        $this->token = $token;
        $this->mailer = $mailer;
        $this->newPasswordEmailView = $newPasswordEmailView;
    }

    public function sendResetLink(CanResetPassword $user)
    {
        $user->sendPasswordResetNotification($this->token->create($user));

        return PasswordBroker::RESET_LINK_SENT;
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
