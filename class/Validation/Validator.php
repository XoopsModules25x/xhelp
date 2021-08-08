<?php namespace XoopsModules\Xhelp\Validation;

use XoopsModules\Xhelp;

/**
 *  Validator superclass for form validation
 */
class Validator
{
    /**
     * Private
     * $errorMsg stores error messages if not valid
     */
    public $errorMsg;

    //! A constructor.

    /**
     * Constucts a new Validator object
     */
    public function __construct()
    {
        $this->errorMsg = [];
        $this->validate();
    }

    //! A manipulator

    /**
     * @return void
     */
    public function validate()
    {
        // Superclass method does nothing
    }

    //! A manipulator

    /**
     * Adds an error message to the array
     * @param $msg
     * @return void
     */
    public function setError($msg)
    {
        $this->errorMsg[] = $msg;
    }

    //! An accessor

    /**
     * Returns true is string valid, false if not
     * @return boolean
     */
    public function isValid()
    {
        if (count($this->errorMsg)) {
            return false;
        } else {
            return true;
        }
    }

    //! An accessor

    /**
     * Pops the last error message off the array
     * @return string
     */
    public function getError()
    {
        return array_pop($this->errorMsg);
    }

    /**
     * @return array
     */
    public function &getErrors()
    {
        return $this->errorMsg;
    }
}
