<?php

namespace XoopsModules\Xhelp\Validation;

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

    public $forceentry;

    //! A constructor.

    /**
     * Constructs a new ValidateNumber object subclass or Validator
     * @param      $text
     * @param bool $forceentry
     */
    public function __construct($text, $forceentry = false)
    {
        $this->text       = $text;
        $this->forceentry = $forceentry;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates a number
     */
    public function validate()
    {
        if (!\is_numeric($this->text) && (mb_strlen($this->text) > 0 && !$this->forceentry)) {
            $this->setError(_XHELP_MESSAGE_NOT_NUMERIC);
        }
    }
}
