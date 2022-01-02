<?php
//=======================================================================
// File:        JPGRAPH_ERRHANDLER.PHP
// Description: Error handler class together with handling of localized
//              error messages. All localized error messages are stored
//              in a separate file under the "lang/" subdirectory.
// Created:     2006-09-24
// Ver:         $Id: jpgraph_errhandler.inc.php 1920 2009-12-08 10:02:26Z ljp $
//
// Copyright 2006 (c) Aditus Consulting. All rights reserved.
//========================================================================

if (!defined('DEFAULT_ERR_LOCALE')) {
    define('DEFAULT_ERR_LOCALE', 'en');
}

if (!defined('USE_IMAGE_ERRORHandler')) {
    define('USE_IMAGE_ERRORHandler', true);
}

global $__jpg_err_locale;
$__jpg_err_locale = DEFAULT_ERR_LOCALE;

class ErrMsgText
{
    private $lt = null;

    public function __construct()
    {
        global $__jpg_err_locale;
        $file = 'lang/' . $__jpg_err_locale . '.inc.php';

        // If the chosen locale doesn't exist try english
        if (!file_exists(__DIR__ . '/' . $file)) {
            $__jpg_err_locale = 'en';
        }

        $file = 'lang/' . $__jpg_err_locale . '.inc.php';
        if (!file_exists(__DIR__ . '/' . $file)) {
            exit('Chosen locale file ("' . $file . '") for error messages does not exist or is not readable for the PHP process. Please make sure that the file exists and that the file permissions are such that the PHP process is allowed to read this file.');
        }
        require $file;
        $this->lt = $_jpg_messages;
    }

    public function get($errnbr, $a1 = null, $a2 = null, $a3 = null, $a4 = null, $a5 = null)
    {
        global $__jpg_err_locale;
        if (!isset($this->lt[$errnbr])) {
            return 'Internal error: The specified error message (' . $errnbr . ') does not exist in the chosen locale (' . $__jpg_err_locale . ')';
        }
        $ea = $this->lt[$errnbr];
        $j  = 0;
        if (null !== $a1) {
            $argv[$j++] = $a1;
            if (null !== $a2) {
                $argv[$j++] = $a2;
                if (null !== $a3) {
                    $argv[$j++] = $a3;
                    if (null !== $a4) {
                        $argv[$j++] = $a4;
                        if (null !== $a5) {
                            $argv[$j++] = $a5;
                        }
                    }
                }
            }
        }
        $numargs = $j;
        if ($ea[1] != $numargs) {
            // Error message argument count do not match.
            // Just return the error message without arguments.
            return $ea[0];
        }
        switch ($numargs) {
            case 1:
                $msg = sprintf($ea[0], $argv[0]);
                break;
            case 2:
                $msg = sprintf($ea[0], $argv[0], $argv[1]);
                break;
            case 3:
                $msg = sprintf($ea[0], $argv[0], $argv[1], $argv[2]);
                break;
            case 4:
                $msg = sprintf($ea[0], $argv[0], $argv[1], $argv[2], $argv[3]);
                break;
            case 5:
                $msg = sprintf($ea[0], $argv[0], $argv[1], $argv[2], $argv[3], $argv[4]);
                break;
            case 0:
            default:
                $msg = $ea[0];
                break;
        }

        return $msg;
    }
}

//
// A wrapper class that is used to access the specified error object
// (to hide the global error parameter and avoid having a GLOBAL directive
// in all methods.
//
class JpGraphError
{
    private static $__iImgFlg  = true;
    private static $__iLogFile = '';
    private static $__iTitle   = 'JpGraph Error: ';

    public static function raise($aMsg, $aHalt = true): void
    {
        throw new JpGraphException($aMsg);
    }

    public static function setErrLocale($aLoc): void
    {
        global $__jpg_err_locale;
        $__jpg_err_locale = $aLoc;
    }

    public static function raiseL($errnbr, $a1 = null, $a2 = null, $a3 = null, $a4 = null, $a5 = null): void
    {
        throw new JpGraphExceptionL($errnbr, $a1, $a2, $a3, $a4, $a5);
    }

    public static function setImageFlag($aFlg = true): void
    {
        self::$__iImgFlg = $aFlg;
    }

    public static function getImageFlag()
    {
        return self::$__iImgFlg;
    }

