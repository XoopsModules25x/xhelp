<?php
//$Id: notifications.php,v 1.7 2005/12/02 15:52:21 eric_juden Exp $
include('../../../include/cp_header.php');
include_once('admin_header.php');
require_once(XHELP_CLASS_PATH.'/session.php');
$_xhelpSession = new Session();
$hNotification =& xhelpGetHandler('notification');

global $xoopsModule;
if(!$templates =& $_xhelpSession->get("xhelp_notifications")){
    $templates =& $xoopsModule->getInfo('_email_tpl');
    $_xhelpSession->set("xhelp_notifications", $templates);
}
$has_notifications = count($templates);

$aStaffSettings = array('2' => _AM_XHELP_STAFF_SETTING2, // '1' => _AM_XHELP_STAFF_SETTING1, -- removed because we don't need it
                            '3' => _AM_XHELP_STAFF_SETTING3, '4' => _AM_XHELP_STAFF_SETTING4);
$aUserSettings = array('1' => _AM_XHELP_USER_SETTING1, '2' => _AM_XHELP_USER_SETTING2);

// Also in profile.php
$aNotifications = array(XHELP_NOTIF_NEWTICKET => array(
                                     'name' => _AM_XHELP_NOTIF_NEW_TICKET, 
                                     'email_tpl' => array('1'=>$templates[1], 
                                                          '18'=>$templates[18], '20'=>$templates[20], '21'=>$templates[21], 
                                                          '22'=>$templates[22], '23'=>$templates[23], '24'=>$templates[24])),
XHELP_NOTIF_DELTICKET => array(
                                     'name' => _AM_XHELP_NOTIF_DEL_TICKET, 
                                     'email_tpl' => array('2'=>$templates[2], '12'=>$templates[12])),
XHELP_NOTIF_EDITTICKET => array(
                                     'name' => _AM_XHELP_NOTIF_MOD_TICKET, 
                                     'email_tpl' => array('3'=>$templates[3], '13'=>$templates[13])),
XHELP_NOTIF_NEWRESPONSE => array(
                                     'name' => _AM_XHELP_NOTIF_NEW_RESPONSE, 
                                     'email_tpl' => array('4'=>$templates[4], '14'=>$templates[14])),
XHELP_NOTIF_EDITRESPONSE => array(
                                     'name' => _AM_XHELP_NOTIF_MOD_RESPONSE, 
                                     'email_tpl' => array('5'=>$templates[5], '15'=>$templates[15])),
XHELP_NOTIF_EDITSTATUS => array(
                                     'name' => _AM_XHELP_NOTIF_MOD_STATUS, 
                                     'email_tpl' => array('6'=>$templates[6], '16'=>$templates[16])),
XHELP_NOTIF_EDITPRIORITY => array(
                                     'name' => _AM_XHELP_NOTIF_MOD_PRIORITY, 
                                     'email_tpl' => array('7'=>$templates[7], '17'=>$templates[17])),
XHELP_NOTIF_EDITOWNER => array(
                                     'name' => _AM_XHELP_NOTIF_MOD_OWNER, 
                                     'email_tpl' => array('8'=>$templates[8], '11'=>$templates[11])),
XHELP_NOTIF_CLOSETICKET => array(
                                     'name' => _AM_XHELP_NOTIF_CLOSE_TICKET, 
                                     'email_tpl' => array('9'=>$templates[9], '19'=>$templates[19])),
XHELP_NOTIF_MERGETICKET => array(
                                     'name' => _AM_XHELP_NOTIF_MERGE_TICKET, 
                                     'email_tpl' => array('10'=>$templates[10], '25'=>$templates[25])));

$op = 'default';
if (isset($_REQUEST['op'])){
    $op = $_REQUEST['op'];
}

switch($op){
    case "edit":
        edit();
        break;

    case "manage":
        manage();
        break;

    case "modifyEmlTpl":
        modifyEmlTpl();
        break;

    Default:
        manage();
}

