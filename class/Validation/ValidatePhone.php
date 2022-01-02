<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Validation;

/**
 * Class ValidatePhone
 */
class ValidatePhone extends Validator
{
    /**
     * Private
     * $phone the phone number to validate
     */
    public $phone;
    //! A constructor.

    /**
     * Constructs a new ValidatePhone object subclass or Validator
     * @param string $phone the string to validate
     */
    public function __construct($phone)
    {
        $this->phone = $phone;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates a phone number
     */
    public function validate()
    {
        $pattern = '/(\d{3})\D*(\d{3})\D*(\d{4})\D*(\d*)$/';
        if (!\preg_match($pattern, $this->phone)) {
            $this->setError(\_XHELP_MESSAGE_INVALID);
        }
    }
}
