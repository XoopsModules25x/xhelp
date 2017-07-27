<?php
/**
 * @package    JPSpan
 * @subpackage RequestData
 */
//--------------------------------------------------------------------------------

/**
 * Controls stripping of magic_quotes_gpc. Set to false if you're already
 * taking care of them
 */
if (!defined('JPSPAN_LISTENER_STRIPQUOTES')) {
    define('JPSPAN_LISTENER_STRIPQUOTES', true);
}

/**
 * Include the unserializer
 */
require_once JPSPAN . 'Unserializer.php';
//--------------------------------------------------------------------------------

/**
 * Fetches data from HTTP_RAW_POST_DATA
 * @package    JPSpan
 * @subpackage RequestData
 * @public
 */
class JPSpan_RequestData_RawPost
{
    /**
     * Returns the data, making sure they are unserialized
     * @access public
     * @param $encoding
     * @return mixed
     * @static
     */
    public function fetch($encoding)
    {
        $http_raw_post_data = file_get_contents('php://input');

        return JPSpan_Unserializer::unserialize($http_raw_post_data, $encoding);
    }
}

//--------------------------------------------------------------------------------

/**
 * Fetches data from HTTP POSTs (Content-Type: application/x-www-form-urlencoded)
 * @package    JPSpan
 * @subpackage RequestData
 * @public
 */
class JPSpan_RequestData_Post
{
    /**
     * Returns the data, making sure they are unserialized and removing magic
     * quotes if enabled
     * @access public
     * @param $encoding
     * @return mixed
     * @static
     */
    public function fetch($encoding)
    {
        $return = [];
        if (JPSPAN_LISTENER_STRIPQUOTES) {
            $strip = get_magic_quotes_gpc();
        } else {
            $strip = false;
        }
        foreach ($_POST as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $key => $data) {
                    $value[$key] = $strip ? stripslashes($data) : $data;
                    $value[$key] = JPSpan_Unserializer::unserialize($value[$key], $encoding);
                }
            } else {
                $value = $strip ? stripslashes($value) : $value;
                $value = JPSpan_Unserializer::unserialize($value, $encoding);
            }
            $return[$name] = $value;
        }

        return $return;
    }
}

//--------------------------------------------------------------------------------

/**
 * Fetches data from HTTP GETs
 * @package    JPSpan
 * @subpackage RequestData
 * @public
 */
class JPSpan_RequestData_Get
{
    /**
     * Returns the data, making sure they are unserialized and removing magic
     * quotes if enabled
     * @access public
     * @param $encoding
     * @return mixed
     * @static
     */
    public function fetch($encoding)
    {
        $return = [];
        if (JPSPAN_LISTENER_STRIPQUOTES) {
            $strip = get_magic_quotes_gpc();
        } else {
            $strip = false;
        }
        foreach ($_GET as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $key => $data) {
                    $value[$key] = $strip ? stripslashes($data) : $data;
                    $value[$key] = JPSpan_Unserializer::unserialize($value[$key], $encoding);
                }
            } else {
                $value = $strip ? stripslashes($value) : $value;
                $value = JPSpan_Unserializer::unserialize($value, $encoding);
            }
            $return[$name] = $value;
        }

        return $return;
    }
}
