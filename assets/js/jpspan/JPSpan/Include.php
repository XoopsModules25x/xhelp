<?php
/**
 * @package    JPSpan
 * @subpackage Include
 */

//-----------------------------------------------------------------------------

/**
 * When to compress the Javascript (remove whitespace formatting)
 * Set to TRUE and Javascript will be "compressed"
 */
if (!defined('JPSPAN_INCLUDE_COMPRESS')) {
    define('JPSPAN_INCLUDE_COMPRESS', false);
}

//-----------------------------------------------------------------------------
/**
 * Make sure a file_get_contents() implementation exists
 * PHP backwards compatability
 */
if (!function_exists('file_get_contents')) {
    /**
     * @see        https://www.php.net/file_get_contents
     * @param mixed $filename
     * @return string file content
     * @package    JPSpan
     * @subpackage Include
     */
    function file_get_contents($filename): string
    {
        $fd      = fopen((string)$filename, 'rb');
        $content = fread($fd, filesize($filename));
        fclose($fd);

        return $content;
    }
}
//-----------------------------------------------------------------------------

/**
 * Include a Javascript file. Filename must be relative to the
 * JPSpan/js/ directory (without a leading slash if in subdir)
 * This is the recommended point to include Javascript files
 * Calling this registers a shutdown function which takes care of displaying
 * the Javascript
 * @param mixed $file
 * @access     public
 * @subpackage Include
 * @package    JPSpan
 */
function JPSpan_Include($file)
{
    $Includer = (new JPSpan_include()))->instance(
        ;
        $Includer->loadFile($file);
        register_shutdown_function('JPSpan_Include_Shutdown');
        }

//-----------------------------------------------------------------------------
/**
 * PHP shutdown function making sure Javascript is displayed
 * @package    JPSpan
 * @subpackage Include
 * @access     private
 */
function JPSpan_Include_Shutdown()
{
    $Includer = (new JPSpan_include())
    )->instance(
        ;
            echo $Includer->getCode();
        }

//-----------------------------------------------------------------------------

/**
 * Loads Javascript but does not register shutdown fn
 * @param mixed $file
 * @access     public
 * @package    JPSpan
 * @subpackage Include
 * @see        JPSpan_Include
 */
function JPSpan_Include_Register($file)
{
    $Includer = (new JPSpan_include())
    )->instance(;
                $Includer->loadFile($file);
        }

//-----------------------------------------------------------------------------

/**
 * Loads the Javascript error reader
 * @param string $lang (optional) 2 letter localization code e.g. 'en'
 * @param array  $app
 * @param array  $ser
 * @param array  $cli
 * @see        JPSpan_Include
 * @package    JPSpan
 * @subpackage Include
 * @internal   param array $list of Application_Errors to merge in
 * @internal   param array $list of Server_Errors to merge in
 * @internal   param array $list of Client_Errors to merge in
 * @todo       Break this function up
 * @access     public
 */
