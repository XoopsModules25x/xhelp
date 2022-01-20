<?php declare(strict_types=1);

//--------------------------------------------------------------------------------
/**
 * Define
 */
if (!defined('JPSPAN')) {
    define('JPSPAN', __DIR__ . '/../');
}
/**
 * Include
 */
require_once JPSPAN . 'Server.php';
//--------------------------------------------------------------------------------

/**
 * Class and method name passed in the URL with params passed
 * as url-encoded POST data. Urls like
 * http://localhost/server.php/Class/Method
 * @public
 */
class JPSpan_Server_PostOffice extends JPSpan_Server
{
    /**
     * Name of user defined handler that was called
     * @param string
     */
    public $calledClass = null;
    /**
     * Name of method in handler
     * @param string
     */
    public $calledMethod = null;
    /**
     * Request encoding to use (e.g. xml or php)
     * @var string
     */
    public $RequestEncoding = 'xml';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Serve a request
     * @param mixed $sendHeaders
     * @return bool FALSE if failed (invalid request - see errors)
     */
    public function serve($sendHeaders = true): bool
    {
        require_once JPSPAN . 'Monitor.php';
        $M = &(new JPSpan_Monitor())->instance();

        $this->calledClass  = null;
        $this->calledMethod = null;

        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            trigger_error('Invalid HTTP request method: ' . $_SERVER['REQUEST_METHOD'], E_USER_ERROR);

            return false;
        }

        if ($this->resolveCall()) {
            $M->setRequestInfo('class', $this->calledClass);
            $M->setRequestInfo('method', $this->calledMethod);

            if (false !== ($Handler = $this->getHandler($this->calledClass))) {
                $args = [];
                $M->setRequestInfo('args', $args);

                if ($this->getArgs($args)) {
                    $M->setRequestInfo('args', $args);

                    $response = call_user_func_array(
                        [
                            &$Handler,
                            $this->calledMethod,
                        ],
                        $args
                    );
                } else {
                    $response = call_user_func(
                        [
                            &$Handler,
                            $this->calledMethod,
                        ]
                    );
                }

                require_once JPSPAN . 'Serializer.php';

                $M->setResponseInfo('payload', $response);
                $M->announceSuccess();

                $response = (new JPSpan_Serializer())->serialize($response);

                if ($sendHeaders) {
                    header('Content-Length: ' . mb_strlen($response));
                    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
                    header('Cache-Control: no-cache, must-revalidate');
                    header('Pragma: no-cache');
                }
                echo $response;

                return true;
            }
            trigger_error('Invalid handle for: ' . $this->calledClass, E_USER_ERROR);

            return false;
        }

        return false;
    }

    /**
     * Resolve the call - identify the handler class and method and store
     * locally
     * @return bool FALSE if failed (invalid request - see errors)
     */
    public function resolveCall(): bool
    {
        // Hack between server.php?class/method and server.php/class/method
        $uriPath = $_SERVER['QUERY_STRING'];

        if ($uriPath) {
            if (preg_match('/\/$/', $uriPath)) {
                $uriPath = mb_substr($uriPath, 0, -1);
            }
        } else {
            $uriPath = JPSpan_Server::getUriPath();
        }

        $uriPath = explode('/', $uriPath);

        if (2 != count($uriPath)) {
            trigger_error('Invalid call syntax', E_USER_ERROR);

            return false;
        }

        if (1 != preg_match('/^[a-z]+[0-9a-z_]*$/', $uriPath[0])) {
            trigger_error('Invalid handler name: ' . $uriPath[0], E_USER_ERROR);

            return false;
        }

        if (1 != preg_match('/^[a-z]+[0-9a-z_]*$/', $uriPath[1])) {
            trigger_error('Invalid handler method: ' . $uriPath[1], E_USER_ERROR);

            return false;
        }

        if (!array_key_exists($uriPath[0], $this->descriptions)) {
            trigger_error('Unknown handler: ' . $uriPath[0], E_USER_ERROR);

            return false;
        }

        if (!in_array($uriPath[1], $this->descriptions[$uriPath[0]]->methods, true)) {
            trigger_error('Unknown handler method: ' . $uriPath[1], E_USER_ERROR);

            return false;
        }

        $this->calledClass  = $uriPath[0];
        $this->calledMethod = $uriPath[1];

        return true;
    }

    /**
     * Populate the args array if there are any
     * @param mixed $args
     * @return bool TRUE if request had args
     */
    public function getArgs(&$args): bool
    {
        require_once JPSPAN . 'RequestData.php';

        if ('php' === $this->RequestEncoding) {
            $args = (new JPSpan_RequestData_Post())->fetch($this->RequestEncoding);
        } else {
            $args = (new JPSpan_RequestData_RawPost())->fetch($this->RequestEncoding);
        }

        if (is_array($args)) {
            return true;
        }

        return false;
    }

    /**
     * Get the Javascript client generator
     * @return JPSpan_Generator
     */
    public function &getGenerator(): JPSpan_Generator
    {
        require_once JPSPAN . 'Generator.php';
        $G = new JPSpan_Generator();
        $postofficeGenerator = new JPSpan_PostOffice_Generator();
        $G->init($postofficeGenerator, $this->descriptions, $this->serverUrl, $this->RequestEncoding);

        return $G;
    }
}

