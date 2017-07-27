<?php
/**
 * @package    JPSpan
 * @subpackage Unserialzier
 */

//---------------------------------------------------------------------------

/**
 * Handles parsing of XML requests
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     public
 */
class JPSpan_Unserializer_XML
{
    /**
     * Dictionary of tag names to data node classes
     * @var array
     * @access private
     */
    public $dict;

    /**
     * Node stack
     * @var array
     * @access private
     */
    public $stack;

    /**
     * Root node
     * @var JPSpan_Unserializer_XML_Root
     * @access private
     */
    public $root;

    /**
     * Instance of the SAX parser
     * @var int
     * @access private
     */
    public $parser;

    /**
     * Whether there's an error in parsing
     * @var boolean (default = FALSE)
     * @access private
     */
    public $isError = false;

    /**
     * Switch for when we're inside the root node
     * @var boolean
     * @access private
     */
    public $inData = false;

    /**
     * Set's up the dictionary
     * @access public
     */
    public function __construct()
    {
        $this->dict = [
            'r' => 'JPSpan_Unserializer_XML_Root',
            'n' => 'JPSpan_Unserializer_XML_Null',
            'b' => 'JPSpan_Unserializer_XML_Boolean',
            'i' => 'JPSpan_Unserializer_XML_Integer',
            'd' => 'JPSpan_Unserializer_XML_Double',
            's' => 'JPSpan_Unserializer_XML_String',
            'a' => 'JPSpan_Unserializer_XML_Array',
            'o' => 'JPSpan_Unserializer_XML_Object',
            'e' => 'JPSpan_Unserializer_XML_Element',
        ];
    }

    /**
     * Sax open tag callback
     * @access private
     * @param $parser
     * @param $tag
     * @param $attrs
     */
    public function open(& $parser, $tag, $attrs)
    {
        if (!array_key_exists($tag, $this->dict)) {
            $errorMsg = 'Illegal tag name: ' . $tag;
            $this->raiseError($errorMsg);

            return;
        }

        if ($tag == 'r') {
            $this->inData = true;
        }

        if ($this->inData) {
            $class = $this->dict[$tag];

            $current       = new $class($this, $attrs);
            $this->stack[] =& $current;

            if ($tag == 'r') {
                $this->root =& $current;
            }
        }
    }

    /**
     * Sax tag cdata callback
     * @access private
     * @param $parser
     * @param $data
     */
    public function cdata(& $parser, $data)
    {
        $len = count($this->stack);
        if ($this->stack[$len - 1]->isString) {
            $this->stack[$len - 1]->readString($data);
        }
    }

    /**
     * Sax close tag callback
     * @access private
     * @param $parser
     * @param $tag
     */
    public function close(& $parser, $tag)
    {
        if ($tag == 'r') {
            $this->inData = false;
        }

        if ($this->inData) {
            $len = count($this->stack);

            $this->stack[$len - 2]->add($this->stack[$len - 1]);

            array_pop($this->stack);
        }
    }

    /**
     * Raise an error
     * @param string error message
     * @access private
     * @return void
     */
    public function raiseError($msg)
    {
        $this->isError = true;
        $msg           .= ' [byte index: ' . xml_get_current_byte_index($this->parser) . ']';
        trigger_error($msg, E_USER_ERROR);
    }

    /**
     * Unserialize some XML. If the provided param is not a string containing
     * an XML document, it will be returned as is
     * @param string XML to unserialize
     * @return mixed unserialized data structure
     * @access public
     */
    public function unserialize($data)
    {
        // Return anything that's not XML immediately
        if (!is_string($data) || !preg_match("/^\s*<\?xml(.+)\?>/U", $data, $match)) {
            return $data;
        }

        $this->parser = &xml_parser_create('UTF-8');
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_object($this->parser, $this);
        xml_set_elementHandler($this->parser, 'open', 'close');
        xml_set_character_dataHandler($this->parser, 'cdata');

        if (!xml_parse($this->parser, trim($data), true)) {
            $errorCode = xml_get_error_code($this->parser);
            $errorMsg  = 'Badly formed XML: (' . $errorCode . ') ' . xml_error_string($this->parser);
            $this->raiseError($errorMsg);
        }

        @xml_parser_free($this->parser);

        if (!$this->isError) {
            return $this->root->value;
        } else {
            return false;
        }
    }
}

