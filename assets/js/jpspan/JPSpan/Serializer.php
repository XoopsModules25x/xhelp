<?php declare(strict_types=1);
/**
 * Library for serializing PHP variables into Javascript for use with
 * Javascript eval()
 */

//-----------------------------------------------------------------------------

/**
 * Includes
 */
require_once JPSPAN . 'CodeWriter.php';
//-----------------------------------------------------------------------------

/**
 * Define global for mapping PHP types to element generation
 * classes
 */
$GLOBALS['_JPSPAN_SERIALIZER_MAP'] = [
    'string'       => [
        'class' => 'JPSpan_SerializedString',
        'file'  => null,
    ],
    'integer'      => [
        'class' => 'JPSpan_SerializedInteger',
        'file'  => null,
    ],
    'boolean'      => [
        'class' => 'JPSpan_SerializedBoolean',
        'file'  => null,
    ],
    'double'       => [
        'class' => 'JPSpan_SerializedFloat',
        'file'  => null,
    ],
    'null'         => [
        'class' => 'JPSpan_SerializedNull',
        'file'  => null,
    ],
    'array'        => [
        'class' => 'JPSpan_SerializedArray',
        'file'  => null,
    ],
    'object'       => [
        'class' => 'JPSpan_SerializedObject',
        'file'  => null,
    ],
    'jpspan_error' => [
        'class' => 'JPSpan_SerializedError',
        'file'  => null,
    ],
];
//-----------------------------------------------------------------------------

/**
 * Serializes PHP data types into a JavaScript string containing an Function
 * object for use with eval()<br>
 * Based on Frederic Saunier's JSserializerCLASS<br>
 * Example:
 * <pre>
 * $myVar = 'Hello World!';
 * echo JPSpan_Serializer::serialize($myVar);
 * // Displays: new Function("var t1 = \'Hello World!\';return t1;");
 * </pre>
 * Use in Javascript would be;
 * <pre>
 * var data_serialized = 'new Function("var t1 = \'Hello World!\';return t1;");';
 * var data_func = eval(data_serialized);
 * var data = data_func(); // data now contains string: Hello World!
 * </pre>
 * @see        https://www.tekool.net/php/js_serializer/
 */
class JPSpan_Serializer
{
    /**
     * Serializes a PHP data structure into Javascript
     * @param mixed $data
     * @return string data as Javascript
     * @static
     */
    public function serialize($data)
    {
        JPSpan_getTmpVar(true);
        $code = new JPSpan_CodeWriter();
        $root = new JPSpan_RootElement($data);
        $root->generate($code);

        return $code->toString();
    }

    /**
     * Adds an entry to the type map
     * @param mixed      $type
     * @param mixed      $class
     * @param null|mixed $file
     * @static
     */
    public function addType($type, $class, $file = null): void
    {
        $GLOBALS['_JPSPAN_SERIALIZER_MAP'][mb_strtolower($type)] = [
            'class' => $class,
            'file'  => $file,
        ];
    }

    /**
     * Determine the type of a PHP value, returning an object
     * used to generated a serialized Javascript representation
     * @param mixed $data
     * @return object subclass of JPSpan_SerializedElement
     */
    public function &reflect($data)
    {
        $type = \mb_strtolower(gettype($data));
        if ('object' === $type) {
            $objtype = \mb_strtolower(get_class($data));
            if (array_key_exists($objtype, $GLOBALS['_JPSPAN_SERIALIZER_MAP'])) {
                $type = $objtype;
            }
        }
        if (array_key_exists($type, $GLOBALS['_JPSPAN_SERIALIZER_MAP'])) {
            $class = $GLOBALS['_JPSPAN_SERIALIZER_MAP'][$type]['class'];
            $file  = $GLOBALS['_JPSPAN_SERIALIZER_MAP'][$type]['file'];
            if (null !== $file) {
                require_once $file;
            }
            $element = new $class();
        } else {
            $element = new JPSpan_SerializedNull();
        }
        $element->setTmpVar();
        $element->setValue($data);

        return $element;
    }
}

//-----------------------------------------------------------------------------