function edit()
{
    global $xoopsModule, $_xhelpSession, $aNotifications, $has_notifications, $aStaffSettings,
    $aUserSettings, $hNotification;

    if(isset($_REQUEST['id'])){
        $id = intval($_REQUEST['id']);
    } else {
        // No id specified, return to manage page
        redirect_header(XHELP_ADMIN_URL."/notifications.php?op=manage", 3, _AM_XHELP_MESSAGE_NO_ID);
    }

    $settings =& $hNotification->get($id);

    xoops_cp_header();
    //echo $oAdminButton->renderButtons('manNotify');
    $indexAdmin = new ModuleAdmin();
    echo $indexAdmin->addNavigation('notifications.php');

    $_xhelpSession->set("xhelp_return_page", substr(strstr($_SERVER['REQUEST_URI'], 'admin/'), 6));

    if(isset($_POST['save_notification'])){
        $settings->setVar('staff_setting', intval($_POST['staff_setting']));
        $settings->setVar('user_setting', intval($_POST['user_setting']));
        if($_POST['staff_setting'] == XHELP_NOTIF_STAFF_DEPT){
            $settings->setVar('staff_options', $_POST['roles']);
        } else {
            $settings->setVar('staff_options', array());
        }
        $hNotification->insert($settings, true);
        header("Location: ".XHELP_ADMIN_URL."/notifications.php?op=edit&id=$id");
    }

    // Retrieve list of email templates
    if(!$templates =& $_xhelpSession->get("xhelp_notifications")){
        $templates =& $xoopsModule->getInfo('_email_tpl');
        $_xhelpSession->set("xhelp_notifications", $templates);
    }
    $notification = $aNotifications[$id];

    $staff_settings = xhelpGetMeta("notify_staff{$id}");
    $user_settings = xhelpGetMeta("notify_user{$id}");
    $hRoles =& xhelpGetHandler('role');
    if($settings->getVar('staff_setting') == XHELP_NOTIF_STAFF_DEPT){
        $selectedRoles = $settings->getVar('staff_options');
    } else {
        $selectedRoles = array();
    }
    $roles =& $hRoles->getObjects();

    echo "<form method='post' action='".XHELP_ADMIN_URL."/notifications.php?op=edit&amp;id=".$id."'>";
    echo "<table width='100%' cellspacing='1' class='outer'>";
    echo "<tr><th colspan='2'>".$notification['name']."</th></tr>";
    echo "<tr><td class='head' width='20%'>"._AM_XHELP_TEXT_NOTIF_STAFF."</td>
              <td class='even' valign='top'>";
    echo "<table border='0'>";
    echo "<tr>";
    foreach($aStaffSettings as $value=>$setting){
        echo "<td valign='top'>";
        if($settings->getVar('staff_setting') == $value){
            $checked = "checked='checked'";
        } else {
            $checked = '';
        }
        echo "<input type='radio' name='staff_setting' id='staff".$value."' value='".$value."' $checked />
                          <label for='staff".$value."'>".$setting."</label>&nbsp;";
        if($value == XHELP_NOTIF_STAFF_DEPT){
            echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <select name='roles[]' multiple='multiple'>";
            foreach($roles as $role){
                $role_id = $role->getVar('id');
                if(in_array($role_id, $selectedRoles)){
                    echo "<option value='".$role_id."' selected='selected'>".$role->getVar('name')."</option>";
                } else {
                    echo "<option value='".$role_id."'>".$role->getVar('name')."</option>";
                }
            }
            echo "</select>";
        }
        echo "</td>";
    }
    echo "</tr></table>";
    echo "</td>
          </tr>";
    echo "<tr><td class='head' width='20%'>"._AM_XHELP_TEXT_NOTIF_USER."</td>
              <td class='even'>";
    foreach($aUserSettings as $value=>$setting){
        if($settings->getVar('user_setting') == $value){
            $checked = "checked='checked'";
        } else {
            $checked = '';
        }
        echo "<input type='radio' name='user_setting' id='user".$value."' value='".$value."' $checked />
                          <label for='user".$value."'>".$setting."</label>&nbsp;";
    }
    echo "</td>
          </tr>";
    echo "<tr>
              <td class='head'></td>
              <td class='even'><input type='submit' name='save_notification' value='"._AM_XHELP_BUTTON_SUBMIT."' /></td>
          </tr>";
    echo "</table></form><br />";

    echo "<table width='100%' cellspacing='1' class='outer'>";
    echo "<tr><th colspan='3'>"._AM_XHELP_TEXT_ASSOC_TPL."</th></tr>";
    echo "<tr class='head'><td>"._AM_XHELP_TEXT_TEMPLATE_NAME."</td>
                           <td>"._AM_XHELP_TEXT_DESCRIPTION."</td>
                           <td>"._AM_XHELP_TEXT_ACTIONS."</td></tr>";
    foreach($notification['email_tpl'] as $template){
        echo "<tr class='even'>
                  <td>".$template['title']."</a></td><td>".$template['description']."</td>
                  <td><a href='".XHELP_ADMIN_URL."/notifications.php?op=modifyEmlTpl&amp;file=".$template['mail_template'].".tpl'>
                      <img src='".XOOPS_URL."/modules/xhelp/images/button_edit.png' title='"._AM_XHELP_TEXT_EDIT."' name='editNotification' /></a>
                  </td>
              </tr>";
    }
    echo "</table>";

    xoops_cp_footer();
}