    public static function setLogFile($aFile): void
    {
        self::$__iLogFile = $aFile;
    }

    public static function getLogFile()
    {
        return self::$__iLogFile;
    }

    public static function setTitle($aTitle): void
    {
        self::$__iTitle = $aTitle;
    }

    public static function getTitle()
    {
        return self::$__iTitle;
    }
}

class JpGraphException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0)
    {
        // make sure everything is assigned properly
        parent::__construct($message, $code);
    }

    // custom string representation of object
    public function _toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message} at " . basename($this->getFile()) . ':' . $this->getLine() . "\n" . $this->getTraceAsString() . "\n";
    }

    // custom representation of error as an image
    public function stroke(): void
    {
        if (JpGraphError::getImageFlag()) {
            $errobj = new JpGraphErrObjectImg();
            $errobj->setTitle(JpGraphError::getTitle());
        } else {
            $errobj = new JpGraphErrObject();
            $errobj->setTitle(JpGraphError::getTitle());
            $errobj->setStrokeDest(JpGraphError::getLogFile());
        }
        $errobj->Raise($this->getMessage());
    }

    public static function defaultHandler(\Throwable $exception): void
    {
        global $__jpg_OldHandler;
        if ($exception instanceof self) {
            $exception->stroke();
        } else {
            // Restore old handler
            if (null !== $__jpg_OldHandler) {
                set_exception_handler($__jpg_OldHandler);
            }
            throw $exception;
        }
    }
}

class JpGraphExceptionL extends JpGraphException
{
    // Redefine the exception so message isn't optional
    public function __construct($errcode, $a1 = null, $a2 = null, $a3 = null, $a4 = null, $a5 = null)
    {
        // make sure everything is assigned properly
        $errtxt = new ErrMsgText();
        JpGraphError::setTitle('JpGraph Error: ' . $errcode);
        parent::__construct($errtxt->get($errcode, $a1, $a2, $a3, $a4, $a5), 0);
    }
}

// Setup the default handler
global $__jpg_OldHandler;
$__jpg_OldHandler = set_exception_handler(['JpGraphException', 'defaultHandler']);

//
// First of all set up a default error handler
//

//=============================================================
// The default trivial text error handler.
//=============================================================
class JpGraphErrObject
{
    protected $iTitle = 'JpGraph error: ';
    protected $iDest  = false;

    public function __construct()
    {
        // Empty. Reserved for future use
    }

    public function setTitle($aTitle): void
    {
        $this->iTitle = $aTitle;
    }

    public function setStrokeDest($aDest): void
    {
        $this->iDest = $aDest;
    }

    // If aHalt is true then execution can't continue. Typical used for fatal errors
    public function raise($aMsg, $aHalt = false): void
    {
        if ('' != $this->iDest) {
            if ('syslog' == $this->iDest) {
                error_log($this->iTitle . $aMsg);
            } else {
                $str = '[' . date('r') . '] ' . $this->iTitle . $aMsg . "\n";
                $f   = @fopen($this->iDest, 'ab');
                if ($f) {
                    @fwrite($f, $str);
                    @fclose($f);
                }
            }
        } else {
            $aMsg = $this->iTitle . $aMsg;
            // Check SAPI and if we are called from the command line
            // send the error to STDERR instead
            if (PHP_SAPI == 'cli') {
                fwrite(STDERR, $aMsg);
            } else {
                echo $aMsg;
            }
        }
        if ($aHalt) {
            exit(1);
        }
    }
}

//==============================================================
// An image based error handler
//==============================================================
class JpGraphErrObjectImg extends JpGraphErrObject
{
    public function __construct()
    {
        parent::__construct();
        // Empty. Reserved for future use
    }