//---------------------------------------------------------------------------

/**
 * Base class for represented data elements in XML
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     protected
 */
class JPSpan_Unserializer_XML_Node
{
    /**
     * @var JPSpan_Unserializer_XML
     * @access protected
     */
    public $Handler;

    /**
     * @var mixed node value
     * @access protected
     */
    public $value;

    /**
     * @var boolean switch to indentify JPSpan_Unserializer_XML_Element nodes
     * @access protected
     */
    public $isElement = false;

    /**
     * @var boolean switch to identify JPSpan_Unserializer_XML_String nodes
     * @access protected
     */
    public $isString = false;

    /**
     * @param JPSpan_Unserializer_XML
     * @access protected
     */
    public function __construct(& $Handler)
    {
        $this->Handler =& $Handler;
    }

    /**
     * @param JPSpan_Unserializer_XML_Node subclass
     * @return void
     * @access protected
     */
    public function add($child)
    {
        $errorMsg = 'Scalar nodes cannot have children';
        $this->Handler->raiseError($errorMsg);
    }
}

//---------------------------------------------------------------------------

/**
 * The root XML tag 'r'. Zero or one child tag allowed
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     protected
 */
class JPSpan_Unserializer_XML_Root extends JPSpan_Unserializer_XML_Node
{
    /**
     * Switch to track whether root as single child node
     * @var boolean
     * @access private
     */
    public $hasValue = false;

    /**
     * @param JPSpan_Unserializer_XML
     * @param array XML attributes
     * @access protected
     */
    public function __construct(& $Handler, $attrs)
    {
        $this->Handler =& $Handler;
        $this->value   = null;
    }

    /**
     * @param JPSpan_Unserializer_XML_Node subclass
     * @return void
     * @access protected
     */
    public function add($child)
    {
        if (!$this->hasValue) {
            if (!$child->isElement) {
                $this->value    = $child->value;
                $this->hasValue = true;
            } else {
                $errorMsg = 'Element nodes can only be placed inside array or object nodes';
                $this->Handler->raiseError($errorMsg);
            }
        } else {
            $errorMsg = 'Root node can only contain a single child node';
            $this->Handler->raiseError($errorMsg);
        }
    }
}

//---------------------------------------------------------------------------

/**
 * Null variable 'n'. No children allowed
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     protected
 */
class JPSpan_Unserializer_XML_Null extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param JPSpan_Unserializer_XML
     * @param array XML attributes
     * @access protected
     */
    public function __construct(& $Handler, $attrs)
    {
        $this->Handler =& $Handler;
        $this->value   = null;
    }
}

//---------------------------------------------------------------------------

/**
 * Boolean variable 'b'. Attribute 'v' required. No children allowed
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     protected
 */
class JPSpan_Unserializer_XML_Boolean extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param JPSpan_Unserializer_XML
     * @param array XML attributes
     * @access protected
     */
    public function __construct(& $Handler, $attrs)
    {
        $this->Handler =& $Handler;

        if (isset($attrs['v'])) {
            $this->value = (bool)$attrs['v'];
        } else {
            $errorMsg = 'Value required for boolean';
            $this->Handler->raiseError($errorMsg);
        }
    }
}

//---------------------------------------------------------------------------

/**
 * Integer variable 'i'. Attribute 'v' required. No children allowed
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     protected
 */
class JPSpan_Unserializer_XML_Integer extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param JPSpan_Unserializer_XML
     * @param array XML attributes
     * @access protected
     */
    public function __construct(& $Handler, $attrs)
    {
        $this->Handler =& $Handler;

        if (isset($attrs['v'])) {
            $this->value = (int)$attrs['v'];
        } else {
            $errorMsg = 'Value required for integer';
            $this->Handler->raiseError($errorMsg);
        }
    }
}

//---------------------------------------------------------------------------

/**
 * Double variable 'd' - 'v' attribute required. No children allowed
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     protected
 */
class JPSpan_Unserializer_XML_Double extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param JPSpan_Unserializer_XML
     * @param array XML attributes
     * @access protected
     */
    public function __construct(& $Handler, $attrs)
    {
        $this->Handler =& $Handler;

        if (isset($attrs['v'])) {
            $this->value = (double)$attrs['v'];
        } else {
            $errorMsg = 'Value required for double';
            $this->Handler->raiseError($errorMsg);
        }
    }
}

