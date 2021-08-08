<?php namespace XoopsModules\Xhelp\Validation;

use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;


/**
 * Class ValidateNumber
 */
class ValidateNumber extends Validator
{
    /**
     * Private
     * $text the string to validate
     */
    public $text;

    public $forceeentry;

    //! A constructor.

    /**
     * Constucts a new ValidateNumber object subclass or Validator
     * @param      $text
     * @param bool $forceentry
     */
    public function __construct($text, $forceentry = false)
    {
        $this->text       = $text;
        $this->forceentry = $forceentry;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates a number
     * @return void
     */
    public function validate()
    {
        if (!is_numeric($this->text) && (strlen($this->text) > 0 && !$this->forceentry)) {
            $this->setError(_XHELP_MESSAGE_NOT_NUMERIC);
        }
    }
}
