<?php declare(strict_types=1);

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Xhelp\{
    Helper,
    RoleHandler,
    Session,
    Utility
};

require_once __DIR__ . '/admin_header.php';
$session = Session::getInstance();
$helper  = Helper::getInstance();
/** @var \XoopsModules\Xhelp\NotificationHandler $notificationHandler */
$notificationHandler = $helper->getHandler('Notification');

global $xoopsModule;
if (!$templates = $session->get('xhelp_notifications')) {
    $templates = $xoopsModule->getInfo('_email_tpl');
    $session->set('xhelp_notifications', $templates);
}
$has_notifications = count($templates);

$aStaffSettings = [
    2 => _AM_XHELP_STAFF_SETTING2, // 1 => _AM_XHELP_STAFF_SETTING1, -- removed because we don't need it
    3 => _AM_XHELP_STAFF_SETTING3,
    4 => _AM_XHELP_STAFF_SETTING4,
];
$aUserSettings  = ['1' => _AM_XHELP_USER_SETTING1, '2' => _AM_XHELP_USER_SETTING2];

// Also in profile.php
$aNotifications = [
    XHELP_NOTIF_NEWTICKET    => [
        'name'      => _AM_XHELP_NOTIF_NEW_TICKET,
        'email_tpl' => [
            1  => $templates[1],
            18 => $templates[18],
            20 => $templates[20],
            21 => $templates[21],
            22 => $templates[22],
            23 => $templates[23],
            24 => $templates[24],
        ],
    ],
    XHELP_NOTIF_DELTICKET    => [
        'name'      => _AM_XHELP_NOTIF_DEL_TICKET,
        'email_tpl' => [2 => $templates[2], 12 => $templates[12]],
    ],
    XHELP_NOTIF_EDITTICKET   => [
        'name'      => _AM_XHELP_NOTIF_MOD_TICKET,
        'email_tpl' => [3 => $templates[3], 13 => $templates[13]],
    ],
    XHELP_NOTIF_NEWRESPONSE  => [
        'name'      => _AM_XHELP_NOTIF_NEW_RESPONSE,
        'email_tpl' => [4 => $templates[4], 14 => $templates[14]],
    ],
    XHELP_NOTIF_EDITRESPONSE => [
        'name'      => _AM_XHELP_NOTIF_MOD_RESPONSE,
        'email_tpl' => [5 => $templates[5], 15 => $templates[15]],
    ],
    XHELP_NOTIF_EDITSTATUS   => [
        'name'      => _AM_XHELP_NOTIF_MOD_STATUS,
        'email_tpl' => [6 => $templates[6], 16 => $templates[16]],
    ],
    XHELP_NOTIF_EDITPRIORITY => [
        'name'      => _AM_XHELP_NOTIF_MOD_PRIORITY,
        'email_tpl' => [7 => $templates[7], 17 => $templates[17]],
    ],
    XHELP_NOTIF_EDITOWNER    => [
        'name'      => _AM_XHELP_NOTIF_MOD_OWNER,
        'email_tpl' => [8 => $templates[8], 11 => $templates[11]],
    ],
    XHELP_NOTIF_CLOSETICKET  => [
        'name'      => _AM_XHELP_NOTIF_CLOSE_TICKET,
        'email_tpl' => [9 => $templates[9], 19 => $templates[19]],
    ],
    XHELP_NOTIF_MERGETICKET  => [
        'name'      => _AM_XHELP_NOTIF_MERGE_TICKET,
        'email_tpl' => [10 => $templates[10], 25 => $templates[25]],
    ],
];

$op = 'default';
if (Request::hasVar('op', 'REQUEST')) {
    $op = $_REQUEST['op'];
}

switch ($op) {
    case 'edit':
        edit();
        break;
    case 'manage':
        manage();
        break;
    case 'modifyEmailTpl':
        modifyEmailTpl();
        break;
    default:
        manage();
}

