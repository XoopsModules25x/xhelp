<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Validation;

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
     * Constructs a new Validator object
     */
    public function __construct()
    {
        $this->errorMsg = [];
        $this->validate();
    }

    //! A manipulator

    public function validate()
    {
        // Superclass method does nothing
    }

    //! A manipulator

    /**
     * Adds an error message to the array
     * @param string $msg
     */
    public function setError($msg)
    {
        $this->errorMsg[] = $msg;
    }

    //! An accessor

    /**
     * Returns true is string valid, false if not
     * @return bool
     */
    public function isValid(): bool
    {
        if (\count($this->errorMsg)) {
            return false;
        }

        return true;
    }

    //! An accessor

    /**
     * Pops the last error message off the array
     * @return string
     */
    public function getError(): string
    {
        return \array_pop($this->errorMsg);
    }

    /**
     * @return array
     */
    public function &getErrors(): array
    {
        return $this->errorMsg;
    }
}
