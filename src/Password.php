<?php
namespace Laravolt\Password;

use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use UnexpectedValueException;

class Password
{
    protected $token;

    protected $mailer;

    /**
     * Password constructor.
     * @param TokenRepositoryInterface  $token
     * @param Mailer $mailer
     */
    public function __construct(TokenRepositoryInterface $token, Mailer $mailer)
    {
        $this->token = $token;
        $this->mailer = $mailer;
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

    public function changePasswordByToken($user, $password, $token)
    {
        if (!$user instanceof CanResetPassword) {
            throw new UnexpectedValueException('User must implement CanResetPassword interface.');
        }

        if (!$user instanceof CanChangePasswordContract) {
            throw new UnexpectedValueException('User must implement CanChangePasswordContract interface.');
        }

        if (!$this->token->exists($user, $token)) {
            return \Illuminate\Support\Facades\Password::INVALID_TOKEN;
        }

        return DB::transaction(function () use ($user, $password) {
            $user->setPassword($password);
            $this->token->delete($user);

            return \Illuminate\Support\Facades\Password::PASSWORD_RESET;
        });

    }

    public function resetTokenExists($token)
    {

    }

    protected function generate($length = 8)
    {
        return str_random($length);
    }

    protected function emailNewPassword(CanChangePasswordContract $user, $password)
    {
        Mail::to($user)->send(new NewPasswordMail($password));
    }
}
