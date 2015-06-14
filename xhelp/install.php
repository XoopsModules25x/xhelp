<?php
//$Id: install.php,v 1.15 2005/10/07 21:03:17 eric_juden Exp $
require('../../mainfile.php');
if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    include_once(XOOPS_ROOT_PATH.'/modules/xhelp/include/constants.php');
}

include_once(XHELP_BASE_PATH.'/functions.php');
xhelpIncludeLang('modinfo');
xhelpIncludeLang('main');

$op = '';

if(isset($_GET['op'])){
    $op = $_GET['op'];
}

switch($op){
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

function updateDepts()
{
    global $xoopsDB;

    echo "<link rel='stylesheet' type='text/css' media'screen' href='".XOOPS_URL."/xoops.css' />
          <link rel='stylesheet' type='text/css' media='screen' href='". xoops_getcss() ."' />
          <link rel='stylesheet' type='text/css' media='screen' href='../system/style.css' />";
    echo "<table width='100%' border='1' cellpadding='0' cellspacing='2' class='formButton'>";
    echo "<tr><th>". _MI_XHELP_DEFAULT_DEPT ."</th></tr>";
    echo "<tr class='head'><td>". _XHELP_TEXT_DEPTS_ADDED ."</td></tr>";

    if(!$xhelp_config = removeDepts()){
        return false;
    }

    //Retrieve list of departments
    $hDept =& xhelpGetHandler('department');
    $depts =& $hDept->getObjects();

    $class = 'odd';
    foreach($depts as $dept){
        $deptid = $dept->getVar('id');
        $deptname = $dept->getVar('department');

        $hConfigOption =& xhelpGetHandler('configoption');
        $newOption =& $hConfigOption->create();
        $newOption->setVar('confop_name', $deptname);
        $newOption->setVar('confop_value', $deptid);
        $newOption->setVar('conf_id', $xhelp_config);

        if(!$hConfigOption->insert($newOption, true)){
            return false;
        }

        echo "<tr class='". $class ."'><td>". $dept->getVar('department') ."</td></tr>";
        $class = ( $class == 'odd' ) ? 'even' : 'odd';
    }
    echo "<tr class='foot'><td>". _XHELP_TEXT_UPDATE_COMP ."<br /><br /><input type='button' name='closeWindow' value='". _XHELP_TEXT_CLOSE_WINDOW ."' class='formButton' onClick=\"javascript:window.opener.location=window.opener.location;window.close();\" /></td></tr>";
    echo "</table>";

    return true;
}

function removeDepts()
{
    global $xoopsDB;

    //Needs force on delete
    $hConfig =& xoops_gethandler('config');

    // Select the config from the xoops_config table
    $crit = new Criteria('conf_name', 'xhelp_defaultDept');
    $config =& $hConfig->getConfigs($crit);

    if(count($config) > 0){
        $xhelp_config = $config[0]->getVar('conf_id');
    } else {
        return false;
    }

    // Remove the config options
    $hConfigOption =& xhelpGetHandler('configoption');
    $crit = new Criteria('conf_id', $xhelp_config);
    $configOptions =& $hConfigOption->getObjects($crit);

    if(count($configOptions) > 0){
        foreach($configOptions as $option){
            if(!$hConfigOption->deleteAll($option, true)){   // Remove each config option
                return false;
            }
        }
    } else {    // If no config options were found
        return $xhelp_config;
    }

    return $xhelp_config;
}

function updateTopics($onInstall = false)
{
    if(!$onInstall){    // Don't need to display anything if installing
        echo "<link rel='stylesheet' type='text/css' media='screen' href='". XOOPS_URL ."/xoops.css' />
              <link rel='stylesheet' type='text/css' media='screen' href='". xoops_getcss() ."' />
              <link rel='stylesheet' type='text/css' media='screen' href='../system/style.css' />";
        echo "<table width='100%' border='1' cellpadding='0' cellspacing='2' class='formButton'>";
        echo "<tr><th>". _MI_XHELP_ANNOUNCEMENTS ."</th></tr>";
        echo "<tr class='head'><td>". _XHELP_TEXT_TOPICS_ADDED ."</td></tr>";
    }
    if(!$xhelp_config = removeTopics()){
        return false;
    }

    //Retrieve list of topics from DB
    global $xoopsDB;
    $ret = $xoopsDB->query("SELECT topic_id, topic_title FROM " . $xoopsDB->prefix('topics'));
    $myTopics = array();
    $myTopics[_MI_XHELP_ANNOUNCEMENTS_NONE]  = 0;
    while ($arr = $xoopsDB->fetchArray($ret)) {
        $myTopics[$arr['topic_title']] = $arr['topic_id'];
    }

    $class = 'odd';
    foreach($myTopics as $topic=>$value){
        $xhelp_id = $xoopsDB->genId($xoopsDB->prefix('configoption').'_uid_seq');
        $sql = sprintf("INSERT INTO %s (confop_id, confop_name, confop_value, conf_id) VALUES (%u, %s, %s, %u)",
        $xoopsDB->prefix('configoption'), $xhelp_id, $xoopsDB->quoteString($topic),
        $xoopsDB->quoteString($value), $xhelp_config);

        if(!$result = $xoopsDB->queryF($sql)){
            return false;
        }

        if(empty($xhelp_id)){
            $xhelp_id = $xoopsDB->getInsertId();
        }
        if(!$onInstall){    // Don't need to display anything if installing
            echo "<tr class='". $class ."'><td>". $topic ."</td></tr>";
            $class = ( $class == 'odd' ) ? 'even' : 'odd';
        }
    }
    if(!$onInstall){    // Don't need to display anything if installing
        echo "<tr class='foot'><td>". _XHELP_TEXT_UPDATE_COMP ."<br /><br /><input type='button' name='closeWindow' value='". _XHELP_TEXT_CLOSE_WINDOW ."' class='formButton' onClick=\"javascript:window.opener.location=window.opener.location;window.close();\" /></td></tr>";
        echo "</table>";
    }
}

function removeTopics()
{
    global $xoopsDB;
    // Select the config from the xoops_config table
    $sql = sprintf("SELECT * FROM %s WHERE conf_name = %s", $xoopsDB->prefix('config'), "'xhelp_announcements'");
    if(!$ret = $xoopsDB->query($sql)){
        return false;
    }
    $xhelp_config = false;
    $arr = $xoopsDB->fetchArray($ret);
    $xhelp_config = $arr['conf_id'];

    // Remove the config options
    $sql = sprintf("DELETE FROM %s WHERE conf_id = %s", $xoopsDB->prefix('configoption'), $xhelp_config);
    if(!$ret = $xoopsDB->queryF($sql)){
        return false;
    }

    return $xhelp_config;
}

function xoops_module_install_xhelp(&$module)
{
    $myTopics = updateTopics(true);
    $hasRoles = xhelpCreateRoles();
    $hasStatuses = xhelpCreateStatuses();
    $hasNotifications = xhelpCreateNotifications();
    $hasTicketLists = xhelpCreateDefaultTicketLists();

    return true;
}