//---------------------------------------------------------------------------

/**
 * String variable 's' - value passed from JPSpan_Unserializer_XML::cdata
 * No child tags allowed
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     protected
 */
class JPSpan_Unserializer_XML_String extends JPSpan_Unserializer_XML_Node
{
    /**
     * Declare it's a string - instructs JPSpan_Unserializer_XML::cdata to
     * pass on string values
     * @var boolean TRUE
     * @access private
     */
    public $isString = true;

    /**
     * @param JPSpan_Unserializer_XML
     * @param array XML attributes
     * @access protected
     */
    public function __construct(& $Handler, $attrs)
    {
        $this->Handler =& $Handler;
        $this->value   = '';
    }

    /**
     * Read some more string
     * @param string
     * @return void
     * @access protected
     */
    public function readString($string)
    {
        $this->value .= $string;
    }
}

//---------------------------------------------------------------------------

/**
 * Array variable 'a' - can only contain 'e' tags - zero or more
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     protected
 */
class JPSpan_Unserializer_XML_Array extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param JPSpan_Unserializer_XML
     * @param array XML attributes
     * @access protected
     */
    public function __construct(& $Handler, $attrs)
    {
        $this->Handler =& $Handler;
        $this->value   = [];
    }

    /**
     * @param JPSpan_Unserializer_XML_Node subclass
     * @return void
     * @access protected
     */
    public function add($child)
    {
        if ($child->isElement && null !== $child->key) {
            $this->value[$child->key] = $child->value;
        } else {
            $errorMsg = 'Array nodes can only contain element nodes';
            $this->Handler->raiseError($errorMsg);
        }
    }
}

//---------------------------------------------------------------------------

/**
 * Object variable 'o'. Attribute 'c' (class name) required
 * Can only contain 'e' tags - zero or more
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     protected
 */
class JPSpan_Unserializer_XML_Object extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param JPSpan_Unserializer_XML
     * @param array XML attributes
     * @access protected
     */
    public function __construct(& $Handler, $attrs)
    {
        $this->Handler =& $Handler;

        if (isset($attrs['c'])) {
            $class = $attrs['c'];

            if (!array_key_exists(strtolower($class), $GLOBALS['_JPSPAN_UNSERIALIZER_MAP'])) {
                $errorMsg = 'Illegal object type: ' . strtolower($class);
                $this->Handler->raiseError($errorMsg);

                return;
            }

            $this->value = new $class;
        } else {
            $errorMsg = 'Object node requires class attribute';
            $this->Handler->raiseError($errorMsg);
        }
    }

    /**
     * @param JPSpan_Unserializer_XML_Node subclass
     * @return void
     * @access protected
     */
    public function add($child)
    {
        if ($child->isElement && $child->key) {
            $this->value->{$child->key} = $child->value;
        } else {
            $errorMsg = 'Object nodes can only contain element nodes';
            $this->Handler->raiseError($errorMsg);
        }
    }
}

//---------------------------------------------------------------------------

/**
 * Array element or object property variable 'e'. Attribute 'k' (key) required
 * Can contain zero or one child tags
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     protected
 */
class JPSpan_Unserializer_XML_Element extends JPSpan_Unserializer_XML_Node
{
    /**
     * Value of element - defaults to NULL if no child
     * @var mixed value
     * @access protected
     */
    public $value = null;

    /**
     * Element key (e.g. array index or object property name)
     * @var mixed key (string or integer)
     * @access protected
     */
    public $key = null;

    /**
     * Declare it's an element
     * @var boolean TRUE
     * @access protected
     */
    public $isElement = true;

    /**
     * @param JPSpan_Unserializer_XML
     * @param array XML attributes
     * @access protected
     */
    public function __construct(& $Handler, $attrs)
    {
        $this->Handler =& $Handler;

        if (isset($attrs['k'])) {
            $this->key = $attrs['k'];
        } else {
            $errorMsg = 'Element node requires key attribute';
            $this->Handler->raiseError($errorMsg);
        }
    }

    /**
     * @param JPSpan_Unserializer_XML_Node subclass
     * @return void
     * @access protected
     */
    public function add($child)
    {
        if (!$child->isElement) {
            $this->value = $child->value;
        } else {
            $errorMsg = 'Element nodes can only be placed inside array or object nodes';
            $this->Handler->raiseError($errorMsg);
        }
    }
}
