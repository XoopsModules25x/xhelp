<?php declare(strict_types=1);

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
 */
class JPSpan_Listener
{
    /**
     * Encoding used by request (e.g. 'xml' or 'php')
     * @var string
     */
    public $encoding = 'xml';
    /**
     * Object which responds to request
     * @var object implementing Responder interface
     */
    public $Responder;

    /**
     * Constructs the listener, setting the default NullResponder
     */
    public function __construct()
    {
        $this->Response = new JPSpan_NullResponder();
    }

    /**
     * Set the Responder
     * @param mixed $Responder
     */
    public function setResponder(&$Responder): void
    {
        $this->Responder = &$Responder;
    }

    /**
     * Serve incoming requests
     */
    public function serve(): void
    {
        $this->Responder->execute($this->getRequestData());
    }

    /**
     * Detects the type of incoming request and calls the corresponding
     * RequestData handler to deal with it.
     * @return mixed request data as native PHP variables.
     */
    public function getRequestData()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $http_raw_post_data = file_get_contents('php://input');
                if ($http_raw_post_data) {
                    return (new JPSpan_RequestData_RawPost())->fetch($this->encoding);
                }

                return (new JPSpan_RequestData_Post())->fetch($this->encoding);
                break;
            case 'GET':
            default:
                return (new JPSpan_RequestData_Get())->fetch($this->encoding);
                break;
        }
    }
}

//-----------------------------------------------------------------------------

/**
 * A NullResponder loaded as the default responder
 */
class JPSpan_NullResponder
{
    /**
     * Does nothing
     * @param mixed $payload
     */
    public function execute(&$payload): void
    {
    }
}
