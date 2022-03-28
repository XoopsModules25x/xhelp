<?php declare(strict_types=1);

//--------------------------------------------------------------------------------

/**
 * Global array of known classes which may be unserialized.
 * Use JPSpan_Unserializer::register() to register new types
 * @see        JPSpan_Unserializer::register
 */
$GLOBALS['_JPSPAN_UNSERIALIZER_MAP'] = [];

/**
 * Include the Script Server types for mapping JS <> PHP
 */
require_once JPSPAN . 'Types.php';

/**
 * Register the allowed unserialization objects
 * Function defined in Types script
 * @todo Change the name of this function
 */
JPSpan_Register_Unserialization_Types();
//--------------------------------------------------------------------------------

/**
 * Handles unserialization on incoming request data
 */
class JPSpan_Unserializer
{
    /**
     * Unserialize a string into PHP data types.
     * @param mixed $data
     * @param mixed $encoding
     * @return mixed PHP data
     * @static
     */
    public function unserialize($data, $encoding = 'xml')
    {
        switch ($encoding) {
            case 'php':
                require_once JPSPAN . 'Unserializer/PHP.php';
                $U = new JPSpan_Unserializer_PHP();
                break;
            case 'xml':
            default:
                require_once JPSPAN . 'Unserializer/XML.php';
                $U = new JPSpan_Unserializer_XML();
                break;
        }

        return $U->unserialize($data);
    }

    /**
     * Register a known class for unserialization
     * Places a value in the global _JPSpan_UNSERIALIZER_MAP variable
     * @param mixed      $class
     * @param null|mixed $file
     * @static
     */
    public function addType($class, $file = null): void
    {
        $GLOBALS['_JPSPAN_UNSERIALIZER_MAP'][mb_strtolower($class)] = $file;
    }
}
