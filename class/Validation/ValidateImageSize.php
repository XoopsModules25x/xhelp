<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Validation;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    XOOPS Project (https://xoops.org)
 * @license      GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       XOOPS Development Team
 */

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
     * @param string $file
     * @param int    $maxwidth
     * @param int    $maxheight
     */
    public function __construct(string $file, int $maxwidth, int $maxheight)
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
