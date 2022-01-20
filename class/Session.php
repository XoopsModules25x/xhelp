<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

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
        if (!@\session_start()) {
            throw new \RuntimeException('Session could not start.');
        }
    }

    /**
     * Sets a session variable
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value): void
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Fetches a session variable
     * @param string $name
     * @return string|array|bool value of session variable
     */
    public function get(string $name)
    {
        return $_SESSION[$name] ?? false;
    }

    /**
     * Deletes a session variable
     * @param string $name
     */
    public function del($name): void
    {
        unset($_SESSION[$name]);
    }

    /**
     * Destroys the whole session
     */
    public function destroy(): void
    {
        $_SESSION = [];
        \session_destroy();
    }

    /**
     * @return static
     */
    public static function getInstance(): Session
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }
}
