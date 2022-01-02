<?php
/**
 * @package    JPSpan
 * @subpackage Generator
 */

//--------------------------------------------------------------------------------
/**
 * Define
 */
if (!defined('JPSPAN')) {
    define('JPSPAN', __DIR__ . '/');
}

/**
 * Generaters client-side Javascript primed to access a server
 * Works with JPSpan_HandleDescription to generate
 * client primed for a server
 * @todo       Review this - may be worth eliminating - not serving much useful purpose
 * @see        JPSpan_Server::getGenerator()
 * @package    JPSpan
 * @subpackage Generator
 * @access     public
 */
class JPSpan_Generator
{
    /**
     * Object responsible for generating client
     * @var object
     * @access private
     */
    public $ClientGenerator;

    /**
     * Initialize the generator
     * @param mixed $ClientGenerator
     * @param mixed $descriptions
     * @param mixed $serverUrl
     * @param mixed $encoding
     * @access public
     * @todo   This method needs to die - just setup the ClientGenerator object
     */
    public function init(&$ClientGenerator, &$descriptions, $serverUrl, $encoding): void
    {
        $this->ClientGenerator                  = &$ClientGenerator;
        $this->ClientGenerator->descriptions    = &$descriptions;
        $this->ClientGenerator->serverUrl       = $serverUrl;
        $this->ClientGenerator->RequestEncoding = $encoding;
    }

    /**
     * Return the Javascript client for the server
     * @return string Javascript
     * @access public
     */
    public function getClient(): string
    {
        require_once JPSPAN . 'CodeWriter.php';
        $Code = new JPSpan_CodeWriter();
        $this->ClientGenerator->generate($Code);

        return $Code->toString();
    }
}

//--------------------------------------------------------------------------------

/**
 * @package    JPSpan
 * @subpackage Generator
 * @access     public
 */
class JPSpan_Generator_AdHoc
{
    public $descriptions    = [];
    public $RequestEncoding = 'xml';
    public $RequestMethod   = 'rawpost';
    public $jsRequestClass  = 'JPSpan_Request_RawPost';
    public $jsEncodingClass = 'JPSpan_Encode_Xml';

    /**
     * @param $description
     */
    public function addDescription($description): void
    {
        $this->descriptions[$description->jsClass] = $description;
    }

    /**
     * Invokes code generator
     * @param mixed $Code
     * @access public
     */
    public function generate($Code): void
    {
        switch ($this->RequestMethod) {
            case 'rawpost':
                $this->jsRequestClass = 'JPSpan_Request_RawPost';
                break;
            case 'post':
                $this->jsRequestClass = 'JPSpan_Request_Post';
                break;
            case 'get':
                // The JPSpan JS GetRequest object has bugs plus
                // changing state via GET is bad idea
                // https://www.intertwingly.net/blog/2005/03/16/AJAX-Considered-Harmful
                trigger_error('Sending data via GET vars not supported', E_USER_ERROR);
                break;
            default:
                trigger_error('Request method unknown: ' . $this->RequestMethod, E_USER_ERROR);
                break;
        }

        if ('xml' === $this->RequestEncoding) {
            $this->jsEncodingClass = 'JPSpan_Encode_Xml';
        } else {
            $this->jsEncodingClass = 'JPSpan_Encode_PHP';
        }

        $this->generateScriptHeader($Code);

        foreach (array_keys($this->descriptions) as $key) {
            $this->generateJsClass($Code, $this->descriptions[$key]);
        }
    }

    /**
     * Generate the starting includes section of the script
     * @param mixed $Code
     * @access private
     */
    public function generateScriptHeader($Code): void
    {
        ob_start(); ?>
        /**@ * require_once __DIR__   . '/remoteobject.js';
        <?php
        switch ($this->RequestMethod) {
            case 'rawpost':
                ?>
                * require_once __DIR__   . '/request/rawpost.js';
                <?php
                break;
            case 'post':
                ?>
                * require_once __DIR__   . '/request/rawpost.js';
                <?php
                break;
        }

        if ('xml' === $this->RequestEncoding) {
            ?>

            * require_once __DIR__   . '/encode/xml.js';
            <?php
        } else {
            ?>
            * require_once __DIR__   . '/encode/php.js';
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
     * @access private
     */
    public function generateJsClass($Code, $Description): void
    {
        ob_start(); ?>

        function
        <?php echo $Description->Class; ?>
        () { var oParent = new JPSpan_RemoteObject(); if (arguments[0]) {
        oParent.Async(arguments[0]); } oParent.__remoteClass = '
        <?php echo $Description->Class; ?>
        '; oParent.__request = new
        <?php echo $this->jsRequestClass; ?>
        (new
        <?php echo $this->jsEncodingClass; ?>
        ());
        <?php
        foreach ($Description->methods as $method => $url) {
            ?>

            // @access public oParent.
            <?php echo $method; ?>
            = function() { return this.__call('
            <?php echo $url; ?>
            ',arguments,'
            <?php echo $method; ?>
            '); };
            <?php
        } ?>

        return oParent; }

        <?php
        $Code->append(ob_get_clean());
    }

    /**
     * @return string
     */
    public function getClient(): string
    {
        require_once JPSPAN . 'CodeWriter.php';
        $Code = new JPSpan_CodeWriter();
        $this->generate($Code);
        $client = $Code->toString();

        require_once JPSPAN . 'Include.php';
        $I = (new JPSpan_include()))->instance(;

        // HACK - this needs to change
        $I->loadString(__FILE__, $client);

        return $I->getCode();
    }
}

//--------------------------------------------------------------------------------

/**
 * @package    JPSpan
 * @subpackage Generator
 * @access     public
 */
class JPSpan_Generator_AdHoc_Description
{
    public $Class;
    /**
     * Map of method name to URL endpoint for method
     */
    public $methods = [];
}
