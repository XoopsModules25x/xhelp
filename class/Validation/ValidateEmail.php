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
     * Constructs a new ValidateEmail object subclass or Validator
     * @param string $email the string to validate
     */
    public function __construct(string $email)
    {
        $this->email = $email;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates an email address
     */
    public function validate()
    {
        $pattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i';
        //$pattern= "/^([a-zA-Z0-9])+([.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-]+)+/";
        if (!\preg_match($pattern, $this->email)) {
            $this->setError(\_XHELP_MESSAGE_INVALID);
        }
        if (mb_strlen($this->email) > 100) {
            $this->setError(\_XHELP_MESSAGE_LONG);
        }
        if (mb_strlen($this->email) < 5) {
            $this->setError(\_XHELP_MESSAGE_SHORT);
        }
    }
}
