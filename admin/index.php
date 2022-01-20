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
 * @copyright    XOOPS Project (https://xoops.org)
 * @license      GNU GPL 2 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       XOOPS Development Team
 */

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Xhelp\{
    Common\Configurator,
    Common\TestdataButtons,
    Helper,
    Utility
};

/** @var Admin $adminObject */
/** @var Helper $helper */
/** @var Utility $utility */
require_once __DIR__ . '/admin_header.php';
// Display Admin header
xoops_cp_header();

$moduleDirName      = \basename(\dirname(__DIR__));
$moduleDirNameUpper = \mb_strtoupper($moduleDirName);

$adminObject  = Admin::getInstance();
$configurator = new Configurator();

$modStats    = [];
$moduleStats = $utility::getModuleStats($configurator);

$adminObject->addInfoBox(constant('CO_' . $moduleDirNameUpper . '_' . 'STATS_SUMMARY'));
if (is_array($moduleStats) && count($moduleStats) > 0) {
    foreach ($moduleStats as $key => $value) {
        switch ($key) {
            case 'totaldepartments':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_XHELP_TOTAL_DEPARTMENTS));
                break;
            case 'totalfiles':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_XHELP_TOTAL_FILES));
                break;
            case 'totallogmessages':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_XHELP_TOTAL_LOGMESSAGES));
                break;
            case 'totalresponses':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_XHELP_TOTAL_RESPONSES));
                break;
            case 'totalstaff':
                $ret = '<span style=\'font-weight: bold; color: red;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_XHELP_TOTALS_STAFF));
                break;
            case 'totalstaffreview':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_XHELP_TOTAL_STAFF_REVIEWS));
                break;
            case 'totaltickets':
                $ret = '<span style=\'font-weight: bold; color: red;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_XHELP_TOTAL_TICKETS));
                break;
            case 'totalroles':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_XHELP_TOTAL_ROLES));
                break;
            case 'totalnotifications':
                $ret = '<span style=\'font-weight: bold; color: red;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_XHELP_TOTAL_NOTIFICATIONS));
                break;
            case 'totalticketsolutions':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_XHELP_TOTAL_TICKET_SOLUTIONS));
                break;
        }
    }
}

$adminObject->displayNavigation(basename(__FILE__));

//check for latest release
//$newRelease = $utility->checkVerModule($helper);
//if (null !== $newRelease) {
//    $adminObject->addItemButton($newRelease[0], $newRelease[1], 'download', 'style="color : Red"');
//}

//------------- Test Data Buttons ----------------------------
if ($helper->getConfig('displaySampleButton')) {
    TestdataButtons::loadButtonConfig($adminObject);
    $adminObject->displayButton('left', '');
}
$op = Request::getString('op', '', 'GET');
switch ($op) {
    case 'hide_buttons':
        TestdataButtons::hideButtons();
        break;
    case 'show_buttons':
        TestdataButtons::showButtons();
        break;
}
//------------- End Test Data Buttons ----------------------------

$adminObject->displayIndex();
echo $utility::getServerStats();

//codeDump(__FILE__);
require_once __DIR__ . '/admin_footer.php';