function edit()
{
    global $xoopsModule, $session, $aNotifications, $has_notifications, $aStaffSettings, $aUserSettings;
    $helper = Helper::getInstance();

    if (Request::hasVar('id', 'REQUEST')) {
        $id = Request::getInt('id', 0, 'REQUEST');
    } else {
        // No id specified, return to manage page
        $helper->redirect('admin/notifications.php?op=manage', 3, _AM_XHELP_MESSAGE_NO_ID);
    }

    /** @var \XoopsModules\Xhelp\NotificationHandler $notificationHandler */
    $notificationHandler = $helper->getHandler('Notification');
    $settings            = $notificationHandler->get($id);

    if (null === $settings || false === $settings) {
        $helper->redirect('admin/notifications.php?op=manage', 3, _AM_XHELP_EDIT_ERR);
    }

    xoops_cp_header();
    //echo $oAdminButton->renderButtons('manNotify');
    $adminObject = Admin::getInstance();

    $adminObject->addItemButton(_AM_XHELP_TEXT_MANAGE_NOTIFICATIONS, 'notifications.php?op=manage', 'add');
    $adminObject->addItemButton(_AM_XHELP_MENU_MODIFY_EMLTPL, 'notifications.php?op=modifyEmailTpl', 'list');
    $adminObject->displayButton('left');

    $adminObject->displayNavigation(basename(__FILE__));

    $session->set('xhelp_return_page', mb_substr(mb_strstr($_SERVER['REQUEST_URI'], 'admin/'), 6));

    if (Request::hasVar('save_notification', 'POST')) {
        $settings->setVar('staff_setting', Request::getInt('staff_setting', 0, 'POST'));
        $settings->setVar('user_setting', Request::getInt('user_setting', 0, 'POST'));
        if (XHELP_NOTIF_STAFF_DEPT == Request::getInt('staff_setting', 0, 'POST')) {
            $settings->setVar('staff_options', $_POST['roles']);
        } else {
            $settings->setVar('staff_options', []);
        }
        $notificationHandler->insert($settings, true);
        $helper->redirect("notifications.php?op=edit&id=$id");
    }

    // Retrieve list of email templates
    if (!$templates = $session->get('xhelp_notifications')) {
        $templates = $xoopsModule->getInfo('_email_tpl');
        $session->set('xhelp_notifications', $templates);
    }
    $notification = $aNotifications[$id];

    $staff_settings = Utility::getMeta("notify_staff{$id}");
    $user_settings  = Utility::getMeta("notify_user{$id}");
    /** @var RoleHandler $roleHandler */
    $roleHandler = $helper->getHandler('Role');
    if (XHELP_NOTIF_STAFF_DEPT == $settings->getVar('staff_setting')) {
        $selectedRoles = $settings->getVar('staff_options');
    } else {
        $selectedRoles = [];
    }
    $roles = $roleHandler->getObjects();

    echo "<form method='post' action='" . XHELP_ADMIN_URL . '/notifications.php?op=edit&amp;id=' . $id . "'>";
    echo "<table width='100%' cellspacing='1' class='outer'>";
    echo "<tr><th colspan='2'>" . $notification['name'] . '</th></tr>';
    echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_NOTIF_STAFF . "</td>
              <td class='even' valign='top'>";
    echo "<table border='0'>";
    echo '<tr>';
    foreach ($aStaffSettings as $value => $setting) {
        echo "<td valign='top'>";
        if ($settings->getVar('staff_setting') == $value) {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        echo "<input type='radio' name='staff_setting' id='staff" . $value . "' value='" . $value . "' $checked>
                          <label for='staff" . $value . "'>" . $setting . '</label>&nbsp;';
        if (XHELP_NOTIF_STAFF_DEPT == $value) {
            echo "<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <select name='roles[]' multiple='multiple'>";
            foreach ($roles as $role) {
                $role_id = $role->getVar('id');
                if (in_array($role_id, $selectedRoles)) {
                    echo "<option value='" . $role_id . "' selected>" . $role->getVar('name') . '</option>';
                } else {
                    echo "<option value='" . $role_id . "'>" . $role->getVar('name') . '</option>';
                }
            }
            echo '</select>';
        }
        echo '</td>';
    }
    echo '</tr></table>';
    echo '</td>
          </tr>';
    echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_NOTIF_USER . "</td>
              <td class='even'>";
    foreach ($aUserSettings as $value => $setting) {
        if ($settings->getVar('user_setting') == $value) {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        echo "<input type='radio' name='user_setting' id='user" . $value . "' value='" . $value . "' $checked>
                          <label for='user" . $value . "'>" . $setting . '</label>&nbsp;';
    }
    echo '</td>
          </tr>';
    echo "<tr>
              <td class='head'></td>
              <td class='even'><input type='submit' name='save_notification' value='" . _AM_XHELP_BUTTON_SUBMIT . "'></td>
          </tr>";
    echo '</table></form><br>';

    echo "<table width='100%' cellspacing='1' class='outer'>";
    echo "<tr><th colspan='3'>" . _AM_XHELP_TEXT_ASSOC_TPL . '</th></tr>';
    echo "<tr class='head'><td>" . _AM_XHELP_TEXT_TEMPLATE_NAME . '</td>
                           <td>' . _AM_XHELP_TEXT_DESCRIPTION . '</td>
                           <td>' . _AM_XHELP_TEXT_ACTIONS . '</td></tr>';
    foreach ($notification['email_tpl'] as $template) {
        echo "<tr class='even'>
                  <td>" . $template['title'] . '</a></td><td>' . $template['description'] . "</td>
                  <td><a href='" . XHELP_ADMIN_URL . 'notifications.php?op=modifyEmailTpl&amp;file=' . $template['mail_template'] . ".tpl'>
                      <img src='" . XOOPS_URL . "/modules/xhelp/assets/images/button_edit.png' title='" . _AM_XHELP_TEXT_EDIT . "' name='editNotification'></a>
                  </td>
              </tr>";
    }
    echo '</table>';

    xoops_cp_footer();
}

function manage()
{
    global $xoopsModule, $session, $aNotifications, $has_notifications, $xoopsDB, $aStaffSettings, $aUserSettings;
    $helper = Helper::getInstance();

    xoops_cp_header();
    //echo $oAdminButton->renderButtons('manNotify');
    $adminObject = Admin::getInstance();

    $adminObject->addItemButton(_AM_XHELP_TEXT_MANAGE_NOTIFICATIONS, 'notifications.php?op=manage', 'add');
    $adminObject->addItemButton(_AM_XHELP_MENU_MODIFY_EMLTPL, 'notifications.php?op=modifyEmailTpl', 'list');

    $adminObject->displayNavigation(basename(__FILE__));
    $adminObject->displayButton('left');

    /** @var \XoopsModules\Xhelp\NotificationHandler $notificationHandler */
    $notificationHandler = $helper->getHandler('Notification');
    $settings            = $notificationHandler->getObjects(null, true);

    echo "<table width='100%' cellspacing='1' class='outer'>";
    echo "<tr><th colspan='3'>" . _AM_XHELP_TEXT_MANAGE_NOTIFICATIONS . '</th></tr>';
    if ($has_notifications) {
        echo "<tr class='head'>
                  <td>" . _AM_XHELP_TEXT_NOTIF_NAME . '</td>
                  <td>' . _AM_XHELP_TEXT_SUBSCRIBED_MEMBERS . '</td>
                  <td>' . _AM_XHELP_TEXT_ACTIONS . '</td>
              </tr>';
        foreach ($aNotifications as $template_id => $template) {
            //            if (isset($settings[$template_id])) {
            $cSettings = $settings[$template_id] ?? '';
            //                if (null !== $cSettings) {
            //                $staff_setting = $cSettings->getVar('staff_setting');
            //                $user_setting  = $cSettings->getVar('user_setting');
            $staff_setting = !empty($cSettings) ? $cSettings->getVar('staff_setting') : 0;
            $user_setting  = !empty($cSettings) ? $cSettings->getVar('user_setting') : 0;
            //            }
            // Build text of who gets notification
            if (XHELP_NOTIF_USER_YES == $user_setting) {
                if (\XHELP_NOTIF_STAFF_NONE == $staff_setting) {
                    $sSettings = _AM_XHELP_TEXT_SUBMITTER;
                } else {
                    $sSettings = $aStaffSettings[$staff_setting] . ' ' . _AM_XHELP_TEXT_AND . ' ' . _AM_XHELP_TEXT_SUBMITTER;
                }
            } elseif (\XHELP_NOTIF_STAFF_NONE == $staff_setting) {
                $sSettings = '';
            } else {
                $sSettings = $aStaffSettings[$staff_setting] ?? '';
            }
            // End Build text of who gets notification

            echo "<tr class='even'>
                     <td width='20%'>" . $template['name'] . '</td>
                     <td>' . $sSettings . "</td>
                     <td>
                         <a href='notifications.php?op=edit&amp;id=" . $template_id . "'><img src='" . XOOPS_URL . "/modules/xhelp/assets/images/button_edit.png' title='" . _AM_XHELP_TEXT_EDIT . "' name='editNotification'></a>
                     </td>
                  </tr>";
        }
    } else {
        // No notifications found (Should never happen)
        echo "<tr><td class='even' colspan='3'>" . _AM_XHELP_TEXT_NO_RECORDS . '</td></tr>';
    }
    echo '</table>';

    xoops_cp_footer();
}

function modifyEmailTpl()
{
    global $xoopsConfig, $session;
    $helper = Helper::getInstance();

    if (is_dir(XOOPS_ROOT_PATH . '/modules/xhelp/language/' . $xoopsConfig['language'] . '/mail_template')) {
        $opendir = opendir(XOOPS_ROOT_PATH . '/modules/xhelp/language/' . $xoopsConfig['language'] . '/mail_template/');
        $dir     = XOOPS_ROOT_PATH . '/modules/xhelp/language/' . $xoopsConfig['language'] . '/mail_template/';
        $url     = XOOPS_URL . '/modules/xhelp/language/' . $xoopsConfig['language'] . '/mail_template/';
    } else {
        $opendir = opendir(XOOPS_ROOT_PATH . '/modules/xhelp/language/english/mail_template/');
        $dir     = XOOPS_ROOT_PATH . '/modules/xhelp/language/english/mail_template/';
        $url     = XOOPS_URL . '/modules/xhelp/language/english/mail_template/';
    }

    $notNames = [
        _MI_XHELP_DEPT_NEWTICKET_NOTIFYTPL          => [
            _MI_XHELP_DEPT_NEWTICKET_NOTIFY,
            _MI_XHELP_DEPT_NEWTICKET_NOTIFYDSC,
            _MI_XHELP_DEPT_NEWTICKET_NOTIFYTPL,
        ],
        _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYTPL      => [
            _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFY,
            _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYDSC,
            _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYTPL,
        ],
        _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYTPL        => [
            _MI_XHELP_DEPT_NEWRESPONSE_NOTIFY,
            _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYDSC,
            _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYTPL,
        ],
        _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYTPL   => [
            _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFY,
            _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYDSC,
            _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYTPL,
        ],
        _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYTPL     => [
            _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFY,
            _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYDSC,
            _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYTPL,
        ],
        _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYTPL      => [
            _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFY,
            _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYDSC,
            _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYTPL,
        ],
        _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYTPL    => [
            _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFY,
            _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYDSC,
            _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYTPL,
        ],
        _MI_XHELP_DEPT_NEWOWNER_NOTIFYTPL           => [
            _MI_XHELP_DEPT_NEWOWNER_NOTIFY,
            _MI_XHELP_DEPT_NEWOWNER_NOTIFYDSC,
            _MI_XHELP_DEPT_NEWOWNER_NOTIFYTPL,
        ],
        _MI_XHELP_DEPT_CLOSETICKET_NOTIFYTPL        => [
            _MI_XHELP_DEPT_CLOSETICKET_NOTIFY,
            _MI_XHELP_DEPT_CLOSETICKET_NOTIFYDSC,
            _MI_XHELP_DEPT_CLOSETICKET_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_NEWOWNER_NOTIFYTPL         => [
            _MI_XHELP_TICKET_NEWOWNER_NOTIFY,
            _MI_XHELP_TICKET_NEWOWNER_NOTIFYDSC,
            _MI_XHELP_TICKET_NEWOWNER_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYTPL    => [
            _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFY,
            _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYDSC,
            _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYTPL   => [
            _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFY,
            _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYDSC,
            _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYTPL      => [
            _MI_XHELP_TICKET_NEWRESPONSE_NOTIFY,
            _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYDSC,
            _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYTPL => [
            _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFY,
            _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYDSC,
            _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYTPL    => [
            _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFY,
            _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYDSC,
            _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYTPL  => [
            _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFY,
            _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYDSC,
            _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_NEWTICKET_NOTIFYTPL        => [
            _MI_XHELP_TICKET_NEWTICKET_NOTIFY,
            _MI_XHELP_TICKET_NEWTICKET_NOTIFYDSC,
            _MI_XHELP_TICKET_NEWTICKET_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYTPL  => [
            _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFY,
            _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYDSC,
            _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_CLOSETICKET_NOTIFYTPL      => [
            _MI_XHELP_TICKET_CLOSETICKET_NOTIFY,
            _MI_XHELP_TICKET_CLOSETICKET_NOTIFYDSC,
            _MI_XHELP_TICKET_CLOSETICKET_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_NEWUSER_NOTIFYTPL          => [
            _MI_XHELP_TICKET_NEWUSER_NOTIFY,
            _MI_XHELP_TICKET_NEWUSER_NOTIFYDSC,
            _MI_XHELP_TICKET_NEWUSER_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYTPL     => [
            _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFY,
            _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYDSC,
            _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYTPL     => [
            _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFY,
            _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYDSC,
            _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYTPL      => [
            _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFY,
            _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYDSC,
            _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYTPL,
        ],
        _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYTPL       => [
            _MI_XHELP_DEPT_MERGE_TICKET_NOTIFY,
            _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYDSC,
            _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYTPL,
        ],
        _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYTPL     => [
            _MI_XHELP_TICKET_MERGE_TICKET_NOTIFY,
            _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYDSC,
            _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYTPL,
        ],
    ];

    $notKeys = array_keys($notNames);

    while (false !== ($file = readdir($opendir))) {
        //Do not Display .
        if (is_dir($file)) {
            continue;
        }

        if (!in_array($file, $notKeys)) {
            continue;
        }

        $aFile             = [];
        $aFile['name']     = $notNames[$file][0];
        $aFile['desc']     = $notNames[$file][1];
        $aFile['filename'] = $notNames[$file][2];
        $aFile['url']      = "notifications.php?op=modifyEmailTpl&amp;file=$file";
        $aFiles[]          = $aFile;
    }

    if (isset($_GET['file'])) {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manNotify');
        $adminObject = Admin::getInstance();
        $adminObject->addItemButton(_AM_XHELP_TEXT_MANAGE_NOTIFICATIONS, 'notifications.php?op=manage', 'add');
        $adminObject->addItemButton(_AM_XHELP_MENU_MODIFY_EMLTPL, 'notifications.php?op=modifyEmailTpl', 'list');

        $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->displayButton('left');

        foreach ($aFiles as $file) {
            if (\Xmf\Request::getString('file', '', 'GET') == $file['filename']) {
                $myFileName = $file['filename'];
                $myFileDesc = $file['desc'];
                $myName     = $file['name'];
                break;
            }
        }
        if (!$has_write = is_writable($dir . $myFileName)) {
            $message  = _AM_XHELP_MESSAGE_FILE_READONLY;
            $handle   = fopen($dir . $myFileName, 'rb');
            $fileSize = filesize($dir . $myFileName);
        } elseif (Request::hasVar('editTemplate', 'POST')) {
            $handle = fopen($dir . $myFileName, 'wb+');
        } else {
            $handle   = fopen($dir . $myFileName, 'rb+');
            $fileSize = filesize($dir . $myFileName);
        }

        if (Request::hasVar('editTemplate', 'POST')) {
            if (Request::hasVar('templateText', 'POST')) {
                $text = $_POST['templateText'];    // Get new text for template
            } else {
                $text = '';
            }

            if (!$returnPage = $session->get('xhelp_return_page')) {
                $returnPage = false;
            }

            if (fwrite($handle, $text)) {
                $message  = _AM_XHELP_MESSAGE_FILE_UPDATED;
                $fileSize = filesize($dir . $myFileName);
                fclose($handle);
                if ($returnPage) {
                    $helper->redirect((string)$returnPage);
                } else {
                    $helper->redirect('admin/notifications.php');
                }
            } else {
                $message  = _AM_XHELP_MESSAGE_FILE_UPDATED_ERROR;
                $fileSize = filesize($dir . $myFileName);
                fclose($handle);
                if ($returnPage) {
                    $helper->redirect((string)$returnPage, 3, $message);
                } else {
                    $helper->redirect('admin/notifications.php', 3, $message);
                }
            }
        }
        if (!$has_write) {
            echo "<div id='readOnly' class='errorMsg'>";
            echo $message;
            echo '</div>';
        }

        echo "<form action='" . XHELP_ADMIN_URL . '/notifications.php?op=modifyEmailTpl&amp;file=' . $myFileName . "' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<table width='100%' border='0' cellspacing='1' class='outer'>
              <tr><th colspan='2'>" . $myName . "</th></tr>
              <tr><td colspan='2' class='head'>" . $myFileDesc . '</td></tr>';

        echo "<tr class='odd'>
                  <td><textarea name='templateText' cols='40' rows='40'>" . fread($handle, $fileSize) . "</textarea></td>
                  <td valign='top'>
                      <b>" . _AM_XHELP_TEXT_GENERAL_TAGS . '</b>
                      <ul>
                        <li>' . _AM_XHELP_TEXT_GENERAL_TAGS1 . '</li>
                        <li>' . _AM_XHELP_TEXT_GENERAL_TAGS2 . '</li>
                        <li>' . _AM_XHELP_TEXT_GENERAL_TAGS3 . '</li>
                        <li>' . _AM_XHELP_TEXT_GENERAL_TAGS4 . '</li>
                        <li>' . _AM_XHELP_TEXT_GENERAL_TAGS5 . '</li>
                      </ul>
                      <br>
                      <u>' . _AM_XHELP_TEXT_TAGS_NO_MODIFY . '</u>
                  </td>
              </tr>';

        if ($has_write) {
            echo "<tr><td class='foot' colspan='2'><input type='submit' name='editTemplate' value='" . _AM_XHELP_BUTTON_UPDATE . "' class='formButton'></td></tr>";
        }
        echo '</table></form>';
    } else {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manNotify');
        $adminObject = Admin::getInstance();
        $adminObject->addItemButton(_AM_XHELP_TEXT_MANAGE_NOTIFICATIONS, 'notifications.php?op=manage', 'add');
        $adminObject->addItemButton(_AM_XHELP_MENU_MODIFY_EMLTPL, 'notifications.php?op=modifyEmailTpl', 'list');

        $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->displayButton('left');

        echo "<table width='100%' border='0' cellspacing='1' class='outer'>
              <tr><th colspan='2'><label>" . _AM_XHELP_MENU_MODIFY_EMLTPL . "</label></th></tr>
              <tr class='head'><td>" . _AM_XHELP_TEXT_TEMPLATE_NAME . '</td><td>' . _AM_XHELP_TEXT_DESCRIPTION . '</td></tr>';

        static $rowSwitch = 0;
        foreach ($aFiles as $file) {
            if (0 == $rowSwitch) {
                echo "<tr class='odd'><td><a href='" . $file['url'] . "'>" . $file['name'] . '</a></td><td>' . $file['desc'] . '</td></tr>';
                $rowSwitch = 1;
            } else {
                echo "<tr class='even'><td><a href='" . $file['url'] . "'>" . $file['name'] . '</a></td><td>' . $file['desc'] . '</td></tr>';
                $rowSwitch = 0;
            }
        }
        echo '</table>';
    }
    require_once __DIR__ . '/admin_footer.php';
}
