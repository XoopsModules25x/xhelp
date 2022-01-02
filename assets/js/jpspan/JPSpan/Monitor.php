<?php declare(strict_types=1);

//--------------------------------------------------------------------------------
/**
 * Define as TRUE to switch on monitor
 */
if (!defined('JPSPAN_MONITOR')) {
    define('JPSPAN_MONITOR', false);
}

/**
 * Observable for logging - notifies registered logger of events
 * You should create instances of this using the instance method
 */
class JPSpan_Monitor
{
    /**
     * Array of request info containing keys 'class', 'method', 'args'
     * @var array
     */
    public $requestInfo = ['class' => null, 'method' => null, 'args' => null];
    /**
     * Array of response info containing keys 'payload'
     * @var array
     */
    public $responseInfo = ['payload' => null];
    /**
     * Objects observing the monitor
     * @var array
     */
    public $observers = [];

    /**
     * Register and observer for notifications
     * @param mixed $Observer
     * @see    JPSpan_Monitor_Observer
     */
    public function addObserver(&$Observer): void
    {
        $this->observers[] = &$Observer;
    }

    /**
     * Add a value to the request info.
     * @param $key
     * @param $value
     * @internal param key $string
     */
    public function setRequestInfo($key, $value): void
    {
        $this->requestInfo[$key] = $value;
    }

    /**
     * Add a value to the response info.
     * @param $key
     * @param $value
     * @internal param key $string
     */
    public function setResponseInfo($key, $value): void
    {
        $this->responseInfo[$key] = $value;
    }

    /**
     * Captures data about the current environment, before a notification
     * @return array
     */
    public function prepareData(): array
    {
        $http_raw_post_data = file_get_contents('php://input');

        $Data = [
            'timestamp'    => time(),
            'gmt'          => gmdate('D, d M Y H:i:s', time()) . ' GMT',
            'requestInfo'  => $this->requestInfo,
            'responseInfo' => $this->responseInfo,
            'SERVER'       => $_SERVER,
            'GET'          => $_GET,
            'POST'         => $_POST,
            'RAWPOST'      => $http_raw_post_data,
        ];

        if (function_exists('apache_request_headers')) {
            $Data['requestHeaders']  = apache_request_headers();
            $Data['responseHeaders'] = apache_response_headers();
        }

        return $Data;
    }

    /**
     * Report and error to observers
     * @param mixed $name
     * @param mixed $code
     * @param mixed $message
     * @param mixed $file
     * @param mixed $line
     */
    public function announceError($name, $code, $message, $file, $line): void
    {
        $Data              = $this->prepareData();
        $Data['errorName'] = $name;
        $Data['errorCode'] = $code;
        $Data['errorMsg']  = $message;
        $Data['errorFile'] = $file;
        $Data['errorLine'] = $line;
        foreach (array_keys($this->observers) as $key) {
            $this->observers[$key]->error($Data);
        }
    }

    /**
     * Report successful request / response to observers
     */
    public function announceSuccess(): void
    {
        $Data = $this->prepareData();
        foreach (array_keys($this->observers) as $key) {
            $this->observers[$key]->success($Data);
        }
    }

    /**
     * Create an instance of the Monitor
     * @param mixed $getMonitor
     * @return JPSpan_Monitor or JPSpan_Monitor_Null is monitoring disabled
     */
    public function &instance($getMonitor = false)
    {
        static $Monitor = null;
        if (!$Monitor) {
            // Allow constant or argument to specify use of the real instance
            if (JPSPAN_MONITOR || $getMonitor) {
                $Monitor = new self();
            } else {
                $Monitor = new JPSpan_Monitor_Null();
            }
        }

        return $Monitor;
    }
}

/**
 * Null monitor for when monitoring is disabled
 */
class JPSpan_Monitor_Null
{
    /**
     * @param $Observer
     */
    public function addObserver(&$Observer): void
    {
    }

    /**
     * @param $key
     * @param $value
     */
    public function setRequestInfo($key, $value): void
    {
    }

    /**
     * @param $key
     * @param $value
     */
    public function setResponseInfo($key, $value): void
    {
    }

    /**
     * @param $name
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     */
    public function announceError($name, $code, $message, $file, $line): void
    {
    }

    public function announceSuccess(): void
    {
    }
}

/**
 * Interface observers should provide. Just for info - you don't need to directly extend it
 */
class JPSpan_Monitor_Observer
{
    /**
     * Called when an error occurs
     * @param mixed $Data
     */
    public function error($Data): void
    {
    }

    /**
     * Called on a successful request / response
     * @param mixed $Data
     */
    public function success($Data): void
    {
    }
}
