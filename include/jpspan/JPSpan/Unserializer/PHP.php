<?php
/**
 * @package    JPSpan
 * @subpackage Unserialzier
 */

//--------------------------------------------------------------------------------
/**
 * Unserialize call back function - checks that classes exist in the JPSpan map,
 * and includes them where needed. Throws an E_USER_ERROR if not found and dies
 * @param string  classname (passed by PHP)
 * @param boolean set to TRUE to get back the name of the last failed class
 * @return mixed void unless getFailed param is true
 * @access     private
 * @package    JPSpan
 * @subpackage Unserialzier
 */
function JPSpan_Unserializer_PHP_Callback($className, $getFailed = false)
{
    static $failedClass = null;
    if (!$getFailed) {
        $className = strtolower($className);
        if (array_key_exists($className, $GLOBALS['_JPSPAN_UNSERIALIZER_MAP'])) {
            if (null !== $GLOBALS['_JPSPAN_UNSERIALIZER_MAP'][$className]) {
                require_once $GLOBALS['_JPSPAN_UNSERIALIZER_MAP'][$className];
            }
        } else {
            $failedClass = strtolower($className);
        }
    } else {
        return $failedClass;
    }
}

//---------------------------------------------------------------------------

/**
 * Unserializes PHP serialized strings
 * @package    JPSpan
 * @subpackage Unserialzier
 * @access     public
 */
class JPSpan_Unserializer_PHP
{
    /**
     * Unserialize a string into PHP data types. Changes the unserialize callback
     * function temporarily to JPSpan_Unserializer_PHP_Callback
     * @param string data serialized with PHP's serialization protocol
     * @return mixed PHP data
     * @access public
     */
    public function unserialize($data)
    {
        if (is_string($data)) {
            if (!$this->validateClasses($data)) {
                return false;
            }
        } else {
            // It's not a string - give it back
            return $data;
        }

        $old_cb = ini_get('unserialize_callback_func');
        ini_set('unserialize_callback_func', 'JPSpan_Unserializer_PHP_Callback');

        $result = @unserialize(trim($data));

        ini_set('unserialize_callback_func', $old_cb);

        // Check for a serialized FALSE value
        if ($result !== false || $data == 'b:0;') {
            return $result;
        }

        return $data;
    }

    /**
     * Validates unserialized data, checking the class names of serialized objects,
     * to prevent unexpected objects from being instantiated by PHP's unserialize()
     * @param mixed data to validate
     * @return boolean TRUE if valid
     * @access private
     */
    public function validateClasses($data)
    {
        foreach ($this->getClasses($data) as $class) {
            if (!array_key_exists(strtolower($class), $GLOBALS['_JPSPAN_UNSERIALIZER_MAP'])) {
                trigger_error('Illegal type: ' . strtolower($class), E_USER_ERROR);

                return false;
            }
        }

        return true;
    }

    /**
     * Parses the serialized string, extracting class names
     * @param serialized $string
     * @return array list of classes found
     * @internal param serialized $string string to parse
     * @access   private
     */
    public function getClasses($string)
    {
        // Stip any string representations (which might contain object syntax)
        $string = preg_replace('/s:[0-9]+:".*"/Us', '', $string);

        // Pull out the class named
        preg_match_all('/O:[0-9]+:"(.*)"/U', $string, $matches, PREG_PATTERN_ORDER);

        // Make sure names are unique (same object serialized twice)
        return array_unique($matches[1]);
    }
}
