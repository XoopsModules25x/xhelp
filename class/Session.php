<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

use RuntimeException;

/**
 * A wrapper around PHP's session functions
 * @author  Harry Fuecks (PHP Anthology Volume II)
 */
class Session
{
    /**
     * Session constructor<br>
     * Starts the session with session_start()
     * <b>Note:</b> that if the session has already started,
     * session_start() does nothing
     */
    public function __construct()
    {
        if (false === @\session_start()) {
            throw new RuntimeException('Session could not start.');
        }
    }

    /**
     * Sets a session variable
     * @param mixed $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Fetches a session variable
     * @param mixed $name
     * @return mixed value of session variable
     */
    public function get($name)
    {
        return $_SESSION[$name] ?? false;
    }

    /**
     * Deletes a session variable
     * @param mixed $name
     */
    public function del($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * Destroys the whole session
     */
    public function destroy()
    {
        $_SESSION = [];
        \session_destroy();
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }
}
