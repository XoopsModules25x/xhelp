<?php namespace Xoopsmodules\xhelp\validation;

use Xoopsmodules\xhelp;
use Xoopsmodules\xhelp\validation;


/**
 * Class ValidateImageSize
 */
class ValidateImageSize extends validation\Validator
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
