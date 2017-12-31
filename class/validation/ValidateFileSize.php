<?php namespace Xoopsmodules\xhelp\validation;

use Xoopsmodules\xhelp;
use Xoopsmodules\xhelp\validation;


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
