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
 */

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
     * Constructs a new ValidateTimestamp object subclass or Validator
     * @param int $timestamp the string to validate
     */
    public function __construct(int $timestamp)
    {
        $this->timestamp = $timestamp;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates a timestamp
     */
    public function validate()
    {
    }
}
