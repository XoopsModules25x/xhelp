<?php
//

/**
 *  Validator superclass for form validation
 */
class Validator
{
    /**
     * Private
     * $errorMsg stores error messages if not valid
     */
    public $errorMsg;

    //! A constructor.

    /**
     * Constucts a new Validator object
     */
    public function __construct()
    {
        $this->errorMsg = [];
        $this->validate();
    }

    //! A manipulator

    /**
     * @return void
     */
    public function validate()
    {
        // Superclass method does nothing
    }

    //! A manipulator

    /**
     * Adds an error message to the array
     * @param $msg
     * @return void
     */
    public function setError($msg)
    {
        $this->errorMsg[] = $msg;
    }

    //! An accessor

    /**
     * Returns true is string valid, false if not
     * @return boolean
     */
    public function isValid()
    {
        if (count($this->errorMsg)) {
            return false;
        } else {
            return true;
        }
    }

    //! An accessor

    /**
     * Pops the last error message off the array
     * @return string
     */
    public function getError()
    {
        return array_pop($this->errorMsg);
    }

    /**
     * @return array
     */
    public function &getErrors()
    {
        return $this->errorMsg;
    }
}

/**
 *  ValidatorEmail subclass of Validator
 *  Validates an email address
 */
class ValidateEmail extends Validator
{
    /**
     * Private
     * $email the email address to validate
     */
    public $email;

    //! A constructor.

    /**
     * Constucts a new ValidateEmail object subclass or Validator
     * @param $email the string to validate
     */
    public function __construct($email)
    {
        $this->email = $email;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates an email address
     * @return void
     */
    public function validate()
    {
        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i";
        //$pattern= "/^([a-zA-Z0-9])+([.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-]+)+/";
        if (!preg_match($pattern, $this->email)) {
            $this->setError(_XHELP_MESSAGE_INVALID);
        }
        if (strlen($this->email) > 100) {
            $this->setError(_XHELP_MESSAGE_LONG);
        }
        if (strlen($this->email) < 5) {
            $this->setError(_XHELP_MESSAGE_SHORT);
        }
    }
}

/**
 * Class ValidatePhone
 */
class ValidatePhone extends Validator
{
    /**
     * Private
     * $phone the phone number to validate
     */
    public $phone;

    //! A constructor.

    /**
     * Constucts a new ValidatePhone object subclass or Validator
     * @param $phone the string to validate
     */
    public function __construct($phone)
    {
        $this->phone = $phone;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates a phone number
     * @return void
     */
    public function validate()
    {
        $pattern = "(\d{3})\D*(\d{3})\D*(\d{4})\D*(\d*)$";
        if (!preg_match($pattern, $this->phone)) {
            $this->setError(_XHELP_MESSAGE_INVALID);
        }
    }
}

/**
 * Class ValidateTimestamp
 */
class ValidateTimestamp extends Validator
{
    /**
     * Private
     * $timestamp the date/time to validate
     */
    public $timestamp;

    //! A constructor.

    /**
     * Constucts a new ValidateTimestamp object subclass or Validator
     * @param $timestamp the string to validate
     */
    public function __construct($timestamp)
    {
        $this->timestamp = $timestamp;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates a timestamp
     * @return void
     */
    public function validate()
    {
    }
}

/**
 * Class ValidateLength
 */
class ValidateLength extends Validator
{
    /**
     * Private
     * $text the string to validate
     */
    public $text;

    /**
     * Private
     * $min_length the minimum length of string to validate
     */
    public $min_length;

    /**
     * Private
     * $max_length the max length of string to validate
     */
    public $max_length;

    //! A constructor.

    /**
     * Constucts a new ValidateLength object subclass or Validator
     * @param         $text
     * @param         $min_length the min string size
     * @param int|the $max_length the max string size
     */
    public function __construct($text, $min_length, $max_length = 0)
    {
        $this->text       = $text;
        $this->min_length = $min_length;
        $this->max_length = $max_length;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates a string
     * @return void
     */
    public function validate()
    {
        if (strlen($this->text) < $this->min_length) {
            $this->setError(_XHELP_MESSAGE_SHORT);
        }
        if ($this->max_length) {
            if (strlen($this->text) > $this->max_length) {
                $this->setError(_XHELP_MESSAGE_LONG);
            }
        }
    }
}

/**
 * Class ValidateNumber
 */
class ValidateNumber extends Validator
{
    /**
     * Private
     * $text the string to validate
     */
    public $text;

    public $forceeentry;

    //! A constructor.