/**
 * Function for generating temporary variable names for use in
 * serialized Javascript. Uses a static counter to keep names
 * unique
 * @param bool $refresh
 * @return string e.g. t2
 */
function JPSpan_getTmpVar($refresh = false)
{
    static $count = 1;
    if (!$refresh) {
        $name = 't' . $count;
        ++$count;

        return $name;
    }
    $count = 1;
}

//-----------------------------------------------------------------------------

/**
 * Wraps the generated JavaScript in an anonymous function
 */
class JPSpan_RootElement
{
    /**
     * Data to be serialized
     * @var mixed
     */
    public $data;

    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Triggers code generation for child data structure then wraps
     * in anonymous function
     * @param mixed $code
     */
    public function generate($code): void
    {
        $child = &(new JPSpan_Serializer())->reflect($this->data);
        $child->generate($code);

        $code->write('new Function("' . addcslashes($code->toString(), "\000\042\047\134") . $child->getReturn() . '");');
    }
}

/**
 * Base of class hierarchy for generating Javascript
 * @abstract
 */
class JPSpan_SerializedElement
{
    /**
     * Value of the element - used only for scalar types
     * @var mixed
     */
    public $value;
    /**
     * Temporary variable name to use in serialized Javascript
     * @var string
     */
    public $tmpName;

    /**
     * Sets the value of the element
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * Sets the temporary variable name
     */
    public function setTmpVar(): void
    {
        $this->tmpName = JPSpan_getTmpVar();
    }

    /**
     * JavaScript string to return if this is the root data element
     * Called from JPSpan_RootElement::generate
     * @return string
     */
    public function getReturn()
    {
        return 'return ' . $this->tmpName . ';';
    }

    /**
     * Template method for generating code
     * @param mixed $code
     */
    public function generate(&$code): void
    {
    }
}

//-----------------------------------------------------------------------------

/**
 * Generates the representation of a string in Javascript
 */
class JPSpan_SerializedString extends JPSpan_SerializedElement
{
    /**
     * @param mixed $code
     */
    public function generate($code): void
    {
        $value = addcslashes($this->value, "\000\042\047\134");
        $value = str_replace("\r\n", '\n', $value);
        $value = str_replace("\n", '\n', $value);
        $value = str_replace("\r", '\n', $value);
        $value = str_replace("\t", '\t', $value);
        $code->append("var {$this->tmpName} = '$value';");
    }
}

//-----------------------------------------------------------------------------

/**
 * Generates the representation of a boolean value in Javascript
 */
class JPSpan_SerializedBoolean extends JPSpan_SerializedElement
{
    /**
     * @param mixed $code
     */
    public function generate($code): void
    {
        if ($this->value) {
            $code->append("var {$this->tmpName} = true;");
        } else {
            $code->append("var {$this->tmpName} = false;");
        }
    }
}

//-----------------------------------------------------------------------------

/**
 * Generates the representation of an integer value in Javascript
 */
class JPSpan_SerializedInteger extends JPSpan_SerializedElement
{
    /**
     * @param mixed $code
     */
    public function generate($code): void
    {
        $code->append("var {$this->tmpName} = parseInt('{$this->value}');");
    }
}

//-----------------------------------------------------------------------------

/**
 * Generates the representation of a float value in Javascript
 */
class JPSpan_SerializedFloat extends JPSpan_SerializedElement
{
    /**
     * @param mixed $code
     */
    public function generate($code): void
    {
        $code->append("var {$this->tmpName} = parseFloat('{$this->value}');");
    }
}

//-----------------------------------------------------------------------------

/**
 * Generates the representation of a null value in Javascript
 */
class JPSpan_SerializedNull extends JPSpan_SerializedElement
{
    /**
     * @param mixed $code
     */
    public function generate($code): void
    {
        $code->append("var {$this->tmpName} = null;");
    }
}

//-----------------------------------------------------------------------------

/**
 * Generates the representation of an array in Javascript
 */
