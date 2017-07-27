<?php
/**
 * @package    JPSpan
 * @subpackage Listener
 */
//-----------------------------------------------------------------------------

/**
 * Include handlers for incoming request data
 */
require_once JPSPAN . 'RequestData.php';

/**
 * Check always_populate_raw_post_data is switched off
 */
if (ini_get('always_populate_raw_post_data')) {
    trigger_error("Configuration error: PHP ini setting 'always_populate_raw_post_data' must be off", E_USER_ERROR);
}
//-----------------------------------------------------------------------------

/**
 * Listener for incoming requests
 * @package    JPSpan
 * @subpackage Listener
 * @access     public
 */
class JPSpan_Listener
{
    /**
     * Encoding used by request (e.g. 'xml' or 'php')
     * @var string
     * @access public
     */
    public $encoding = 'xml';

    /**
     * Object which responds to request
     * @var object implementing Responder interface
     * @access private
     */
    public $Responder;

    /**
     * Constructs the listener, setting the default NullResponder
     * @access public
     */
    public function __construct()
    {
        $this->Response = new JPSpan_NullResponder();
    }

    /**
     * Set the Responder
     * @param object implementing Responder interface
     * @return void
     * @access public
     */
    public function setResponder(& $Responder)
    {
        $this->Responder =& $Responder;
    }

    /**
     * Serve incoming requests
     * @return void
     * @access public
     */
    public function serve()
    {
        $this->Responder->execute($this->getRequestData());
    }

    /**
     * Detects the type of incoming request and calls the corresponding
     * RequestData handler to deal with it.
     * @return mixed request data as native PHP variables.
     * @access private
     */
    public function getRequestData()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $http_raw_post_data = file_get_contents('php://input');
                if ($http_raw_post_data) {
                    return JPSpan_RequestData_RawPost::fetch($this->encoding);
                } else {
                    return JPSpan_RequestData_Post::fetch($this->encoding);
                }
                break;
            case 'GET':
            default:
                return JPSpan_RequestData_Get::fetch($this->encoding);
                break;
        }
    }
}

//-----------------------------------------------------------------------------

/**
 * A NullResponder loaded as the default responder
 * @package    JPSpan
 * @subpackage Listener
 * @access     public
 */
class JPSpan_NullResponder
{
    /**
     * Does nothing
     * @param mixed incoming request data
     * @return void
     * @access public
     */
    public function execute(& $payload)
    {
    }
}
