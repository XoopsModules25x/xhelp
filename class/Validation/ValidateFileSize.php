<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Validation;

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
        parent::__construct();
    }

    public function validate()
    {
        if ($this->maxsize < \filesize($this->file)) {
            $this->setError('File is too large');
        }
    }
}