function JPSpan_Include_ErrorReader($lang = 'en', $app = [], $ser = [], $cli = [])
{
    $errorfile = 'errors.' . $lang . '.ini';
    if (!file_exists(JPSPAN . 'errors/' . $errorfile)) {
        $errorfile = 'errors.en.ini';
    }

    $errors = parse_ini_file(JPSPAN . 'errors/' . $errorfile, true);

    $script = "/**@\n* require_once __DIR__   . '/util/errorreader.js';\n*/\n";
    // Use Object instead of Array as Javascript will fill empty elements
    $script .= "JPSpan_Util_ErrorReader.prototype.errorList = new Object();\n";

    foreach ($errors['Client_Error'] as $key => $value) {
        $value  = addcslashes($value, "\000\042\047\134");
        $script .= "JPSpan_Util_ErrorReader.prototype.errorList[$key] = '$value';\n";
    }

    foreach ($cli as $key => $value) {
        if (array_key_exists($key, $errors['Client_Error'])) {
            continue;
        }
        $value  = addcslashes($value, "\000\042\047\134");
        $script .= "JPSpan_Util_ErrorReader.prototype.errorList[$key] = '$value';\n";
    }

    foreach ($errors['Server_Error'] as $key => $value) {
        $value  = addcslashes($value, "\000\042\047\134");
        $script .= "JPSpan_Util_ErrorReader.prototype.errorList[$key] = '$value';\n";
    }

    foreach ($ser as $key => $value) {
        if (array_key_exists($key, $errors['Server_Error'])) {
            continue;
        }
        $value  = addcslashes($value, "\000\042\047\134");
        $script .= "JPSpan_Util_ErrorReader.prototype.errorList[$key] = '$value';\n";
    }

    foreach ($errors['Application_Error'] as $key => $value) {
        $value  = addcslashes($value, "\000\042\047\134");
        $script .= "JPSpan_Util_ErrorReader.prototype.errorList[$key] = '$value';\n";
    }

    foreach ($app as $key => $value) {
        if (array_key_exists($key, $errors['Application_Error'])) {
            continue;
        }
        $value  = addcslashes($value, "\000\042\047\134");
        $script .= "JPSpan_Util_ErrorReader.prototype.errorList[$key] = '$value';\n";
    }

    $Includer = (new JPSpan_include())
    )->instance(
        ;
                    $Includer->loadString('errorreaderlist', $script);
                }

//-----------------------------------------------------------------------------

/**
 * Returns all loaded Javascript
 * @return string
 * @access     public
 * @package    JPSpan
 * @subpackage Include
 * @see        JPSpan_Include
 */
function JPSpan_Includes_Fetch(): string
{
    $Includer = (new JPSpan_include())
    )->instance(;

                        return $Includer->getCode();
                    }

//-----------------------------------------------------------------------------

/**
 * Displays all loaded Javascript
 * @see        JPSpan_Include
 * @package    JPSpan
 * @subpackage Include
 * @access     public
 */
function JPSpan_Includes_Display()
{
    echo JPSpan_Includes_Fetch();
}

//-----------------------------------------------------------------------------

/**
 * Front for dealing with includes
 * @package    JPSpan
 * @subpackage Include
 * @access     public
 */
class JPSpan_Include
{
    /**
     * @var JPSpan_Include_Manager
     * @access private
     */
    public $Manager;

    /**
     * Do not construct JPSpan_Include directly! Use instance method
     * @see    instance
     * @access private
     */
    public function __construct()
    {
        $this->Manager = new JPSpan_Include_Manager();
    }

    /**
     * Load a Javascript file
     * @param mixed $file
     * @access public
     */
    public function loadFile($file): void
    {
        $file = JPSPAN . 'js/' . $file;
        $this->Manager->loadFile($file);
    }

    /**
     * Load a Javascript script from a string
     * @param $name
     * @param $src
     * @internal param source $string code
     * @access   public
     */
    public function loadString($name, $src): void
    {
        $this->Manager->load($name, $src);
    }

    /**
     * Get the code
     * @return string Javascript
     * @access public
     */
    public function getCode(): string
    {
        if (JPSPAN_INCLUDE_COMPRESS) {
            require_once JPSPAN . 'Script.php';
            $code = $this->Manager->getCode();

            return (new JPSpan_Script())->compress($code);
        }

        return $this->Manager->getCode();
    }

    /**
     * Obtain singleton instance of JPSpan_Include
     * @return JPSpan_Include
     * @access public
     * @static
     */
    public function &instance(): ?\JPSpan_Include
    {
        static $importer = null;

        if (!$importer) {
            $importer = new self();
        }

        return $importer;
    }
}

//-----------------------------------------------------------------------------

/**
 * Manages the includes, making sure dependencies are resolved
 * @package    JPSpan
 * @subpackage Include
 * @access     protected
 */
class JPSpan_Include_Manager
{
    /**
     * List of files to include. Dependencies are added at end of list
     * @var array
     * @access private
     */
    public $includes = [];
    /**
     * Map of file name to source code
     * @var array
     * @access private
     */
    public $code = [];