function manage()
{
    global $xoopsModule, $_xhelpSession, $aNotifications, $has_notifications, $xoopsDB, $aStaffSettings,
    $aUserSettings, $hNotification;

    xoops_cp_header();
    //echo $oAdminButton->renderButtons('manNotify');
    $indexAdmin = new ModuleAdmin();
    echo $indexAdmin->addNavigation('notifications.php');

    $settings =& $hNotification->getObjects(null, true);

    echo "<table width='100%' cellspacing='1' class='outer'>";
    echo "<tr><th colspan='3'>"._AM_XHELP_TEXT_MANAGE_NOTIFICATIONS."</th></tr>";
    if($has_notifications){
        echo "<tr class='head'>
                  <td>"._AM_XHELP_TEXT_NOTIF_NAME."</td>
                  <td>"._AM_XHELP_TEXT_SUBSCRIBED_MEMBERS."</td>
                  <td>"._AM_XHELP_TEXT_ACTIONS."</td>
              </tr>";
        foreach($aNotifications as $template_id=>$template){
            $cSettings = $settings[$template_id];
            $staff_setting = $cSettings->getVar('staff_setting');
            $user_setting = $cSettings->getVar('user_setting');

            // Build text of who gets notification
            if($user_setting == XHELP_NOTIF_USER_YES){
                if($staff_setting == XHELP_NOTIF_STAFF_NONE){
                    $sSettings = _AM_XHELP_TEXT_SUBMITTER;
                } else {
                    $sSettings = $aStaffSettings[$staff_setting]." "._AM_XHELP_TEXT_AND." "._AM_XHELP_TEXT_SUBMITTER;
                }
            } else {
                if($staff_setting == XHELP_NOTIF_STAFF_NONE){
                    $sSettings = '';
                } else {
                    $sSettings = $aStaffSettings[$staff_setting];
                }
            }
            // End Build text of who gets notification

            echo "<tr class='even'>
                     <td width='20%'>".$template['name']."</td>
                     <td>".$sSettings."</td>
                     <td>
                         <a href='notifications.php?op=edit&amp;id=".$template_id."'><img src='".XOOPS_URL."/modules/xhelp/images/button_edit.png' title='"._AM_XHELP_TEXT_EDIT."' name='editNotification' /></a>
                     </td>
                  </tr>";
        }
    } else {
        // No notifications found (Should never happen)
        echo "<tr><td class='even' colspan='3'>"._AM_XHELP_TEXT_NO_RECORDS."</td></tr>";
    }
    echo "</table>";

    xoops_cp_footer();
}

