<?php namespace Xoopsmodules\xhelp\validation;

use Xoopsmodules\xhelp;
use Xoopsmodules\xhelp\validation;


/**
 * Class ValidateMimeType
 */
class ValidateMimeType extends validation\Validator
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
