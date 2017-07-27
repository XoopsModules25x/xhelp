<?php
/**
 * @package    JPSpan
 * @subpackage CodeWriter
 */
//-----------------------------------------------------------------------------

/**
 * Javascript is written via an instance of this class
 * @access     public
 * @package    JPSpan
 * @subpackage CodeWriter
 */
class JPSpan_CodeWriter
{
    /**
     * Serialized Javascript
     * @var string
     * @access private
     */
    public $code = '';

    /**
     * Disables further writing of code
     * Used when errors are generated
     * @var boolean
     * @access public
     */
    public $enabled = true;

    /**
     * Write some code - overwrites the existing code
     * @param string
     * @return void
     * @access public
     */
    public function write($code)
    {
        if ($this->enabled) {
            $this->code = $code;
        }
    }

    /**
     * Append some code to the existing code
     * @param string
     * @return void
     * @access public
     */
    public function append($code)
    {
        if ($this->enabled) {
            $this->code .= $code;
        }
    }

    /**
     * Return all the written code
     * @return string Javascript
     * @access public
     */
    public function toString()
    {
        return $this->code;
    }
}
