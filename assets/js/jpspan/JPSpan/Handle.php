<?php declare(strict_types=1);
/**
 * Swiped from WACT: https://wact.sourceforge.net (handle.inc.php)
 * @see        https://wact.sourceforge.net/index.php/ResolveHandle
 */

//-----------------------------------------------------------------------------

/**
 * Contains static methods for resolving and reflecting on handles
 * @see        https://wact.sourceforge.net/index.php/Handle
 */
class JPSpan_Handle
{
    /**
     * Takes a "handle" to an object and modifies it to convert it to an instance
     * of the class. Allows for "lazy loading" of objects on demand.
     * @see    https://wact.sourceforge.net/index.php/ResolveHandle
     * @todo   Cases where Handle not array, string or object?
     * @param mixed $Handle
     * @return bool FALSE if handle not resolved
     * @static
     */
    public function resolve(&$Handle): bool
    {
        switch (gettype($Handle)) {
            case 'array':
                $Class            = array_shift($Handle);
                $ConstructionArgs = $Handle;
                break;
            case 'string':
                $ConstructionArgs = [];
                $Class            = $Handle;
                break;
            case 'object':
                return true;
                break;
            default:
                return false;
                break;
        }

        if (is_int($Pos = mb_strpos($Class, '|'))) {
            $File  = mb_substr($Class, 0, $Pos);
            $Class = mb_substr($Class, $Pos + 1);
            require_once $File;
        }

        switch (count($ConstructionArgs)) {
            case 0:
                $Handle = new $Class();
                break;
            case 1:
                $Handle = new $Class(array_shift($ConstructionArgs));
                break;
            case 2:
                $Handle = new $Class(array_shift($ConstructionArgs), array_shift($ConstructionArgs));
                break;
            case 3:
                $Handle = new $Class(array_shift($ConstructionArgs), array_shift($ConstructionArgs), array_shift($ConstructionArgs));
                break;
            default:
                trigger_error('Maximum constructor arg count exceeded', E_USER_ERROR);

                return false;
                break;
        }

        return true;
    }

    /**
     * Determines the "public" class methods exposed by a handle
     * Class constructors and methods beginning with an underscore
     * are ignored.
     * @see    https://wact.sourceforge.net/index.php/ResolveHandle
     * @todo   Cases where Handle not array, string or object?
     * @param mixed $Handle
     * @return false|\JPSpan_HandleDescription JPSpan_HandleDescription or FALSE if invalid handle
     * @static
     */
    public function examine($Handle)
    {
        switch (gettype($Handle)) {
            case 'array':
                $Class = array_shift($Handle);
                break;
            case 'string':
                $Class = $Handle;
                break;
            case 'object':
                $Class = get_class($Handle);
                break;
            default:
                return false;
                break;
        }

        if (is_int($Pos = mb_strpos($Class, '|'))) {
            $File  = mb_substr($Class, 0, $Pos);
            $Class = mb_substr($Class, $Pos + 1);
            require_once $File;
        }

        $Class = \mb_strtolower($Class);

        $Description        = new JPSpan_HandleDescription();
        $Description->Class = $Class;

        $methods = get_class_methods($Class);
        if (null === $methods) {
            return false;
        }
        $methods = array_map('\strtolower', $methods);

        if (false !== ($constructor = array_search($Class, $methods, true))) {
            unset($methods[$constructor]);
        }

        foreach ($methods as $method) {
            if (1 == preg_match('/^[a-z]+[0-9a-z_]*$/', $method)) {
                $Description->methods[] = $method;
            }
        }

        return $Description;
    }
}

//-----------------------------------------------------------------------------

/**
 * Describes a handle: used to help generate Javascript clients
 * and validate incoming calls
 */
class JPSpan_HandleDescription
{
    /**
     * @var string class name for handle
     */
    public $Class = '';
    /**
     * @var array methods exposed by handle
     */
    public $methods = [];
}