    public function raise($aMsg, $aHalt = true): void
    {
        $img_iconerror = 'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAMAAAC7IEhfAAAAaV'
                         . 'BMVEX//////2Xy8mLl5V/Z2VvMzFi/v1WyslKlpU+ZmUyMjEh/'
                         . 'f0VyckJlZT9YWDxMTDjAwMDy8sLl5bnY2K/MzKW/v5yyspKlpY'
                         . 'iYmH+MjHY/PzV/f2xycmJlZVlZWU9MTEXY2Ms/PzwyMjLFTjea'
                         . 'AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACx'
                         . 'IAAAsSAdLdfvwAAAAHdElNRQfTBgISOCqusfs5AAABLUlEQVR4'
                         . '2tWV3XKCMBBGWfkranCIVClKLd/7P2Q3QsgCxjDTq+6FE2cPH+'
                         . 'xJ0Ogn2lQbsT+Wrs+buAZAV4W5T6Bs0YXBBwpKgEuIu+JERAX6'
                         . 'wM2rHjmDdEITmsQEEmWADgZm6rAjhXsoMGY9B/NZBwJzBvn+e3'
                         . 'wHntCAJdGu9SviwIwoZVDxPB9+Rc0TSEbQr0j3SA1gwdSn6Db0'
                         . '6Tm1KfV6yzWGQO7zdpvyKLKBDmRFjzeB3LYgK7r6A/noDAfjtS'
                         . 'IXaIzbJSv6WgUebTMV4EoRB8a2mQiQjgtF91HdKDKZ1gtFtQjk'
                         . 'YcWaR5OKOhkYt+ZsTFdJRfPAApOpQYJTNHvCRSJR6SJngQadfc'
                         . 'vd69OLMddVOPCGVnmrFD8bVYd3JXfxXPtLR/+mtv59/ALWiiMx'
                         . 'qL72fwAAAABJRU5ErkJggg==';

        if (function_exists('imagetypes')) {
            $supported = imagetypes();
        } else {
            $supported = 0;
        }

        if (!function_exists('imagecreatefromstring')) {
            $supported = 0;
        }

        if (ob_get_length() || headers_sent() || !($supported & IMG_PNG)) {
            // Special case for headers already sent or that the installation doesn't support
            // the PNG format (which the error icon is encoded in).
            // Dont return an image since it can't be displayed
            exit($this->iTitle . ' ' . $aMsg);
        }

        $aMsg  = wordwrap($aMsg, 55);
        $lines = mb_substr_count($aMsg, "\n");

        // Create the error icon GD
        $erricon = Image::CreateFromString(base64_decode($img_iconerror, true));

        // Create an image that contains the error text.
        $w = 400;
        $h = 100 + 15 * max(0, $lines - 3);

        $img = new Image($w, $h);

        // Drop shadow
        $img->SetColor('gray');
        $img->FilledRectangle(5, 5, $w - 1, $h - 1, 10);
        $img->SetColor('gray:0.7');
        $img->FilledRectangle(5, 5, $w - 3, $h - 3, 10);

        // Window background
        $img->SetColor('lightblue');
        $img->FilledRectangle(1, 1, $w - 5, $h - 5);
        $img->CopyCanvasH($img->img, $erricon, 5, 30, 0, 0, 40, 40);

        // Window border
        $img->SetColor('black');
        $img->Rectangle(1, 1, $w - 5, $h - 5);
        $img->Rectangle(0, 0, $w - 4, $h - 4);

        // Window top row
        $img->SetColor('darkred');
        for ($y = 3; $y < 18; $y += 2) {
            $img->Line(1, $y, $w - 6, $y);
        }

        // "White shadow"
        $img->SetColor('white');

        // Left window edge
        $img->Line(2, 2, 2, $h - 5);
        $img->Line(2, 2, $w - 6, 2);

        // "Gray button shadow"
        $img->SetColor('darkgray');

        // Gray window shadow
        $img->Line(2, $h - 6, $w - 5, $h - 6);
        $img->Line(3, $h - 7, $w - 5, $h - 7);

        // Window title
        $m = floor($w / 2 - 5);
        $l = 110;
        $img->SetColor('lightgray:1.3');
        $img->FilledRectangle($m - $l, 2, $m + $l, 16);

        // Stroke text
        $img->SetColor('darkred');
        $img->SetFont(FF_FONT2, FS_BOLD);
        $img->StrokeText($m - 90, 15, $this->iTitle);
        $img->SetColor('black');
        $img->SetFont(FF_FONT1, FS_NORMAL);
        $txt = new Text($aMsg, 52, 25);
        $txt->SetFont(FF_FONT1);
        $txt->Align('left', 'top');
        $txt->Stroke($img);
        if ($this->iDest) {
            $img->Stream($this->iDest);
        } else {
            $img->Headers();
            $img->Stream();
        }
        if ($aHalt) {
            exit();
        }
    }
}

if (!USE_IMAGE_ERRORHandler) {
    JpGraphError::setImageFlag(false);
}