    /**
     * Constucts a new ValidateNumber object subclass or Validator
     * @param      $text
     * @param bool $forceentry
     */
    public function __construct($text, $forceentry = false)
    {
        $this->text       = $text;
        $this->forceentry = $forceentry;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates a number
     * @return void
     */
    public function validate()
    {
        if (!is_numeric($this->text) && (strlen($this->text) > 0 && !$this->forceentry)) {
            $this->setError(_XHELP_MESSAGE_NOT_NUMERIC);
        }
    }
}

/**
 *  ValidatorUname subclass of Validator
 *  Validates a username
 */
class ValidateUname extends Validator
{
    /**
     * Private
     * $uname the username to validate
     */
    public $uname;

    //! A constructor.

    /**
     * Constucts a new ValidateUname object subclass or Validator
     * @param $uname the string to validate
     */
    public function __construct($uname)
    {
        $this->uname = $uname;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates an email address
     * @return void
     */
    public function validate()
    {
        $hConfig = xoops_getHandler('config');
        //$xoopsConfigUser = $hConfig->getConfigsByCat(XOOPS_CONF_USER);
        $xoopsConfigUser = [];
        $crit            = new Criteria('conf_catid', 2);
        $myConfigs       = $hConfig->getConfigs($crit);
        foreach ($myConfigs as $myConf) {
            $xoopsConfigUser[$myConf->getVar('conf_name')] = $myConf->getVar('conf_value');
        }
        $xoopsDB = XoopsDatabaseFactory::getDatabaseConnection();

        switch ($xoopsConfigUser['uname_test_level']) {
            case 0:
                // strict
                $restriction = '/[^a-zA-Z0-9\_\-]/';
                break;
            case 1:
                // medium
                $restriction = '/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"]/';
                break;
            case 2:
                // loose
                $restriction = '/[\000-\040]/';
                break;
        }

        if (empty($this->uname) || preg_match($restriction, $this->uname)) {
            $this->setError(_XHELP_MESSAGE_INVALID);
        }
        if (strlen($this->uname) > $xoopsConfigUser['maxuname']) {
            $this->setError(sprintf(_XHELP_MESSAGE_LONG, $xoopsConfigUser['maxuname']));
        }
        if (strlen($this->uname) < $xoopsConfigUser['minuname']) {
            $this->setError(sprintf(_XHELP_MESSAGE_SHORT, $xoopsConfigUser['minuname']));
        }
        foreach ($xoopsConfigUser['bad_unames'] as $bu) {
            if (!empty($bu) && preg_match('/' . $bu . '/i', $this->uname)) {
                $this->setError(_XHELP_MESSAGE_RESERVED);
                break;
            }
        }
        if (strrpos($this->uname, ' ') > 0) {
            $this->setError(_XHELP_MESSAGE_NO_SPACES);
        }
        $sql    = 'SELECT COUNT(*) FROM ' . $xoopsDB->prefix('users') . " WHERE uname='" . addslashes($this->uname) . "'";
        $result = $xoopsDB->query($sql);
        list($count) = $xoopsDB->fetchRow($result);
        if ($count > 0) {
            $this->setError(_XHELP_MESSAGE_UNAME_TAKEN);
        }
    }
}

/**
 *  ValidatorPassword subclass of Validator
 *  Validates a password
 */
class ValidatePassword extends Validator
{
    /**
     * Private
     * $pass the password to validate
     */
    public $pass;

    /**
     * Private
     * $vpass the verification password to validate
     */
    public $vpass;

    //! A constructor.

    /**
     * Constucts a new ValidatePassword object subclass or Validator
     * @param $pass the string to validate
     * @param $vpass
     */
    public function __construct($pass, $vpass)
    {
        $this->pass  = $pass;
        $this->vpass = $vpass;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates a password
     * @return void
     */
    public function validate()
    {
        $hConfig = xoops_getHandler('config');
        //$xoopsConfigUser = $hConfig->getConfigsByCat(XOOPS_CONF_USER);
        $xoopsConfigUser = [];
        $crit            = new Criteria('conf_catid', 2);
        $myConfigs       = $hConfig->getConfigs($crit);
        foreach ($myConfigs as $myConf) {
            $xoopsConfigUser[$myConf->getVar('conf_name')] = $myConf->getVar('conf_value');
        }

        if (!isset($this->pass) || $this->pass == '' || !isset($this->vpass) || $this->vpass == '') {
            $this->setError(_XHELP_MESSAGE_NOT_SUPPLIED);
            //$stop .= _US_ENTERPWD.'<br>';
        }
        if (isset($this->pass) && ($this->pass != $this->vpass)) {
            $this->setError(_XHELP_MESSAGE_NOT_SAME);
            //$stop .= _US_PASSNOTSAME.'<br>';
        } elseif (($this->pass != '') && (strlen($this->pass) < $xoopsConfigUser['minpass'])) {
            $this->setError(sprintf(_XHELP_MESSAGE_SHORT, $xoopsConfigUser['minpass']));
            //$stop .= sprintf(_US_PWDTOOSHORT,$xoopsConfigUser['minpass'])."<br>";
        }
    }
}

/**
 * Class ValidateMimeType
 */
class ValidateMimeType extends Validator
{
    public $file;
    public $mimetype;
    public $allowed_mimetypes;

