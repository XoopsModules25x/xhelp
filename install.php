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

use Xmf\Request;
use XoopsModules\Xhelp;

/** @var Xhelp\Helper $helper */
require_once __DIR__ . '/header.php';

require_once \dirname(__DIR__, 2) . '/mainfile.php';
if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require_once XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}

//require_once XHELP_BASE_PATH . '/functions.php';
$helper->loadLanguage('modinfo');
$helper->loadLanguage('main');

$op = '';

if (Request::hasVar('op', 'GET')) {
    $op = $_GET['op'];
}

switch ($op) {
    case 'updateTopics':
        global $xoopsModule;
        $myTopics = updateTopics();
        break;
    case 'updateDepts':
        global $xoopsModule;
        $myDepts = updateDepts();
        break;
    default:
        return false;
}

/**
 * @return bool
 */
function updateDepts(): bool
{
    global $xoopsDB;
    $helper = Xhelp\Helper::getInstance();

    echo "<link rel='stylesheet' type='text/css' media'screen' href='" . XOOPS_URL . "/xoops.css'>
          <link rel='stylesheet' type='text/css' media='screen' href='" . xoops_getcss() . "'>
          <link rel='stylesheet' type='text/css' media='screen' href='../system/style.css'>";
    echo "<table width='100%' border='1' cellpadding='0' cellspacing='2' class='formButton'>";
    echo '<tr><th>' . _MI_XHELP_DEFAULT_DEPT . '</th></tr>';
    echo "<tr class='head'><td>" . _XHELP_TEXT_DEPTS_ADDED . '</td></tr>';

    if (!$xhelp_config = removeDepts()) {
        return false;
    }

    //Retrieve list of departments
    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    $depts             = $departmentHandler->getObjects();

    $class = 'odd';
    foreach ($depts as $dept) {
        $deptid   = $dept->getVar('id');
        $deptname = $dept->getVar('department');

        /** @var \XoopsModules\Xhelp\ConfigOptionHandler $configOptionHandler */
        $configOptionHandler = $helper->getHandler('ConfigOption');
        $newOption           = $configOptionHandler->create();
        $newOption->setVar('confop_name', $deptname);
        $newOption->setVar('confop_value', $deptid);
        $newOption->setVar('conf_id', $xhelp_config);

        if (!$configOptionHandler->insert($newOption, true)) {
            return false;
        }

        echo "<tr class='" . $class . "'><td>" . $dept->getVar('department') . '</td></tr>';
        $class = ('odd' === $class) ? 'even' : 'odd';
    }
    echo "<tr class='foot'><td>" . _XHELP_TEXT_UPDATE_COMP . "<br><br><input type='button' name='closeWindow' value='" . _XHELP_TEXT_CLOSE_WINDOW . "' class='formButton' onClick=\"window.opener.location=window.opener.location;window.close();\"></td></tr>";
    echo '</table>';

    return true;
}

/**
 * @return bool
 */
function removeDepts(): bool
{
    global $xoopsDB;
    $helper = Xhelp\Helper::getInstance();

    //Needs force on delete
    /** @var \XoopsConfigHandler $configHandler */
    $configHandler = xoops_getHandler('config');

    // Select the config from the xoops_config table
    $criteria = new \Criteria('conf_name', 'xhelp_defaultDept');
    $config   = $configHandler->getConfigs($criteria);

    if (count($config) > 0) {
        $xhelp_config = $config[0]->getVar('conf_id');
    } else {
        return false;
    }

    // Remove the config options
    /** @var \XoopsModules\Xhelp\ConfigOptionHandler $configOptionHandler */
    $configOptionHandler = $helper->getHandler('ConfigOption');
    $criteria            = new \Criteria('conf_id', $xhelp_config);
    $configOptions       = $configOptionHandler->getObjects($criteria);

    if (count($configOptions) > 0) {
        foreach ($configOptions as $option) {
            if (!$configOptionHandler->deleteAll($option, true)) {   // Remove each config option
                return false;
            }
        }
    } else {    // If no config options were found
        return $xhelp_config;
    }

    return $xhelp_config;
}

