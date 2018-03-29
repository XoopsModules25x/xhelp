<?php namespace XoopsModules\Xhelp\Validation;

use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;

/**
 *  ValidatorEmail subclass of Validator
 *  Validates an email address
 */
class ValidateEmail extends Validator
{
    /**
     * Private
     * $email the email address to validate
     */
    public $email;

    //! A constructor.

    /**
     * Constucts a new ValidateEmail object subclass or Validator
     * @param string $email the string to validate
     */
    public function __construct($email)
    {
        $this->email = $email;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates an email address
     * @return void
     */
    public function validate()
    {
        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i";
        //$pattern= "/^([a-zA-Z0-9])+([.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-]+)+/";
        if (!preg_match($pattern, $this->email)) {
            $this->setError(_XHELP_MESSAGE_INVALID);
        }
        if (strlen($this->email) > 100) {
            $this->setError(_XHELP_MESSAGE_LONG);
        }
        if (strlen($this->email) < 5) {
            $this->setError(_XHELP_MESSAGE_SHORT);
        }
    }
}
