<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Validation;

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
        parent::__construct();
    }

    public function validate()
    {
        [$width, $height] = \getimagesize($this->file);
        if ($this->maxwidth < $width) {
            $this->setError('Image Width is too large');
        }
        if ($this->maxheight < $height) {
            $this->setError('Image Height is too large');
        }
    }
}