class JPSpan_SerializedArray extends JPSpan_SerializedElement
{
    /**
     * Representations of the elements of the array
     * @var array
     */
    public $children = [];

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        foreach ($value as $key => $value) {
            $this->children[$key] = &(new JPSpan_Serializer())->reflect($value);
        }
    }

    /**
     * @param mixed $code
     */
    public function generate(&$code): void
    {
        $code->append("var {$this->tmpName} = new Array();");
        foreach (array_keys($this->children) as $key) {
            $this->children[$key]->generate($code);
            $tmpName = $this->children[$key]->tmpName;
            // Spot the difference between index and hash keys..
            if (preg_match('/^[0-9]+$/', $key)) {
                $code->append("{$this->tmpName}[$key] = $tmpName;");
            } else {
                $code->append("{$this->tmpName}['$key'] = $tmpName;");
            }
        }

        // Override Javascript toString to display hash values
        $toString = 'function() { ';
        $toString .= "var str = '[';";
        $toString .= "var sep = '';";
        $toString .= 'for (var prop in this) { ';
        $toString .= "if (prop == 'toString') { continue; }";
        $toString .= "str+=sep+prop+': '+this[prop];";
        $toString .= "sep = ', ';";
        $toString .= "} return str+']';";
        $toString .= '}';

        $code->append("{$this->tmpName}.toString = $toString;");
    }
}

//-----------------------------------------------------------------------------

/**
 * Generates the representation of an object in Javascript
 */
class JPSpan_SerializedObject extends JPSpan_SerializedElement
{
    /**
     * Name for Javascript object
     * @var string (= Object)
     */
    public $classname = 'Object';
    /**
     * Representations of the properties of the object
     * @var array
     */
    public $children = [];

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->setChildValues($value);
    }

    /**
     * Called from setValue. Sets the value of all children of
     * an object
     * @param mixed $value
     */
    public function setChildValues($value): void
    {
        $properties = get_object_vars($value);
        foreach (array_keys($properties) as $property) {
            $this->children[$property] = &(new JPSpan_Serializer())->reflect($value->$property);
        }
    }

    /**
     * @param mixed $code
     */
    public function generate(&$code): void
    {
        $code->append('var ' . $this->tmpName . ' = new ' . $this->classname . '();');
        $this->generateChildren($code);
    }

    /**
     * Called from generate. Invokes generate on each child
     * of the object
     * @param mixed $code
     */
    public function generateChildren(&$code): void
    {
        foreach (array_keys($this->children) as $key) {
            $this->children[$key]->generate($code);
            $tmpName = $this->children[$key]->tmpName;
            if (preg_match('/^[0-9]+$/', $key)) {
                $code->append("{$this->tmpName}[$key] = $tmpName;");
            } else {
                $code->append("{$this->tmpName}.$key = $tmpName;");
            }
        }
    }
}

//-----------------------------------------------------------------------------

/**
 * Generates the representation of a JPSpan_Error object.
 * Note that you can only generate a single error and that it will
 * erase all other generated code (the first error in a data structure
 * will be that which be generated)
 */
class JPSpan_SerializedError
{
    /**
     * Name of Javascript Error class
     * @var string
     */
    public $name;
    /**
     * Error message
     * @var string
     */
    public $message;
    /**
     * Obey interface
     * @var string
     */
    public $tmpName = '';

    /**
     * @param mixed $error
     */
    public function setValue($error): void
    {
        $this->code    = $error->code;
        $this->name    = $error->name;
        $this->message = strip_tags($error->message);
        $this->message = str_replace("'", '', $this->message);
        $this->message = str_replace('"', '', $this->message);
    }

    /**
     * Conform to interface
     */
    public function setTmpVar(): void
    {
    }

    /**
     * Errors do no return - exception thrown
     * @ return string empty
     */
    public function getReturn()
    {
        return '';
    }

    /**
     * @param mixed $code
     */
    public function generate($code): void
    {
        $error = "var e = new Error('{$this->message}');";
        $error .= "e.name = '{$this->name}';";
        $error .= "e.code = '{$this->code}';";
        $error .= 'throw e;';
        // Wrap in anon function - violates RootElement
        $code->write('new Function("' . addcslashes($error, "\000\042\047\134") . '");');

        // Disable further code writing so only single Error returned
        $code->enabled = false;
    }
}
