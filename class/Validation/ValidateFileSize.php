<?php namespace XoopsModules\Xhelp\Validation;

use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;


/**
 * Class ValidateFileSize
 */
class ValidateFileSize extends validation\Validator
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
