<?php
/**
 * @package    JPSpan
 * @subpackage Server
 */

//--------------------------------------------------------------------------------

/**
 * Define
 */

use Xmf\Request;

if (!defined('JPSPAN')) {
    define('JPSPAN', __DIR__ . '/');
}
/**
 * Include
 */
require_once JPSPAN . 'Handle.php';
//--------------------------------------------------------------------------------

/**
 * Base Server class.
 * @package    JPSpan
 * @subpackage Server
 * @public
 * @abstract
 */
class JPSpan_Server
{
    /**
     * Hash of user defined handlers (keys are class name)
     * @var array
     * @access private
     */
    public $handlers = [];
    /**
     * Descriptions of handlers stored here as hash
     * @var array
     * @access private
     */
    public $descriptions = [];
    /**
     * URL where server is published
     * @var string
     * @access private
     */
    public $serverUrl;

    /**
     * Sets up the default server url
     * @access public
     */
    public function __construct()
    {
        if (Request::hasVar('HTTPS', 'SERVER') && 'on' === \mb_strtolower($_SERVER['HTTPS'])) {
            $prot = 'https://';
        } else {
            $prot = 'https://';
        }
        $this->serverUrl = $prot . $_SERVER['HTTP_HOST'] . $this->resolveScriptName();
    }

    /**
     * Set the URL where the server is published
     * @param mixed $serverUrl
     * @access public
     */
    public function setServerUrl($serverUrl): void
    {
        $this->serverUrl = $serverUrl;
    }

    /**
     * Return the server url
     * @return string server url (where the server is public)
     * @access public
     */
    public function getServerUrl(): string
    {
        return $this->serverUrl;
    }

    /**
     * Return reference to a handler given it's name.
     * Note this will also resolve the handle
     * @param mixed $name
     * @return mixed object handler or FALSE if not found
     * @access public
     */
    public function getHandler($name)
    {
        $name = \mb_strtolower($name);
        if (isset($this->handlers[$name])) {
            (new JPSpan_Handle())->resolve($this->handlers[$name]);

            return $this->handlers[$name];
        }

        return false;
    }

    /**
     * Return handler description given it's name
     * @param mixed $name
     * @return mixed object handler description or FALSE if not found
     * @access public
     */
    public function getDescription($name)
    {
        $name = \mb_strtolower($name);
        return $this->descriptions[$name] ?? false;
    }

    /**
     * Registers a user handler class with the server
     * @see      https://wact.sourceforge.net/index.php/Handle
     * @param      $Handle
     * @param null $Description
     * @internal param handle $mixed to user class
     * @access   public
     */
    public function addHandler(&$Handle, $Description = null): void
    {
        if (null === $Description) {
            if (false !== ($Description = (new JPSpan_Handle())->examine($Handle))) {
                $this->handlers[$Description->Class]     = &$Handle;
                $this->descriptions[$Description->Class] = $Description;
            } else {
                trigger_error('Invalid handle', E_USER_ERROR);
            }
        } else {
            if (isset($Description->Class) && is_string($Description->Class) && is_array($Description->methods)) {
                $Description->Class                                     = \mb_strtolower($Description->Class);
                $Description->methods                                   = array_map('\strtolower', $Description->methods);
                $this->handlers[mb_strtolower($Description->Class)]     = &$Handle;
                $this->descriptions[mb_strtolower($Description->Class)] = $Description;
            } else {
                trigger_error('Invalid description', E_USER_ERROR);
            }
        }
    }

    /**
     * Returns object for generating the client
     * @access public
     * @abstract
     */
    public function getGenerator()
    {
    }

    /**
     * Start serving (override in subclasses)
     * @access public
     * @abstact
     */
    public function serve()
    {
    }

    /**
     * Returns the portion of the URL to the right of the executed
     * PHP script e.g. http://localhost/index.php/foo/bar/ returns
     * 'foo/bar'. Returns the string up to the end or to the first ?
     * character
     * @return string
     * @access public
     * @static
     */
    public function getUriPath(): string
    {
        $basePath = explode('/', $this->resolveScriptName());
        $script   = array_pop($basePath);
        $basePath = implode('/', $basePath);

        // Determine URI path - path variables to the right of the PHP script
        if ($script && (false !== mb_strpos($_SERVER['REQUEST_URI'], $script))) {
            $uriPath = explode($script, $_SERVER['REQUEST_URI']);
            $uriPath = $uriPath[1];
        } else {
            $pattern = '/^' . str_replace('/', '\/', $basePath) . '/';
            $uriPath = preg_replace($pattern, '', $_SERVER['REQUEST_URI']);
        }
        if (false !== ($pos = mb_strpos($uriPath, '?'))) {
            $uriPath = mb_substr($uriPath, 0, $pos);
        }
        $uriPath = preg_replace(['/^\//', '/\/$/'], '', $uriPath);

        return $uriPath;
    }

    /**
     * Introspects the name of the script. Depending on the PHP SAPI
     * determining the name of the current script varies. This will probably
     * need updating later and testing under a number of environments
     * @return string script name
     * @access public
     */
    public function resolveScriptName(): string
    {
        if (Request::hasVar('PATH_INFO', 'SERVER') && $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME']) {
            $script_name = $_SERVER['PATH_INFO'];
        } else {
            $script_name = $_SERVER['SCRIPT_NAME'];
        }

        return $script_name;
    }

    /**
     * Load the error reader
     * @param string $lang (optional) 2 letter localization code e.g. 'en'
     * @param array  $app
     * @param array  $ser
     * @param array  $cli
     * @internal param array $list of Application_Errors to merge in
     * @internal param array $list of Server_Errors to merge in
     * @internal param array $list of Client_Errors to merge in
     * @todo     Break this function up
     * @access   public
     */
    public function loadErrorReader($lang = 'en', $app = [], $ser = [], $cli = []): void
    {
        require_once JPSPAN . 'Include.php';
        JPSpan_Include_ErrorReader($lang, $app, $ser, $cli);
    }

    /**
     * Display the Javascript client and exit
     * @access public
     */
    public function displayClient(): void
    {
        $G = $this->getGenerator();
        require_once JPSPAN . 'Include.php';
        //        $I = new JPSpan_Include()->instance();
        $I = &JPSpan_Include::instance();

        // HACK - this needs to change
        $I->loadString(__FILE__, $G->getClient());
        $client = $I->getCode();
        header('Content-Type: application/x-javascript');
        header('Content-Length: ' . mb_strlen($client));
        echo $client;
        exit();
    }
}
