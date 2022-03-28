<?php declare(strict_types=1);
/**
 * @param mixed $className
 * @param mixed $getFailed
 */

//--------------------------------------------------------------------------------
/**
 * Unserialize call back function - checks that classes exist in the JPSpan map,
 * and includes them where needed. Throws an E_USER_ERROR if not found and dies
 * @param string $className classname (passed by PHP)
 * @param bool   $getFailed set to TRUE to get back the name of the last failed class
 * @return void|null void unless getFailed param is true
 */
function JPSpan_Unserializer_PHP_Callback($className, $getFailed = false)
{
    static $failedClass = null;
    if ($getFailed) {
        return $failedClass;
    } else {
        $className = \mb_strtolower($className);
        if (array_key_exists($className, $GLOBALS['_JPSPAN_UNSERIALIZER_MAP'])) {
            if (null !== $GLOBALS['_JPSPAN_UNSERIALIZER_MAP'][$className]) {
                require_once $GLOBALS['_JPSPAN_UNSERIALIZER_MAP'][$className];
            }
        } else {
            $failedClass = \mb_strtolower($className);
        }
    }
}

//---------------------------------------------------------------------------

/**
 * Unserializes PHP serialized strings
 */
class JPSpan_Unserializer_PHP
{
    /**
     * Unserialize a string into PHP data types. Changes the unserialize callback
     * function temporarily to JPSpan_Unserializer_PHP_Callback
     * @param mixed $data
     * @return mixed PHP data
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
        if (false !== $result || 'b:0;' === $data) {
            return $result;
        }

        return $data;
    }

    /**
     * Validates unserialized data, checking the class names of serialized objects,
     * to prevent unexpected objects from being instantiated by PHP's unserialize()
     * @param mixed $data
     * @return bool TRUE if valid
     */
    public function validateClasses($data): bool
    {
        foreach ($this->getClasses($data) as $class) {
            if (!array_key_exists(mb_strtolower($class), $GLOBALS['_JPSPAN_UNSERIALIZER_MAP'])) {
                trigger_error('Illegal type: ' . \mb_strtolower($class), E_USER_ERROR);

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
     */
    public function getClasses($string): array
    {
        // Stip any string representations (which might contain object syntax)
        $string = preg_replace('/s:[0-9]+:".*"/Us', '', $string);

        // Pull out the class named
        preg_match_all('/O:[0-9]+:"(.*)"/U', $string, $matches, PREG_PATTERN_ORDER);

        // Make sure names are unique (same object serialized twice)
        return array_unique($matches[1]);
    }
}
