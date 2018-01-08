<?php namespace XoopsModules\Xhelp\Validation;

use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;


/**
 * Class ValidatePhone
 */
class ValidatePhone extends validation\Validator
{
    /**
     * Private
     * $phone the phone number to validate
     */
    public $phone;

    //! A constructor.

    /**
     * Constucts a new ValidatePhone object subclass or Validator
     * @param string $phone the string to validate
     */
    public function __construct($phone)
    {
        $this->phone = $phone;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates a phone number
     * @return void
     */
    public function validate()
    {
        $pattern = "(\d{3})\D*(\d{3})\D*(\d{4})\D*(\d*)$";
        if (!preg_match($pattern, $this->phone)) {
            $this->setError(_XHELP_MESSAGE_INVALID);
        }
    }
}
