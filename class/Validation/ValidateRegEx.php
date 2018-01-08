<?php namespace XoopsModules\Xhelp\Validation;

use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;


/**
 *  ValidatorRegex subclass of Validator
 *  Validates an email address
 */
class ValidateRegEx extends validation\Validator
{
    public $pattern;
    public $checkText;
    public $required;

    //! A constructor.

    /**
     * Constucts a new ValidateEmail object subclass or Validator
     * @param $checkText
     * @param $pattern
     * @param $required
     * @internal param the $email string to validate
     */
    public function __construct($checkText, $pattern, $required)
    {
        $this->pattern   = $pattern;
        $this->checkText = $checkText;
        $this->required  = $required;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates a regular expression
     * @return void
     */
    public function validate()
    {
        if (1 == $this->required) {                                // If value is required
            if (is_array($this->checkText) && isset($this->checkText['size'])) {     // If this is a file
                if ('' == $this->checkText['name']) {
                    $this->setError(_XHELP_MESSAGE_REQUIRED);           // Return message saying required value
                }
            } else {                                                    // If not a file
                if ('' != $this->pattern) {                               // If regex pattern is not empty
                    if (!preg_match('/' . $this->pattern . '/', $this->checkText)) {  // Check regex against supplied text
                        $this->setError(_XHELP_MESSAGE_INVALID);        // Return message saying invalid value
                    }
                } else {
                    if (empty($this->checkText)) {    // If text is not supplied
                        $this->setError(_XHELP_MESSAGE_REQUIRED);           // Return message saying required value
                    }
                }
            }
        } else {
            if (empty($this->checkText)) {
                if ('' != $this->pattern) {
                    if (!preg_match('/' . $this->pattern . '/', $this->checkText)) {
                        $this->setError(_XHELP_MESSAGE_INVALID);
                    }
                }
            }
        }
    }
}