//--------------------------------------------------------------------------------

/**
 * Generator for the JPSpan_Server_PostOffice
 * @todo       Much refactoring need to make code generation "pluggable"
 * @see        JPSpan_Server_PostOffice
 */
class JPSpan_PostOffice_Generator
{
    /**
     * @var array list of JPSpan_HandleDescription objects
     */
    public $descriptions;
    /**
     * @var string URL or server
     */
    public $serverUrl;
    /**
     * How requests should be encoded
     * @var string request encoding
     */
    public $RequestEncoding;

    /**
     * Invokes code generator
     * @param mixed $Code
     */
    public function generate($Code): void
    {
        $this->generateScriptHeader($Code);
        foreach (array_keys($this->descriptions) as $key) {
            $this->generateHandleClient($Code, $this->descriptions[$key]);
        }
    }

    /**
     * Generate the starting includes section of the script
     * @param mixed $Code
     */
    public function generateScriptHeader($Code): void
    {
        ob_start(); ?>
        /**@ * require_once __DIR__   . '/remoteobject.js';
        <?php
        if ('xml' === $this->RequestEncoding) {
            ?>
            * require_once __DIR__   . '/request/rawpost.js'; * require_once __DIR__   . '/encode/xml.js';
            <?php
        } else {
            ?>
            * require_once __DIR__   . '/request/post.js'; * require_once __DIR__   . '/encode/php.js';
            <?php
        } ?>
        */
        <?php
        $Code->append(ob_get_clean());
    }

    /**
     * Generate code for a single description (a single PHP class)
     * @param mixed $Code
     * @param mixed $Description
     */
    public function generateHandleClient($Code, $Description): void
    {
        ob_start(); ?>

        function
        <?php echo $Description->Class; ?>
        () { var oParent = new JPSpan_RemoteObject(); if (arguments[0]) {
        oParent.Async(arguments[0]); } oParent.__serverurl = '
        <?php
        echo $this->serverUrl . '?' . $Description->Class; ?>
        '; oParent.__remoteClass = '
        <?php echo $Description->Class; ?>
        ';

        <?php
        if ('xml' === $this->RequestEncoding) {
            ?>
            oParent.__request = new JPSpan_Request_RawPost(new JPSpan_Encode_Xml());
            <?php
        } else {
            ?>
            oParent.__request = new JPSpan_Request_Post(new JPSpan_Encode_PHP());
            <?php
        }

        foreach ($Description->methods as $method) {
            ?>

            // @access public oParent.
            <?php echo $method; ?>
            = function() { var url = this.__serverurl+'/
            <?php echo $method; ?>
            /'; return this.__call(url,arguments,'
            <?php echo $method; ?>
            '); };
            <?php
        } ?>

        return oParent; }

        <?php
        $Code->append(ob_get_clean());
    }
}
