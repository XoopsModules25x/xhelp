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
 * @copyright    {@link https://xoops.org/ XOOPS Project}
 * @license      {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

/**
 *  ValidatorPassword subclass of Validator
 *  Validates a password
 */
class ValidatePassword extends Validator
{
    /**
     * Private
     * $pass the password to validate
     */
    public $pass;
    /**
     * Private
     * $vpass the verification password to validate
     */
    public $vpass;
    //! A constructor.

    /**
     * Constructs a new ValidatePassword object subclass or Validator
     * @param string $pass the string to validate
     * @param string $vpass
     */
    public function __construct(string $pass, string $vpass)
    {
        $this->pass  = $pass;
        $this->vpass = $vpass;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates a password
     */
    public function validate()
    {
        /** @var \XoopsConfigHandler $configHandler */
        $configHandler = \xoops_getHandler('config');
        //$xoopsConfigUser = $configHandler->getConfigsByCat(XOOPS_CONF_USER);
        $xoopsConfigUser = [];
        $criteria        = new \Criteria('conf_catid', 2);
        $myConfigs       = $configHandler->getConfigs($criteria);
        foreach ($myConfigs as $myConf) {
            $xoopsConfigUser[$myConf->getVar('conf_name')] = $myConf->getVar('conf_value');
        }

        if (null === $this->pass || '' == $this->pass || null === $this->vpass || '' == $this->vpass) {
            $this->setError(\_XHELP_MESSAGE_NOT_SUPPLIED);
            //$stop .= _US_ENTERPWD.'<br>';
        }
        if (null !== $this->pass && ($this->pass != $this->vpass)) {
            $this->setError(\_XHELP_MESSAGE_NOT_SAME);
            //$stop .= _US_PASSNOTSAME.'<br>';
        } elseif (('' != $this->pass) && (mb_strlen($this->pass) < $xoopsConfigUser['minpass'])) {
            $this->setError(\sprintf(\_XHELP_MESSAGE_SHORT, $xoopsConfigUser['minpass']));
            //$stop .= sprintf(_US_PWDTOOSHORT,$xoopsConfigUser['minpass'])."<br>";
        }
    }
}
