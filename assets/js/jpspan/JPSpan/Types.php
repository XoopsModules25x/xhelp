<?php declare(strict_types=1);

//--------------------------------------------------------------------------------

/**
 * Javascript Objects are unserialized into instances of
 * of this class
 */
class JPSpan_Object
{
}

//--------------------------------------------------------------------------------

/**
 * Used to generate Javascript errors
 */
class JPSpan_Error
{
    /**
     * Error code
     * @var string
     */
    public $code;
    /**
     * Name of Javascript error class
     * @var string
     */
    public $name;
    /**
     * Error message
     * @var string
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
     */
    public function __construct($code = null, $name = null, $message = null)
    {
        if ($code && $name && $message) {
            $this->setError($code, $name, $message);
        }
    }

    /**
     * Set the error name and message (also reports to the monitor
     * @param mixed $code
     * @param mixed $name
     * @param mixed $message
     * @see    JPSpan_Monitor
     */
    public function setError($code, $name, $message): void
    {
        $this->code    = $code;
        $this->name    = $name;
        $this->message = $message;

        require_once JPSPAN . 'Monitor.php';
        $M = &(new JPSpan_Monitor())->instance();
        $M->announceError($name, $code, $message, __FILE__, __LINE__);
    }
}

//--------------------------------------------------------------------------------
/**
 * Registers the native types for unserialization. Called when Unserializer.php is
 * included (and expects it to already be included)
 */
function JPSpan_Register_Unserialization_Types(): void
{
    (new JPSpan_Unserializer())->addType('JPSpan_Object');
    (new JPSpan_Unserializer())->addType('JPSpan_Error');
}
