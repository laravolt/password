<?php
namespace Laravolt\Password;

use Carbon\Carbon;

trait CanChangePassword
{
    /**
     * @param $password
     * @param bool $mustBeChanged
     * @return $this
     */
    public function setPassword($password, $mustBeChanged = false)
    {
        $this->password = bcrypt($password);
        $this->password_last_set = new Carbon();

        if ($mustBeChanged) {
            $this->password_last_set = null;
        }

        return $this->save();
    }

    /**
     * @param null $durationInDays
     * @return bool
     */
    public function passwordMustBeChanged($durationInDays = null)
    {
        $durationInDays = (int) $durationInDays;
        if($durationInDays != null && $this->password_last_set != null){
            $expired = $this->password_last_set->addDays($durationInDays);

            return $expired->lte(Carbon::now());
        }

        return $this->password_last_set == null;
    }

    public function getEmailForNewPassword()
    {
        return $this->email;
    }
}