function modifyEmlTpl()
{
    global $xoopsConfig, $_xhelpSession;

    if (is_dir(XOOPS_ROOT_PATH.'/modules/xhelp/language/'.$xoopsConfig['language'].'/mail_template')) {
        $opendir = opendir(XOOPS_ROOT_PATH.'/modules/xhelp/language/'.$xoopsConfig['language'].'/mail_template/');
        $dir = XOOPS_ROOT_PATH.'/modules/xhelp/language/'.$xoopsConfig['language'].'/mail_template/';
        $url = XOOPS_URL.'/modules/xhelp/language/'.$xoopsConfig['language'].'/mail_template/';
    } else {
        $opendir = opendir(XOOPS_ROOT_PATH.'/modules/xhelp/language/english/mail_template/');
        $dir = XOOPS_ROOT_PATH.'/modules/xhelp/language/english/mail_template/';
        $url = XOOPS_URL.'/modules/xhelp/language/english/mail_template/';
    }

    $notNames = array(
    _MI_XHELP_DEPT_NEWTICKET_NOTIFYTPL => array(_MI_XHELP_DEPT_NEWTICKET_NOTIFY, _MI_XHELP_DEPT_NEWTICKET_NOTIFYDSC, _MI_XHELP_DEPT_NEWTICKET_NOTIFYTPL),
    _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYTPL => array(_MI_XHELP_DEPT_REMOVEDTICKET_NOTIFY, _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYDSC, _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYTPL),
    _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYTPL => array(_MI_XHELP_DEPT_NEWRESPONSE_NOTIFY, _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYDSC, _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYTPL),
    _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYTPL => array(_MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFY, _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYDSC, _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYTPL),
    _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYTPL => array(_MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFY, _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYDSC, _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYTPL),
    _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYTPL => array(_MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFY, _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYDSC, _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYTPL),
    _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYTPL => array(_MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFY, _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYDSC, _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYTPL),
    _MI_XHELP_DEPT_NEWOWNER_NOTIFYTPL => array(_MI_XHELP_DEPT_NEWOWNER_NOTIFY, _MI_XHELP_DEPT_NEWOWNER_NOTIFYDSC, _MI_XHELP_DEPT_NEWOWNER_NOTIFYTPL),
    _MI_XHELP_DEPT_CLOSETICKET_NOTIFYTPL => array(_MI_XHELP_DEPT_CLOSETICKET_NOTIFY, _MI_XHELP_DEPT_CLOSETICKET_NOTIFYDSC, _MI_XHELP_DEPT_CLOSETICKET_NOTIFYTPL),
    _MI_XHELP_TICKET_NEWOWNER_NOTIFYTPL => array(_MI_XHELP_TICKET_NEWOWNER_NOTIFY, _MI_XHELP_TICKET_NEWOWNER_NOTIFYDSC, _MI_XHELP_TICKET_NEWOWNER_NOTIFYTPL),
    _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYTPL => array(_MI_XHELP_TICKET_REMOVEDTICKET_NOTIFY, _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYDSC, _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYTPL),
    _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYTPL => array(_MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFY, _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYDSC, _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYTPL),
    _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYTPL => array(_MI_XHELP_TICKET_NEWRESPONSE_NOTIFY, _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYDSC, _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYTPL),
    _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYTPL => array(_MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFY, _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYDSC, _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYTPL),
    _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYTPL => array(_MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFY, _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYDSC, _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYTPL),
    _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYTPL => array(_MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFY, _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYDSC, _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYTPL),
    _MI_XHELP_TICKET_NEWTICKET_NOTIFYTPL => array(_MI_XHELP_TICKET_NEWTICKET_NOTIFY, _MI_XHELP_TICKET_NEWTICKET_NOTIFYDSC, _MI_XHELP_TICKET_NEWTICKET_NOTIFYTPL),
    _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYTPL => array(_MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFY, _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYDSC, _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYTPL),
    _MI_XHELP_TICKET_CLOSETICKET_NOTIFYTPL => array(_MI_XHELP_TICKET_CLOSETICKET_NOTIFY, _MI_XHELP_TICKET_CLOSETICKET_NOTIFYDSC, _MI_XHELP_TICKET_CLOSETICKET_NOTIFYTPL),
    _MI_XHELP_TICKET_NEWUSER_NOTIFYTPL => array(_MI_XHELP_TICKET_NEWUSER_NOTIFY, _MI_XHELP_TICKET_NEWUSER_NOTIFYDSC, _MI_XHELP_TICKET_NEWUSER_NOTIFYTPL),
    _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYTPL => array(_MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFY, _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYDSC, _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYTPL),
    _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYTPL => array(_MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFY, _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYDSC, _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYTPL),
    _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYTPL => array(_MI_XHELP_TICKET_EMAIL_ERROR_NOTIFY, _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYDSC, _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYTPL),
    _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYTPL => array(_MI_XHELP_DEPT_MERGE_TICKET_NOTIFY, _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYDSC, _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYTPL),
    _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYTPL => array(_MI_XHELP_TICKET_MERGE_TICKET_NOTIFY, _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYDSC, _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYTPL));

    $notKeys = array_keys($notNames);

    while(($file = readdir($opendir)) != null) {
        //Do not Display .
        if (is_dir($file)) {
            continue;
        }

        if (!in_array($file, $notKeys)) {
            continue;
        }
         
        $aFile = Array();
        $aFile['name'] = $notNames[$file][0];
        $aFile['desc'] = $notNames[$file][1];
        $aFile['filename'] = $notNames[$file][2];
        $aFile['url'] = "index.php?op=modifyEmlTpl&amp;file=$file";
        $aFiles[] = $aFile;
    }

    if(!isset($_GET['file'])){
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manNotify');
        $indexAdmin = new ModuleAdmin();
        echo $indexAdmin->addNavigation('notifications.php');

        echo "<table width='100%' border='0' cellspacing='1' class='outer'>
              <tr><th colspan='2'><label>". _AM_XHELP_MENU_MODIFY_EMLTPL ."</label></th></tr>
              <tr class='head'><td>". _AM_XHELP_TEXT_TEMPLATE_NAME ."</td><td>". _AM_XHELP_TEXT_DESCRIPTION ."</td></tr>";

        foreach($aFiles as $file){
            static $rowSwitch = 0;
            if($rowSwitch == 0){
                echo "<tr class='odd'><td><a href='".$file['url']."'>". $file['name'] ."</a></td><td>". $file['desc'] ."</td></tr>";
                $rowSwitch = 1;
            } else {
                echo "<tr class='even'><td><a href='".$file['url']."'>". $file['name'] ."</a></td><td>". $file['desc'] ."</td></tr>";
                $rowSwitch = 0;
            }
        }
        echo "</table>";
    } else {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manNotify');
        $indexAdmin = new ModuleAdmin();
        echo $indexAdmin->addNavigation('notifications.php');

        foreach($aFiles as $file){
            if($_GET['file'] == $file['filename']){
                $myFileName = $file['filename'];
                $myFileDesc = $file['desc'];
                $myName = $file['name'];
                break;
            }
        }
        if(!$has_write = is_writable($dir.$myFileName)){
            $message = _AM_XHELP_MESSAGE_FILE_READONLY;
            $handle = fopen($dir.$myFileName, 'r');
            $fileSize = filesize($dir.$myFileName);
        } elseif(isset($_POST['editTemplate'])){
            $handle = fopen($dir.$myFileName, 'w+');
        } else {
            $handle = fopen($dir.$myFileName, 'r+');
            $fileSize = filesize($dir.$myFileName);
        }

        if(isset($_POST['editTemplate'])){
            if(isset($_POST['templateText'])){
                $text = $_POST['templateText'];    // Get new text for template
            } else {
                $text = '';
            }

            if(!$returnPage =& $_xhelpSession->get("xhelp_return_page")){
                $returnPage = false;
            }

            if(fwrite($handle, $text)){
                $message = _AM_XHELP_MESSAGE_FILE_UPDATED;
                $fileSize = filesize($dir.$myFileName);
                fclose($handle);
                if($returnPage){
                    header("Location: ".XHELP_ADMIN_URL."/$returnPage");
                } else {
                    header("Location: ".XHELP_ADMIN_URL."/notifications.php");
                }
            } else {
                $message = _AM_XHELP_MESSAGE_FILE_UPDATED_ERROR;
                $fileSize = filesize($dir.$myFileName);
                fclose($handle);
                if($returnPage){
                    redirect_header("Location: ".XHELP_ADMIN_URL."/$returnPage", 3, $message);
                } else {
                    redirect_header("Location: ".XHELP_ADMIN_URL."/notifications.php", 3, $message);
                }
            }
        }
        if(!$has_write){
            echo "<div id='readOnly' class='errorMsg'>";
            echo $message;
            echo "</div>";
        }

        echo "<form action='".XHELP_ADMIN_URL."/notifications.php?op=modifyEmlTpl&amp;file=".$myFileName."' method='post'>";
        echo "<table width='100%' border='0' cellspacing='1' class='outer'>
              <tr><th colspan='2'>".$myName."</th></tr>
              <tr><td colspan='2' class='head'>". $myFileDesc ."</td></tr>";

        echo "<tr class='odd'>
                  <td><textarea name='templateText' cols='40' rows='40'>". fread($handle, $fileSize) ."</textarea></td>
                  <td valign='top'>
                      <b>". _AM_XHELP_TEXT_GENERAL_TAGS ."</b>
                      <ul>
                        <li>". _AM_XHELP_TEXT_GENERAL_TAGS1 ."</li>
                        <li>". _AM_XHELP_TEXT_GENERAL_TAGS2 ."</li>
                        <li>". _AM_XHELP_TEXT_GENERAL_TAGS3 ."</li>
                        <li>". _AM_XHELP_TEXT_GENERAL_TAGS4 ."</li>
                        <li>". _AM_XHELP_TEXT_GENERAL_TAGS5 ."</li>
                      </ul>
                      <br />
                      <u>". _AM_XHELP_TEXT_TAGS_NO_MODIFY ."</u>
                  </td>
              </tr>";

        if($has_write){
            echo "<tr><td class='foot' colspan='2'><input type='submit' name='editTemplate' value='". _AM_XHELP_BUTTON_UPDATE ."' class='formButton' /></td></tr>";
        }
        echo "</table></form>";
    }
    include_once "admin_footer.php";
}