<?php
namespace Laravolt\Password;

use Carbon\Carbon;

trait CanChangePassword
{
    /**
     * @param      $password
     * @param bool $mustBeChanged
     * @return $this
     */
    public function setPassword($password, $mustBeChanged = false)
    {
        $this->password = bcrypt($password);

        if ($mustBeChanged) {
            $this->password_last_set = null;
        }

        return $this->save();
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = $value;
            $this->password_last_set = new Carbon();
        }

        return true;
    }

    /**
     * @param null $durationInDays
     * @return bool
     */
    public function passwordMustBeChanged($durationInDays = null)
    {
        if ($this->password_last_set === null) {
            return true;
        }

        if ($durationInDays === null) {
            return false;
        }

        return $this->password_last_set->addDays((int)$durationInDays)->lte(Carbon::now());
    }

    public function getEmailForNewPassword()
    {
        return $this->email;
    }
}
