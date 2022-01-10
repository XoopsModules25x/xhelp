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
 *  ValidatorRegex subclass of Validator
 *  Validates an email address
 */
class ValidateRegex extends Validator
{
    public $pattern;
    public $checkText = '';
    public $required;
    //! A constructor.

    /**
     * Constructs a new ValidateEmail object subclass or Validator
     * @param string $checkText
     * @param string $pattern
     * @param bool   $required
     * @internal param the $email string to validate
     */
    public function __construct(string $checkText, string $pattern, bool $required)
    {
        $this->pattern   = $pattern;
        $this->checkText = $checkText;
        $this->required  = $required;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates a regular expression
     */
    public function validate()
    {
        if (1 == $this->required) {                                // If value is required
            if (\is_array($this->checkText) && isset($this->checkText['size'])) {     // If this is a file
                if ('' === $this->checkText['name']) {
                    $this->setError(\_XHELP_MESSAGE_REQUIRED);           // Return message saying required value
                }
            } else {                                                    // If not a file
                if ('' != $this->pattern) {                               // If regex pattern is not empty
                    if (!\preg_match('/' . $this->pattern . '/', (string)$this->checkText)) {  // Check regex against supplied text
                        $this->setError(\_XHELP_MESSAGE_INVALID);                              // Return message saying invalid value
                    }
                } else {
                    if (empty($this->checkText)) {                           // If text is not supplied
                        $this->setError(\_XHELP_MESSAGE_REQUIRED);           // Return message saying required value
                    }
                }
            }
        } else {
            if (empty($this->checkText)) {
                if ('' != $this->pattern) {
                    if (!\preg_match('/' . $this->pattern . '/', $this->checkText)) {
                        $this->setError(\_XHELP_MESSAGE_INVALID);
                    }
                }
            }
        }
    }
}
