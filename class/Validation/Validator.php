<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Validation;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    XOOPS Project (https://xoops.org)
 * @license      GNU GPL 2 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       XOOPS Development Team
 */

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
    public function setError(string $msg)
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
