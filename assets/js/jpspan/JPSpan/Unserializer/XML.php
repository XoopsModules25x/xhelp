<?php declare(strict_types=1);

//---------------------------------------------------------------------------

/**
 * Handles parsing of XML requests
 */
class JPSpan_Unserializer_XML
{
    /**
     * Dictionary of tag names to data node classes
     * @var array
     */
    public $dict;
    /**
     * Node stack
     * @var array
     */
    public $stack;
    /**
     * Root node
     * @var JPSpan_Unserializer_XML_Root
     */
    public $root;
    /**
     * Instance of the SAX parser
     * @var int
     */
    public $parser;
    /**
     * Whether there's an error in parsing
     * @var bool (default = FALSE)
     */
    public $isError = false;
    /**
     * Switch for when we're inside the root node
     * @var bool
     */
    public $inData = false;

    /**
     * Set's up the dictionary
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
     * @param $parser
     * @param $tag
     * @param $attrs
     */
    public function open(&$parser, $tag, $attrs): void
    {
        if (!array_key_exists($tag, $this->dict)) {
            $errorMsg = 'Illegal tag name: ' . $tag;
            $this->raiseError($errorMsg);

            return;
        }

        if ('r' === $tag) {
            $this->inData = true;
        }

        if ($this->inData) {
            $class = $this->dict[$tag];

            $current       = new $class($this, $attrs);
            $this->stack[] = &$current;

            if ('r' === $tag) {
                $this->root = &$current;
            }
        }
    }

    /**
     * Sax tag cdata callback
     * @param $parser
     * @param $data
     */
    public function cdata(&$parser, $data): void
    {
        $len = count($this->stack);
        if ($this->stack[$len - 1]->isString) {
            $this->stack[$len - 1]->readString($data);
        }
    }

    /**
     * Sax close tag callback
     * @param $parser
     * @param $tag
     */
    public function close(&$parser, $tag): void
    {
        if ('r' === $tag) {
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
     * @param mixed $msg
     */
    public function raiseError($msg): void
    {
        $this->isError = true;
        $msg           .= ' [byte index: ' . xml_get_current_byte_index($this->parser) . ']';
        trigger_error($msg, E_USER_ERROR);
    }

    /**
     * Unserialize some XML. If the provided param is not a string containing
     * an XML document, it will be returned as is
     * @param mixed $data
     * @return mixed unserialized data structure
     */
    public function unserialize($data)
    {
        // Return anything that's not XML immediately
        if (!is_string($data) || !preg_match('/^\s*<\?xml(.+)\?>/U', $data, $match)) {
            return $data;
        }

        $this->parser = xml_parser_create('UTF-8');
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
        }

        return false;
    }
}

//---------------------------------------------------------------------------

/**
 * Base class for represented data elements in XML
 */
class JPSpan_Unserializer_XML_Node
{
    /**
     * @var JPSpan_Unserializer_XML
     */
    public $Handler;
    /**
     * @var mixed node value
     */
    public $value;
    /**
     * @var bool switch to indentify JPSpan_Unserializer_XML_Element nodes
     */
    public $isElement = false;
    /**
     * @var bool switch to identify JPSpan_Unserializer_XML_String nodes
     */
    public $isString = false;

    /**
     * @param mixed $Handler
     */
    public function __construct(&$Handler)
    {
        $this->Handler = &$Handler;
    }

    /**
     * @param mixed $child
     */
    public function add($child): void
    {
        $errorMsg = 'Scalar nodes cannot have children';
        $this->Handler->raiseError($errorMsg);
    }
}

//---------------------------------------------------------------------------

/**
 * The root XML tag 'r'. Zero or one child tag allowed
 */
class JPSpan_Unserializer_XML_Root extends JPSpan_Unserializer_XML_Node
{
    /**
     * Switch to track whether root as single child node
     * @var bool
     */
    public $hasValue = false;

    /**
     * @param mixed $Handler
     * @param mixed $attrs
     */
    public function __construct(&$Handler, $attrs)
    {
        $this->Handler = &$Handler;
        $this->value   = null;
    }

