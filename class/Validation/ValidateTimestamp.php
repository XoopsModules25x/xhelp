<?php

namespace XoopsModules\Xhelp\Validation;

use XoopsModules\Xhelp\Validation;

/**
 * Class ValidateTimestamp
 */
class ValidateTimestamp extends Validator
{
    /**
     * Private
     * $timestamp the date/time to validate
     */
    public $timestamp;

    //! A constructor.

    /**
     * Constructs a new ValidateTimestamp object subclass or Validator
     * @param int $timestamp the string to validate
     */
    public function __construct($timestamp)
    {
        $this->timestamp = $timestamp;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates a timestamp
     */
    public function validate()
    {
    }
}
