<?php declare(strict_types=1);

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

use XoopsModules\Xhelp;

require_once \dirname(__DIR__, 2) . '/mainfile.php';

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require_once XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}

require_once __DIR__ . '/preloads/autoloader.php';
require_once __DIR__ . '/include/common.php';

global $xoopsUser, $xoopsConfig, $xoopsModule, $xoopsModuleConfig, $xoopsTpl, $xoopsLogger;

$helper = Xhelp\Helper::getInstance();

// Load language files
$helper->loadLanguage('admin');
$helper->loadLanguage('modinfo');
$helper->loadLanguage('main');
$helper->loadLanguage('common');

//require_once XHELP_BASE_PATH . '/functions.php';
// require_once XHELP_CLASS_PATH . '/session.php';
// require_once XHELP_CLASS_PATH . '/eventService.php';

//$session = new Xhelp\Session();
$session = Xhelp\Session::getInstance();

$myts = \MyTextSanitizer::getInstance();

if (!isset($GLOBALS['xoTheme']) || !is_object($GLOBALS['xoTheme'])) {
    require $GLOBALS['xoops']->path('class/theme.php');
    $GLOBALS['xoTheme'] = new \xos_opal_Theme();
}

if (!isset($GLOBALS['xoopsTpl']) || !($GLOBALS['xoopsTpl'] instanceof XoopsTpl)) {
    require $GLOBALS['xoops']->path('class/template.php');
    $xoopsTpl = new XoopsTpl();
}

$roleReset     = false;
$xhelp_isStaff = false;

// Is the current user a staff member?
if ($xoopsUser) {
    /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
    $staffHandler = $helper->getHandler('Staff');
    $staff        = $staffHandler->getByUid($xoopsUser->getVar('uid'));
    if ($staff) {
        $xhelp_isStaff = true;

        // Check if the staff member permissions have changed since the last page request
        if ($myTime = $session->get('xhelp_permTime')) {
            $dbTime = $staff->getVar('permTimestamp');
            if ($dbTime > $myTime) {
                $roleReset = true;
            }
        } else {
            $roleReset = true;
        }

        // Update staff member permissions (if necessary)
        if ($roleReset) {
            $updateRoles = $staff->resetRoleRights();
            $session->set('xhelp_permTime', time());
        }

        //Retrieve the staff member's saved searches
        if (!$aSavedSearches = $session->get('xhelp_savedSearches')) {
            $aSavedSearches = Xhelp\Utility::getSavedSearches($xoopsUser->getVar('uid'));
            $session->set('xhelp_savedSearches', $aSavedSearches);
        }
    }
}

$xhelp_module_css    = XHELP_BASE_URL . '/assets/css/xhelp.css';
$xhelp_module_header = '<link rel="stylesheet" type="text/css" media="all" href="' . $xhelp_module_css . '">';

// @todo - this line is for compatiblity, remove once all references to $isStaff have been modified
//$isStaff = $xhelp_isStaff;