    /**
     * @param mixed $child
     */
    public function add($child): void
    {
        if ($this->hasValue) {
            $errorMsg = 'Root node can only contain a single child node';
            $this->Handler->raiseError($errorMsg);
        } else {
            if (!$child->isElement) {
                $this->value    = $child->value;
                $this->hasValue = true;
            } else {
                $errorMsg = 'Element nodes can only be placed inside array or object nodes';
                $this->Handler->raiseError($errorMsg);
            }
        }
    }
}

//---------------------------------------------------------------------------

/**
 * Null variable 'n'. No children allowed
 */
class JPSpan_Unserializer_XML_Null extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param mixed $Handler
     * @param mixed $attrs
     */
    public function __construct(&$Handler, $attrs)
    {
        $this->Handler = &$Handler;
        $this->value   = null;
    }
}

//---------------------------------------------------------------------------

/**
 * Boolean variable 'b'. Attribute 'v' required. No children allowed
 */
class JPSpan_Unserializer_XML_Boolean extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param mixed $Handler
     * @param mixed $attrs
     */
    public function __construct(&$Handler, $attrs)
    {
        $this->Handler = &$Handler;

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
 */
class JPSpan_Unserializer_XML_Integer extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param mixed $Handler
     * @param mixed $attrs
     */
    public function __construct(&$Handler, $attrs)
    {
        $this->Handler = &$Handler;

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
 */
class JPSpan_Unserializer_XML_Double extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param mixed $Handler
     * @param mixed $attrs
     */
    public function __construct(&$Handler, $attrs)
    {
        $this->Handler = &$Handler;

        if (isset($attrs['v'])) {
            $this->value = (float)$attrs['v'];
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
 */
class JPSpan_Unserializer_XML_String extends JPSpan_Unserializer_XML_Node
{
    /**
     * Declare it's a string - instructs JPSpan_Unserializer_XML::cdata to
     * pass on string values
     * @var bool TRUE
     */
    public $isString = true;

    /**
     * @param mixed $Handler
     * @param mixed $attrs
     */
    public function __construct(&$Handler, $attrs)
    {
        $this->Handler = &$Handler;
        $this->value   = '';
    }

    /**
     * Read some more string
     * @param mixed $string
     */
    public function readString($string): void
    {
        $this->value .= $string;
    }
}

//---------------------------------------------------------------------------

/**
 * Array variable 'a' - can only contain 'e' tags - zero or more
 */
class JPSpan_Unserializer_XML_Array extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param mixed $Handler
     * @param mixed $attrs
     */
    public function __construct(&$Handler, $attrs)
    {
        $this->Handler = &$Handler;
        $this->value   = [];
    }

    /**
     * @param mixed $child
     */
    public function add($child): void
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
 */
class JPSpan_Unserializer_XML_Object extends JPSpan_Unserializer_XML_Node
{
    /**
     * @param mixed $Handler
     * @param mixed $attrs
     */
    public function __construct(&$Handler, $attrs)
    {
        $this->Handler = &$Handler;

        if (isset($attrs['c'])) {
            $class = $attrs['c'];

            if (!array_key_exists(mb_strtolower($class), $GLOBALS['_JPSPAN_UNSERIALIZER_MAP'])) {
                $errorMsg = 'Illegal object type: ' . \mb_strtolower($class);
                $this->Handler->raiseError($errorMsg);

                return;
            }

            $this->value = new $class();
        } else {
            $errorMsg = 'Object node requires class attribute';
            $this->Handler->raiseError($errorMsg);
        }
    }

    /**
     * @param mixed $child
     */
    public function add($child): void
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
 */
class JPSpan_Unserializer_XML_Element extends JPSpan_Unserializer_XML_Node
{
    /**
     * Value of element - defaults to NULL if no child
     * @var mixed value
     */
    public $value = null;
    /**
     * Element key (e.g. array index or object property name)
     * @var mixed key (string or integer)
     */
    public $key = null;
    /**
     * Declare it's an element
     * @var bool TRUE
     */
    public $isElement = true;

    /**
     * @param mixed $Handler
     * @param mixed $attrs
     */
    public function __construct(&$Handler, $attrs)
    {
        $this->Handler = &$Handler;

        if (isset($attrs['k'])) {
            $this->key = $attrs['k'];
        } else {
            $errorMsg = 'Element node requires key attribute';
            $this->Handler->raiseError($errorMsg);
        }
    }

    /**
     * @param mixed $child
     */
    public function add($child): void
    {
        if ($child->isElement) {
            $errorMsg = 'Element nodes can only be placed inside array or object nodes';
            $this->Handler->raiseError($errorMsg);
        } else {
            $this->value = $child->value;
        }
    }
}
