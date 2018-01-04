<?php namespace XoopsModules\Xhelp\validation;

use XoopsModules\Xhelp;
use XoopsModules\Xhelp\validation;


/**
 * Class ValidateLength
 */
class ValidateLength extends  validation\Validator
{
    /**
     * Private
     * $text the string to validate
     */
    public $text;

    /**
     * Private
     * $min_length the minimum length of string to validate
     */
    public $min_length;

    /**
     * Private
     * $max_length the max length of string to validate
     */
    public $max_length;

    //! A constructor.

    /**
     * Constucts a new ValidateLength object subclass or Validator
     * @param         $text
     * @param         $min_length the min string size
     * @param int     $max_length the max string size
     */
    public function __construct($text, $min_length, $max_length = 0)
    {
        $this->text       = $text;
        $this->min_length = $min_length;
        $this->max_length = $max_length;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates a string
     * @return void
     */
    public function validate()
    {
        if (strlen($this->text) < $this->min_length) {
            $this->setError(_XHELP_MESSAGE_SHORT);
        }
        if ($this->max_length) {
            if (strlen($this->text) > $this->max_length) {
                $this->setError(_XHELP_MESSAGE_LONG);
            }
        }
    }
}
