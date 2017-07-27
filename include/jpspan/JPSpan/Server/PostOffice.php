<?php
/**
 * @package    JPSpan
 * @subpackage Server
 */
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
 * @package    JPSpan
 * @subpackage Server
 * @public
 */
class JPSpan_Server_PostOffice extends JPSpan_Server
{
    /**
     * Name of user defined handler that was called
     * @param string
     * @access private
     */
    public $calledClass = null;

    /**
     * Name of method in handler
     * @param string
     * @access private
     */
    public $calledMethod = null;

    /**
     * Request encoding to use (e.g. xml or php)
     * @var string
     * @access public
     */
    public $RequestEncoding = 'xml';

    /**
     * @access public
     */
    public function __construct()
    {
        parent::JPSpan_Server();
    }

    /**
     * Serve a request
     * @param boolean send headers
     * @return boolean FALSE if failed (invalid request - see errors)
     * @access public
     */
    public function serve($sendHeaders = true)
    {
        require_once JPSPAN . 'Monitor.php';
        $M = &JPSpan_Monitor::instance();

        $this->calledClass  = null;
        $this->calledMethod = null;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            trigger_error('Invalid HTTP request method: ' . $_SERVER['REQUEST_METHOD'], E_USER_ERROR);

            return false;
        }

        if ($this->resolveCall()) {
            $M->setRequestInfo('class', $this->calledClass);
            $M->setRequestInfo('method', $this->calledMethod);

            if (false !== ($Handler =& $this->getHandler($this->calledClass))) {
                $args = [];
                $M->setRequestInfo('args', $args);

                if ($this->getArgs($args)) {
                    $M->setRequestInfo('args', $args);

                    $response = call_user_func_array([
                                                         & $Handler,
                                                         $this->calledMethod
                                                     ], $args);
                } else {
                    $response = call_user_func([
                                                   & $Handler,
                                                   $this->calledMethod
                                               ]);
                }

                require_once JPSPAN . 'Serializer.php';

                $M->setResponseInfo('payload', $response);
                $M->announceSuccess();

                $response = JPSpan_Serializer::serialize($response);

                if ($sendHeaders) {
                    header('Content-Length: ' . strlen($response));
                    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
                    header('Cache-Control: no-cache, must-revalidate');
                    header('Pragma: no-cache');
                }
                echo $response;

                return true;
            } else {
                trigger_error('Invalid handle for: ' . $this->calledClass, E_USER_ERROR);

                return false;
            }
        }

        return false;
    }

    /**
     * Resolve the call - identify the handler class and method and store
     * locally
     * @return boolean FALSE if failed (invalid request - see errors)
     * @access private
     */
    public function resolveCall()
    {
        // Hack between server.php?class/method and server.php/class/method
        $uriPath = $_SERVER['QUERY_STRING'];

        if ($uriPath) {
            if (preg_match('/\/$/', $uriPath)) {
                $uriPath = substr($uriPath, 0, strlen($uriPath) - 1);
            }
        } else {
            $uriPath = JPSpan_Server::getUriPath();
        }

        $uriPath = explode('/', $uriPath);

        if (count($uriPath) != 2) {
            trigger_error('Invalid call syntax', E_USER_ERROR);

            return false;
        }

        if (preg_match('/^[a-z]+[0-9a-z_]*$/', $uriPath[0]) != 1) {
            trigger_error('Invalid handler name: ' . $uriPath[0], E_USER_ERROR);

            return false;
        }

        if (preg_match('/^[a-z]+[0-9a-z_]*$/', $uriPath[1]) != 1) {
            trigger_error('Invalid handler method: ' . $uriPath[1], E_USER_ERROR);

            return false;
        }

        if (!array_key_exists($uriPath[0], $this->descriptions)) {
            trigger_error('Unknown handler: ' . $uriPath[0], E_USER_ERROR);

            return false;
        }

        if (!in_array($uriPath[1], $this->descriptions[$uriPath[0]]->methods)) {
            trigger_error('Unknown handler method: ' . $uriPath[1], E_USER_ERROR);

            return false;
        }

        $this->calledClass  = $uriPath[0];
        $this->calledMethod = $uriPath[1];

        return true;
    }

    /**
     * Populate the args array if there are any
     * @param array args (reference)
     * @return boolean TRUE if request had args
     * @access private
     */
    public function getArgs(& $args)
    {
        require_once JPSPAN . 'RequestData.php';

        if ($this->RequestEncoding == 'php') {
            $args = JPSpan_RequestData_Post::fetch($this->RequestEncoding);
        } else {
            $args = JPSpan_RequestData_RawPost::fetch($this->RequestEncoding);
        }

        if (is_array($args)) {
            return true;
        }

        return false;
    }

    /**
     * Get the Javascript client generator
     * @return JPSpan_Generator
     * @access public
     */
    public function & getGenerator()
    {
        require_once JPSPAN . 'Generator.php';
        $G = new JPSpan_Generator();
        $G->init(new JPSpan_PostOffice_Generator(), $this->descriptions, $this->serverUrl, $this->RequestEncoding);

        return $G;
    }
}

//--------------------------------------------------------------------------------

/**
 * Generator for the JPSpan_Server_PostOffice
 * @todo       Much refactoring need to make code generation "pluggable"
 * @see        JPSpan_Server_PostOffice
 * @package    JPSpan
 * @subpackage Server
 * @access     public
 */
class JPSpan_PostOffice_Generator
{
    /**
     * @var array list of JPSpan_HandleDescription objects
     * @access public
     */
    public $descriptions;

    /**
     * @var string URL or server
     * @access public
     */
    public $serverUrl;

    /**
     * How requests should be encoded
     * @var string request encoding
     * @access public
     */
    public $RequestEncoding;

    /**
     * Invokes code generator
     * @param JPSpan_CodeWriter
     * @return void
     * @access public
     */
    public function generate(& $Code)
    {
        $this->generateScriptHeader($Code);
        foreach (array_keys($this->descriptions) as $key) {
            $this->generateHandleClient($Code, $this->descriptions[$key]);
        }
    }

    /**
     * Generate the starting includes section of the script
     * @param JPSpan_CodeWriter
     * @return void
     * @access private
     */
    public function generateScriptHeader(& $Code)
    {
        ob_start(); ?>
        /**@ * include __DIR__ . '/remoteobject.js';
        <?php
        if ($this->RequestEncoding == 'xml') {
            ?>
            * include __DIR__ . '/request/rawpost.js'; * include __DIR__ . '/encode/xml.js';
            <?php
        } else {
            ?>
            * include __DIR__ . '/request/post.js'; * include __DIR__ . '/encode/php.js';
            <?php
        } ?>
        */
        <?php
        $Code->append(ob_get_contents());
        ob_end_clean();
    }

    /**
     * Generate code for a single description (a single PHP class)
     * @param JPSpan_CodeWriter
     * @param JPSpan_HandleDescription
     * @return void
     * @access private
     */
    public function generateHandleClient(& $Code, & $Description)
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
        if ($this->RequestEncoding == 'xml') {
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
        $Code->append(ob_get_contents());
        ob_end_clean();
    }
}
