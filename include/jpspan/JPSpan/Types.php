<?php
/**
 * @package    JPSpan
 * @subpackage Types
 */
//--------------------------------------------------------------------------------

/**
 * Javascript Objects are unserialized into instances of
 * of this class
 * @package    JPSpan
 * @subpackage Types
 * @access     public
 */
class JPSpan_Object
{
}

//--------------------------------------------------------------------------------

/**
 * Used to generate Javascript errors
 * @package    JPSpan
 * @subpackage Types
 * @access     public
 */
class JPSpan_Error
{
    /**
     * Error code
     * @var string
     * @access public
     */
    public $code;

    /**
     * Name of Javascript error class
     * @var string
     * @access public
     */
    public $name;

    /**
     * Error message
     * @var string
     * @access public
     */
    public $message;

    /**
     * Values can be passed optionally to the constructor
     * @param null        $code
     * @param null|string $name
     * @param null        $message
     * @internal param int $error code
     * @internal param string $name to be given to Javascript error class
     * @internal param string $error message
     * @access   public
     */
    public function __construct($code = null, $name = null, $message = null)
    {
        if ($code && $name && $message) {
            $this->setError($code, $name, $message);
        }
    }

    /**
     * Set the error name and message (also reports to the monitor
     * @see    JPSpan_Monitor
     * @param int    error code
     * @param string name to be given to Javascript error class
     * @param string error message
     * @return void
     * @access public
     */
    public function setError($code, $name, $message)
    {
        $this->code    = $code;
        $this->name    = $name;
        $this->message = $message;

        require_once JPSPAN . 'Monitor.php';
        $M = &JPSpan_Monitor::instance();
        $M->announceError($name, $code, $message, __FILE__, __LINE__);
    }
}

//--------------------------------------------------------------------------------
/**
 * Registers the native types for unserialization. Called when Unserializer.php is
 * included (and expects it to already be included)
 * @access     private
 * @return void
 * @package    JPSpan
 * @subpackage Types
 */
function JPSpan_Register_Unserialization_Types()
{
    JPSpan_Unserializer::addType('JPSpan_Object');
    JPSpan_Unserializer::addType('JPSpan_Error');
}