/**
 * @param bool $onInstall
 * @return bool
 */
function updateTopics(bool $onInstall = false): bool
{
    if (!$onInstall) {    // Don't need to display anything if installing
        echo "<link rel='stylesheet' type='text/css' media='screen' href='" . XOOPS_URL . "/xoops.css'>
              <link rel='stylesheet' type='text/css' media='screen' href='" . xoops_getcss() . "'>
              <link rel='stylesheet' type='text/css' media='screen' href='../system/style.css'>";
        echo "<table width='100%' border='1' cellpadding='0' cellspacing='2' class='formButton'>";
        echo '<tr><th>' . _MI_XHELP_ANNOUNCEMENTS . '</th></tr>';
        echo "<tr class='head'><td>" . _XHELP_TEXT_TOPICS_ADDED . '</td></tr>';
    }
    if (!$xhelp_config = removeTopics()) {
        return false;
    }

    //Retrieve list of topics from DB
    global $xoopsDB;
    $ret                                    = $xoopsDB->query('SELECT topic_id, topic_title FROM ' . $xoopsDB->prefix('topics'));
    $myTopics                               = [];
    $myTopics[_MI_XHELP_ANNOUNCEMENTS_NONE] = 0;
    while (false !== ($arr = $xoopsDB->fetchArray($ret))) {
        $myTopics[$arr['topic_title']] = $arr['topic_id'];
    }

    $class = 'odd';
    foreach ($myTopics as $topic => $value) {
        $xhelp_id = $xoopsDB->genId($xoopsDB->prefix('configoption') . '_uid_seq');
        $sql      = sprintf('INSERT INTO `%s` (confop_id, confop_name, confop_value, conf_id) VALUES (%u, %s, %s, %u)', $xoopsDB->prefix('configoption'), $xhelp_id, $xoopsDB->quoteString($topic), $xoopsDB->quoteString($value), $xhelp_config);

        if (!$result = $xoopsDB->queryF($sql)) {
            return false;
        }

        if (empty($xhelp_id)) {
            $xhelp_id = $xoopsDB->getInsertId();
        }
        if (!$onInstall) {    // Don't need to display anything if installing
            echo "<tr class='" . $class . "'><td>" . $topic . '</td></tr>';
            $class = ('odd' === $class) ? 'even' : 'odd';
        }
    }
    if (!$onInstall) {    // Don't need to display anything if installing
        echo "<tr class='foot'><td>" . _XHELP_TEXT_UPDATE_COMP . "<br><br><input type='button' name='closeWindow' value='" . _XHELP_TEXT_CLOSE_WINDOW . "' class='formButton' onClick=\"javascript:window.opener.location=window.opener.location;window.close();\"></td></tr>";
        echo '</table>';
    }
    return true;
}

/**
 * @return bool|string
 */
function removeTopics()
{
    global $xoopsDB;
    // Select the config from the xoops_config table
    $sql = sprintf('SELECT * FROM `%s` WHERE conf_name = %s', $xoopsDB->prefix('config'), "'xhelp_announcements'");
    if (!$ret = $xoopsDB->query($sql)) {
        return false;
    }
    $xhelp_config = false;
    $arr          = $xoopsDB->fetchArray($ret);
    $xhelp_config = $arr['conf_id'];

    // Remove the config options
    $sql = sprintf('DELETE FROM `%s` WHERE conf_id = %s', $xoopsDB->prefix('configoption'), $xhelp_config);
    if (!$ret = $xoopsDB->queryF($sql)) {
        return false;
    }

    return $xhelp_config;
}

/**
 *
 * @param \XoopsModule $module
 * @return bool
 */
function xoops_module_install_xhelp(\XoopsModule $module): bool
{
    $myTopics         = updateTopics(true);
    $hasRoles         = Xhelp\Utility::createRoles();
    $hasStatuses      = Xhelp\Utility::createStatuses();
    $hasNotifications = Xhelp\Utility::createNotifications();
    $hasTicketLists   = Xhelp\Utility::createDefaultTicketLists();

    return true;
}