    /**
     * ValidateMimeType constructor.
     * @param $file
     * @param $mimetype
     * @param $allowed_mimetypes
     */
    public function __construct($file, $mimetype, $allowed_mimetypes)
    {
        $this->file              = $file;
        $this->mimetype          = strtolower($mimetype);
        $this->allowed_mimetypes = $allowed_mimetypes;
        Validator::Validator();
    }

    public function validate()
    {
        $allowed_mimetypes = false;
        //Check MimeType
        if (is_array($this->allowed_mimetypes)) {
            $farray     = explode('.', $this->file);
            $fextension = strtolower($farray[count($farray) - 1]);
            foreach ($this->allowed_mimetypes as $mime) {
                $lower_type = strtolower($mime['type']);
                $lower_ext  = strtolower($mime['ext']);
                if ($lower_type == $this->mimetype && $lower_ext == $fextension) {
                    $allowed_mimetypes = $mime['type'];
                    break;
                }
            }
        }

        if (!$allowed_mimetypes) {
            $this->setError('Invalid MimeType');
        }
    }
}

/**
 * Class ValidateFileSize
 */
class ValidateFileSize extends Validator
{
    public $file;
    public $maxsize;

    /**
     * ValidateFileSize constructor.
     * @param $file
     * @param $maxsize
     */
    public function __construct($file, $maxsize)
    {
        $this->file    = $file;
        $this->maxsize = $maxsize;
        Validator::Validator();
    }

    public function validate()
    {
        if ($this->maxsize < filesize($this->file)) {
            $this->setError('File is too large');
        }
    }
}

/**
 * Class ValidateImageSize
 */
class ValidateImageSize extends Validator
{
    public $file;
    public $maxwidth;
    public $maxheight;

    /**
     * ValidateImageSize constructor.
     * @param $file
     * @param $maxwidth
     * @param $maxheight
     */
    public function __construct($file, $maxwidth, $maxheight)
    {
        $this->file      = $file;
        $this->maxwidth  = $maxwidth;
        $this->maxheight = $maxheight;
        Validator::Validator();
    }

    public function validate()
    {
        list($width, $height) = getimagesize($this->file);
        if ($this->maxwidth < $width) {
            $this->setError('Image Width is too large');
        }
        if ($this->maxheight < $height) {
            $this->setError('Image Height is too large');
        }
    }
}

/**
 *  ValidatorRegex subclass of Validator
 *  Validates an email address
 */
class ValidateRegEx extends Validator
{
    public $pattern;
    public $checkText;
    public $required;

    //! A constructor.

    /**
     * Constucts a new ValidateEmail object subclass or Validator
     * @param $checkText
     * @param $pattern
     * @param $required
     * @internal param the $email string to validate
     */
    public function __construct($checkText, $pattern, $required)
    {
        $this->pattern   = $pattern;
        $this->checkText = $checkText;
        $this->required  = $required;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates a regular expression
     * @return void
     */
    public function validate()
    {
        if ($this->required == 1) {                                // If value is required
            if (is_array($this->checkText) && isset($this->checkText['size'])) {     // If this is a file
                if ($this->checkText['name'] == '') {
                    $this->setError(_XHELP_MESSAGE_REQUIRED);           // Return message saying required value
                }
            } else {                                                    // If not a file
                if ($this->pattern != '') {                               // If regex pattern is not empty
                    if (!preg_match('/' . $this->pattern . '/', $this->checkText)) {  // Check regex against supplied text
                        $this->setError(_XHELP_MESSAGE_INVALID);        // Return message saying invalid value
                    }
                } else {
                    if (empty($this->checkText)) {    // If text is not supplied
                        $this->setError(_XHELP_MESSAGE_REQUIRED);           // Return message saying required value
                    }
                }
            }
        } else {
            if (empty($this->checkText)) {
                if ($this->pattern != '') {
                    if (!preg_match('/' . $this->pattern . '/', $this->checkText)) {
                        $this->setError(_XHELP_MESSAGE_INVALID);
                    }
                }
            }
        }
    }
}
