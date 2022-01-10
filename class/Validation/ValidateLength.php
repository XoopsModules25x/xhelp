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
 * @license      GNU GPL 2 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       XOOPS Development Team
 * @author       Nazar Aziz (www.panthersoftware.com)
 */

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
     * Constructs a new ValidateLength object subclass or Validator
     * @param string $text
     * @param int    $min_length min string size
     * @param int    $max_length the max string size
     */
    public function __construct(string $text, int $min_length, int $max_length = 0)
    {
        $this->text       = $text;
        $this->min_length = $min_length;
        $this->max_length = $max_length;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates a string
     */
    public function validate()
    {
        if (mb_strlen($this->text) < $this->min_length) {
            $this->setError(\_XHELP_MESSAGE_SHORT);
        }
        if ($this->max_length) {
            if (mb_strlen($this->text) > $this->max_length) {
                $this->setError(\_XHELP_MESSAGE_LONG);
            }
        }
    }
}
