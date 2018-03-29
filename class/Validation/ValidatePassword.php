<?php namespace XoopsModules\Xhelp\Validation;

use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;

/**
 *  ValidatorPassword subclass of Validator
 *  Validates a password
 */
class ValidatePassword extends validation\Validator
{
    /**
     * Private
     * $pass the password to validate
     */
    public $pass;

    /**
     * Private
     * $vpass the verification password to validate
     */
    public $vpass;

    //! A constructor.

    /**
     * Constucts a new ValidatePassword object subclass or Validator
     * @param string $pass the string to validate
     * @param $vpass
     */
    public function __construct($pass, $vpass)
    {
        $this->pass  = $pass;
        $this->vpass = $vpass;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates a password
     * @return void
     */
    public function validate()
    {
        $hConfig = xoops_getHandler('config');
        //$xoopsConfigUser = $hConfig->getConfigsByCat(XOOPS_CONF_USER);
        $xoopsConfigUser = [];
        $crit            = new \Criteria('conf_catid', 2);
        $myConfigs       = $hConfig->getConfigs($crit);
        foreach ($myConfigs as $myConf) {
            $xoopsConfigUser[$myConf->getVar('conf_name')] = $myConf->getVar('conf_value');
        }

        if (!isset($this->pass) || '' == $this->pass || !isset($this->vpass) || '' == $this->vpass) {
            $this->setError(_XHELP_MESSAGE_NOT_SUPPLIED);
            //$stop .= _US_ENTERPWD.'<br>';
        }
        if (isset($this->pass) && ($this->pass != $this->vpass)) {
            $this->setError(_XHELP_MESSAGE_NOT_SAME);
        //$stop .= _US_PASSNOTSAME.'<br>';
        } elseif (('' != $this->pass) && (strlen($this->pass) < $xoopsConfigUser['minpass'])) {
            $this->setError(sprintf(_XHELP_MESSAGE_SHORT, $xoopsConfigUser['minpass']));
            //$stop .= sprintf(_US_PWDTOOSHORT,$xoopsConfigUser['minpass'])."<br>";
        }
    }
}