    /**
     * Load a Javascript file
     * @param mixed $file
     * @access protected
     */
    public function loadFile($file): void
    {
        $src = file_get_contents($file);
        $this->load($file, $src);
    }

    /**
     * Load a Javascript contained in a string
     * @param mixed $name
     * @param mixed $src
     * @access protected
     */
    public function load($name, $src): void
    {
        if (!in_array($name, $this->includes)) {
            $this->includes[] = $name;
            $File             = new JPSpan_Include_File($this);
            $File->parse($src);
            $this->code[$name] = $File->src;
            $this->resolveDependencies($File->includes);
        }
    }

    /**
     * Resolve any dependencies a script has on others
     * @param mixed $includes
     * @access private
     */
    public function resolveDependencies($includes): void
    {
        foreach ($includes as $include) {
            $src = file_get_contents($include);
            $this->load($include, $src);
        }
    }

    /**
     * Get the source
     * @return string
     * @access protected
     */
    public function getCode(): string
    {
        $includes = array_reverse($this->includes);
        $code     = '';
        foreach ($includes as $include) {
            $code .= $this->code[$include];
        }

        return $code;
    }
}

//-----------------------------------------------------------------------------

/**
 * Represents a single file - manages parsing the file for dependencies
 * Right now this does no error checking / validation of parse files
 * @package    JPSpan
 * @subpackage Include
 * @access     protected
 */
class JPSpan_Include_File
{
    /**
     * List of dependencies, obtained from parsing the source
     * @var array
     * @access protected
     */
    public $includes = [];
    /**
     * Source code with dependency statements removed
     * @var string
     * @access protected
     */
    public $src = '';

    /**
     * Parse the file for dependencies
     * @param mixed $src
     * @access protected
     */
    public function parse($src): void
    {
        $Parser = new JPSpan_Include_Parser($this);
        $Parser->parse($src);
    }

    /**
     * Parser handler
     * @param mixed $script
     * @param mixed $state
     * @return bool TRUE
     * @access protected
     */
    public function script($script, $state): bool
    {
        $this->src .= $script;

        return true;
    }

    /**
     * Parser handler (discards)
     * @param mixed $decl
     * @param mixed $state
     * @return bool TRUE
     * @access protected
     */
    public function declaration($decl, $state): bool
    {
        return true;
    }

    /**
     * Parser handler - handles include statements
     * @param mixed $file
     * @param mixed $state
     * @return bool TRUE
     * @access protected
     */
    public function inc($file, $state): bool
    {
        if (JPSPAN_LEXER_UNMATCHED == $state) {
            $file             = str_replace(["'", '"'], '', $file);
            $this->includes[] = JPSPAN . 'js/' . trim($file);
        }

        return true;
    }
}

//-----------------------------------------------------------------------------

/**
 * Parses source for include statements
 * @package    JPSpan
 * @subpackage Include
 * @access     protected
 */
class JPSpan_Include_Parser
{
    /**
     * Callback handler for parser
     * @var JPSpan_Include_File
     * @access private
     */
    public $Handler;

    /**
     * @param mixed $Handler
     * @access protected
     */
    public function __construct(&$Handler)
    {
        $this->Handler = &$Handler;
    }

    /**
     * Parse some Javascript
     * @param mixed $src
     * @access protected
     */
    public function parse($src): void
    {
        $Lexer = &$this->getLexer();
        $Lexer->parse($src);
    }

    /**
     * Create the Lexer
     * @return JPSpan_Lexer
     * @access private
     * @see    JPSpan_Lexer
     */
    public function &getLexer(): \JPSpan_Lexer
    {
        require_once JPSPAN . 'Lexer.php';
        $Lexer = new JPSpan_Lexer($this->Handler, 'script');

        $Lexer->addEntryPattern('/\*\*@', 'script', 'declaration');
        $Lexer->addExitPattern('\*/', 'declaration');

        $Lexer->addEntryPattern('include', 'declaration', 'inc');
        $Lexer->addExitPattern(';', 'inc');

        return $Lexer;
    }
}
