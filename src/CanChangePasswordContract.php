<?php
namespace Laravolt\Password;

interface CanChangePasswordContract
{
    public function setPassword($password, $mustBeChanged = false);

    public function passwordMustBeChanged();

    public function getEmailForNewPassword();
}
