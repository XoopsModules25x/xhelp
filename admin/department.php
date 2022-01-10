<?php declare(strict_types=1);

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Constants;

require_once __DIR__ . '/admin_header.php';
xoops_load('XoopsPagenav');
// require_once XHELP_CLASS_PATH . '/Form.php';
// require_once XHELP_CLASS_PATH . '/FormRadio.php';
// require_once XHELP_CLASS_PATH . '/FormCheckbox.php';

$helper = Xhelp\Helper::getInstance();
global $xoopsModule;
$module_id = $xoopsModule->getVar('mid');

$limit = Request::getInt('limit', 15, 'REQUEST');
$start = Request::getInt('start', 0, 'REQUEST');

if (Request::hasVar('order', 'REQUEST')) {
    $order = $_REQUEST['order'];
} else {
    $order = 'ASC';
}
if (Request::hasVar('sort', 'REQUEST')) {
    $sort = $_REQUEST['sort'];
} else {
    $sort = 'department';
}
$dept_search = false;
if (Request::hasVar('dept_search', 'REQUEST')) {
    $dept_search = $_REQUEST['dept_search'];
}

$aSortBy  = ['id' => _AM_XHELP_TEXT_ID, 'department' => _AM_XHELP_TEXT_DEPARTMENT];
$aOrderBy = ['ASC' => _AM_XHELP_TEXT_ASCENDING, 'DESC' => _AM_XHELP_TEXT_DESCENDING];
$aLimitBy = ['10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100];

$op = 'default';

if (Request::hasVar('op', 'REQUEST')) {
    $op = $_REQUEST['op'];
}

switch ($op) {
    case 'activateMailbox':
        activateMailbox();
        break;
    case 'AddDepartmentServer':
        addDepartmentServer();
        break;
    case 'DeleteDepartmentServer':
        deleteDepartmentServer();
        break;
    case 'deleteStaffDept':
        deleteStaffDept();
        break;
    case 'editDepartment':
        editDepartment();
        break;
    case 'EditDepartmentServer':
        editDepartmentServer();
        break;
    case 'manageDepartments':
        manageDepartments();
        break;
    case 'testMailbox':
        testMailbox();
        break;
    case 'clearAddSession':
        clearAddSession();
        break;
    case 'clearEditSession':
        clearEditSession();
        break;
    case 'updateDefault':
        updateDefault();
        break;
    default:
        $helper->redirect('admin/index.php');
        break;
}

/**
 *
 */
function activateMailbox()
{
    $helper   = Xhelp\Helper::getInstance();
    $id       = Request::getInt('id', 0, 'GET');
    $setstate = Request::getInt('setstate', 0, 'GET');

    /** @var \XoopsModules\Xhelp\DepartmentMailBoxHandler $departmentMailBoxHandler */
    $departmentMailBoxHandler = $helper->getHandler('DepartmentMailBox');
    $mailbox                  = $departmentMailBoxHandler->get($id);
    if ($mailbox) {
        $url = XHELP_BASE_URL . '/admin/department.php?op=editDepartment&id=' . $mailbox->getVar('departmentid');
        $mailbox->setVar('active', $setstate);
        if ($departmentMailBoxHandler->insert($mailbox, true)) {
            $helper->redirect($url);
        } else {
            redirect_header($url, 3, _AM_XHELP_DEPARTMENT_SERVER_ERROR);
        }
    } else {
        $helper->redirect('admin/department.php?op=manageDepartments', 3, _XHELP_NO_MAILBOX_ERROR);
    }
}

/**
 *
 */
function addDepartmentServer()
{
    $helper = Xhelp\Helper::getInstance();
    $deptID = 0;

    if (Request::hasVar('id', 'GET')) {
        $deptID = Request::getInt('id', 0, 'GET');
    } else {
        $helper->redirect('admin/department.php?op=manageDepartments', 3, _AM_XHELP_DEPARTMENT_NO_ID);
    }

    /** @var \XoopsModules\Xhelp\DepartmentMailBoxHandler $departmentMailBoxHandler */
    $departmentMailBoxHandler = $helper->getHandler('DepartmentMailBox');
    /** @var \XoopsModules\Xhelp\DepartmentMailBox $server */
    $server = $departmentMailBoxHandler->create();
    $server->setVar('departmentid', $deptID);
    $server->setVar('emailaddress', \Xmf\Request::getString('emailaddress', '', 'POST'));
    $server->setVar('server', \Xmf\Request::getString('server', '', 'POST'));
    $server->setVar('serverport', \Xmf\Request::getString('port', '', 'POST'));
    $server->setVar('username', \Xmf\Request::getString('username', '', 'POST'));
    $server->setVar('password', \Xmf\Request::getString('password', '', 'POST'));
    $server->setVar('priority', $_POST['priority']);

    if ($departmentMailBoxHandler->insert($server)) {
        $helper->redirect('admin/department.php?op=manageDepartments');
    } else {
        $helper->redirect('admin/department.php?op=manageDepartments', 3, _AM_XHELP_DEPARTMENT_SERVER_ERROR);
    }
}

/**
 *
 */
function deleteDepartmentServer()
{
    $helper = Xhelp\Helper::getInstance();
    if (Request::hasVar('id', 'REQUEST')) {
        $emailID = Request::getInt('id', 0, 'REQUEST');
    } else {
        $helper->redirect('admin/department.php?op=manageDepartments', 3, _AM_XHELP_DEPARTMENT_SERVER_NO_ID);
    }
    /** @var \XoopsModules\Xhelp\DepartmentMailBoxHandler $departmentMailBoxHandler */
    $departmentMailBoxHandler = $helper->getHandler('DepartmentMailBox');
    $server                   = $departmentMailBoxHandler->get($emailID);

    if (!isset($_POST['ok'])) {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manDept');
        $adminObject = Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));

        xoops_confirm(['op' => 'DeleteDepartmentServer', 'id' => $emailID, 'ok' => 1], XHELP_BASE_URL . '/admin/department.php', sprintf(_AM_XHELP_MSG_DEPT_MBOX_DEL_CFRM, $server->getVar('emailaddress')));
        xoops_cp_footer();
    } elseif ($departmentMailBoxHandler->delete($server, true)) {
        $helper->redirect('admin/department.php?op=manageDepartments');
    } else {
        $helper->redirect('admin/department.php?op=manageDepartments', 3, _AM_XHELP_DEPARTMENT_SERVER_DELETE_ERROR);
    }
}

/**
 *
 */
function deleteStaffDept()
{
    $deptID = 0;
    $helper = Xhelp\Helper::getInstance();
    if (Request::hasVar('deptid', 'GET')) {
        $deptID = Request::getInt('deptid', 0, 'GET');
    } else {
        $helper->redirect('admin/department.php?op=manageDepartments', 3, _AM_XHELP_MSG_NO_DEPTID);
    }
    if (Request::hasVar('uid', 'GET')) {
        $staffID = Request::getInt('uid', 0, 'GET');
    } elseif (Request::hasVar('staff', 'POST')) {
        $staffID = $_POST['staff'];
    } else {
        $helper->redirect("department.php?op=editDepartment&deptid=$deptID", 3, _AM_XHELP_MSG_NO_UID);
    }

    /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
    $membershipHandler = $helper->getHandler('Membership');
    if (is_array($staffID)) {
        foreach ($staffID as $sid) {
            $ret = $membershipHandler->removeDeptFromStaff($deptID, $sid);
        }
    } else {
        $ret = $membershipHandler->removeDeptFromStaff($deptID, $staffID);
    }

    if ($ret) {
        $helper->redirect("department.php?op=editDepartment&deptid=$deptID");
    } else {
        $helper->redirect("department.php??op=editDepartment&deptid=$deptID", 3, _AM_XHELP_MSG_REMOVE_STAFF_DEPT_ERR);
    }
}

/**
 *
 */
function editDepartment()
{
    $deptID  = 0;
    $session = Xhelp\Session::getInstance();
    global $imagearray, $xoopsModule, $limit, $start;
    $helper = Xhelp\Helper::getInstance();
    $errors = [];

    $module_id   = $xoopsModule->getVar('mid');
    $displayName = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

    $session->set('xhelp_return_page', mb_substr(mb_strstr($_SERVER['REQUEST_URI'], 'admin/'), 6));

    if (Request::hasVar('deptid', 'REQUEST')) {
        $deptID = Request::getInt('deptid', 0);
    } else {
        $helper->redirect('admin/department.php?op=manageDepartments', 3, _AM_XHELP_MSG_NO_DEPTID);
    }

    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    /** @var \XoopsGroupHandler $groupHandler */
    $groupHandler = xoops_getHandler('group');
    /** @var \XoopsGroupPermHandler $grouppermHandler */
    $grouppermHandler = xoops_getHandler('groupperm');

    if (Request::hasVar('updateDept', 'POST')) {
        $groups = ($_POST['groups'] ?? []);

        $hasErrors = false;
        //Department Name supplied?
        if ('' === trim(\Xmf\Request::getString('newDept', '', 'POST'))) {
            $hasErrors           = true;
            $errors['newDept'][] = _AM_XHELP_MESSAGE_NO_DEPT;
        } else {
            //Department Name unique?
            $criteria = new \CriteriaCompo(new \Criteria('department', \Xmf\Request::getString('newDept', '', 'POST')));
            $criteria->add(new \Criteria('id', (string)$deptID, '!='));
            $existingDepts = $departmentHandler->getCount($criteria);
            if ($existingDepts) {
                $hasErrors           = true;
                $errors['newDept'][] = _XHELP_MESSAGE_DEPT_EXISTS;
            }
        }

        if ($hasErrors) {
            $session = Xhelp\Session::getInstance();
            //Store existing dept info in session, reload addition page
            $aDept            = [];
            $aDept['newDept'] = \Xmf\Request::getString('newDept', '', 'POST');
            $aDept['groups']  = $groups;
            $session->set("xhelp_editDepartment_$deptID", $aDept);
            $session->set("xhelp_editDepartmentErrors_$deptID", $errors);
            redirect_header(Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/department.php', ['op' => 'editDepartment', 'deptid' => $deptID], false));
        }

        $dept = $departmentHandler->get($deptID);

        $oldDept = $dept;
        $groups  = $_POST['groups'];

        // Need to remove old group permissions first
        $criteria = new \CriteriaCompo(new \Criteria('gperm_modid', $module_id));
        $criteria->add(new \Criteria('gperm_itemid', (string)$deptID));
        $criteria->add(new \Criteria('gperm_name', _XHELP_GROUP_PERM_DEPT));
        $grouppermHandler->deleteAll($criteria);

        foreach ($groups as $group) {     // Add new group permissions
            $grouppermHandler->addRight(_XHELP_GROUP_PERM_DEPT, $deptID, $group, $module_id);
        }

        $dept->setVar('department', \Xmf\Request::getString('newDept', '', 'POST'));

        if ($departmentHandler->insert($dept)) {
            $message = _XHELP_MESSAGE_UPDATE_DEPT;

            // Update default dept
            if (Request::hasVar('defaultDept', 'POST') && (1 == $_POST['defaultDept'])) {
                Xhelp\Utility::setMeta('default_department', $dept->getVar('id'));
            } else {
                $depts  = $departmentHandler->getObjects();
                $aDepts = [];
                foreach ($depts as $dpt) {
                    $aDepts[] = $dpt->getVar('id');
                }
                Xhelp\Utility::setMeta('default_department', $aDepts[0]);
            }

            // Edit configoption for department
            /** @var \XoopsModules\Xhelp\ConfigOptionHandler $configOptionHandler */
            $configOptionHandler = $helper->getHandler('ConfigOption');
            $criteria            = new \CriteriaCompo(new \Criteria('confop_name', $oldDept->getVar('department')));
            $criteria->add(new \Criteria('confop_value', $oldDept->getVar('id')));
            $confOption = $configOptionHandler->getObjects($criteria);

            if (count($confOption) > 0) {
                $confOption[0]->setVar('confop_name', $dept->getVar('department'));

                if (!$configOptionHandler->insert($confOption[0])) {
                    $helper->redirect('admin/department.php?op=manageDepartments', 3, _AM_XHELP_MSG_UPDATE_CONFIG_ERR);
                }
            }
            clearEditSessionVars($deptID);
            $helper->redirect('admin/department.php?op=manageDepartments');
        } else {
            $message = _XHELP_MESSAGE_UPDATE_DEPT_ERROR . $dept->getHtmlErrors();
            $helper->redirect('admin/department.php?op=manageDepartments', 3, $message);
        }
    } else {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manDept');

        $dept = $departmentHandler->get($deptID);

        $session     = Xhelp\Session::getInstance();
        $sess_dept   = $session->get("xhelp_editDepartment_$deptID");
        $sess_errors = $session->get("xhelp_editDepartmentErrors_$deptID");

        //Display any form errors
        if (false === !$sess_errors) {
            xhelpRenderErrors($sess_errors, Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/department.php', ['op' => 'clearEditSession', 'deptid' => $deptID]));
        }

        $adminObject = Admin::getInstance();
        $adminObject->displayNavigation('department.php?op=editDepartment');

        // Get list of groups with permission
        $criteria = new \CriteriaCompo(new \Criteria('gperm_modid', $module_id));
        $criteria->add(new \Criteria('gperm_itemid', (string)$deptID));
        $criteria->add(new \Criteria('gperm_name', _XHELP_GROUP_PERM_DEPT));
        $group_perms = $grouppermHandler->getObjects($criteria);

        $aPerms = [];      // Put group_perms in usable format
        foreach ($group_perms as $perm) {
            $aPerms[$perm->getVar('gperm_groupid')] = $perm->getVar('gperm_groupid');
        }

        if (false !== !$sess_dept) {
            $fld_newDept = $dept->getVar('department');
            $fld_groups  = $aPerms;
        } else {
            $fld_newDept = $sess_dept['newDept'];
            $fld_groups  = $sess_dept['groups'];
        }

        // Get list of all groups
        $criteria = new \Criteria('', '');
        $criteria->setSort('name');
        $criteria->setOrder('ASC');
        $groups = $groupHandler->getObjects($criteria, true);

        $aGroups = [];
        foreach ($groups as $group_id => $group) {
            $aGroups[$group_id] = $group->getVar('name');
        }
        asort($aGroups);    // Set groups in alphabetical order

        echo '<script type="text/javascript" src="' . XOOPS_URL . '/modules/xhelp/include/functions.js"></script>';
        $form         = new Xhelp\Form(
            _AM_XHELP_EDIT_DEPARTMENT, 'edit_dept', Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/department.php', [
            'op'     => 'editDepartment',
            'deptid' => $deptID,
        ])
        );
        $dept_name    = new \XoopsFormText(_AM_XHELP_TEXT_EDIT_DEPT, 'newDept', 20, 35, $fld_newDept);
        $group_select = new \XoopsFormSelect(_AM_XHELP_TEXT_EDIT_DEPT_PERMS, 'groups', $fld_groups, 6, true);
        $group_select->addOptionArray($aGroups);
        $defaultDeptID = Xhelp\Utility::getMeta('default_department');
        $defaultDept   = new Xhelp\FormCheckbox(_AM_XHELP_TEXT_DEFAULT_DEPT, 'defaultDept', (($defaultDeptID == $deptID) ? 1 : 0), 'defaultDept');
        $defaultDept->addOption('1', '');
        $btn_tray = new \XoopsFormElementTray('');
        $btn_tray->addElement(new \XoopsFormButton('', 'updateDept', _SUBMIT, 'submit'));
        $form->addElement($dept_name);
        $form->addElement($group_select);
        $form->addElement($defaultDept);
        $form->addElement($btn_tray);
        $form->setLabelWidth('20%');
        echo $form->render();

        // Get dept staff members
        /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
        $membershipHandler = $helper->getHandler('Membership');
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = xoops_getHandler('member');
        /** @var \XoopsModules\Xhelp\StaffRoleHandler $staffRoleHandler */
        $staffRoleHandler = $helper->getHandler('StaffRole');
        /** @var \XoopsModules\Xhelp\RoleHandler $roleHandler */
        $roleHandler = $helper->getHandler('Role');

        $staff      = $membershipHandler->membershipByDept($deptID, $limit, $start);
        $criteria   = new \Criteria('j.department', (string)$deptID);
        $staffCount = $membershipHandler->getCount($criteria);
        $roles      = $roleHandler->getObjects(null, true);

        echo "<form action='" . XHELP_ADMIN_URL . '/department.php?op=deleteStaffDept&amp;deptid=' . $deptID . "' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<table width='100%' cellspacing='1' class='outer'>
              <tr><th colspan='" . (3 + count($roles)) . "'><label>" . _AM_XHELP_MANAGE_STAFF . '</label></th></tr>';

        if ($staffCount > 0) {
            $aStaff = [];
            foreach ($staff as $stf) {
                $aStaff[$stf->getVar('uid')] = $stf->getVar('uid');     // Get array of staff uid
            }

            // Get user list
            $criteria = new \Criteria('uid', '(' . implode(',', $aStaff) . ')', 'IN');
            //$members = $memberHandler->getUserList($criteria);
            $members = Xhelp\Utility::getUsers($criteria, $displayName);

            // Get staff roles
            $criteria = new \CriteriaCompo(new \Criteria('uid', '(' . implode(',', $aStaff) . ')', 'IN'));
            $criteria->add(new \Criteria('deptid', (string)$deptID));
            $staffRoles = $staffRoleHandler->getObjects($criteria);
            unset($aStaff);

            $staffInfo = [];
            foreach ($staff as $stf) {
                $staff_uid                      = $stf->getVar('uid');
                $staffInfo[$staff_uid]['uname'] = $members[$staff_uid];
                $aRoles                         = [];
                foreach ($staffRoles as $role) {
                    $role_id = $role->getVar('roleid');
                    if ($role->getVar('uid') == $staff_uid) {
                        $aRoles[$role_id] = $roles[$role_id]->getVar('name');
                    }
                    $staffInfo[$staff_uid]['roles'] = implode(', ', $aRoles);
                }
            }
            $nav = new \XoopsPageNav($staffCount, $limit, $start, 'start', "op=editDepartment&amp;deptid=$deptID&amp;limit=$limit");

            echo "<tr class='head'><td rowspan='2'>" . _AM_XHELP_TEXT_ID . "</td><td rowspan='2'>" . _AM_XHELP_TEXT_USER . "</td><td colspan='" . count($roles) . "'>" . _AM_XHELP_TEXT_ROLES . "</td><td rowspan='2'>" . _AM_XHELP_TEXT_ACTIONS . '</td></tr>';
            echo "<tr class='head'>";
            foreach ($roles as $thisrole) {
                echo '<td>' . $thisrole->getVar('name') . '</td>';
            }
            echo '</tr>';
            foreach ($staffInfo as $uid => $staff) {
                echo "<tr class='even'>
                          <td><input type='checkbox' name='staff[]' value='" . $uid . "'>" . $uid . '</td>
                          <td>' . $staff['uname'] . '</td>';
                foreach ($roles as $thisrole) {
                    echo "<td><img src='" . XHELP_BASE_URL . '/assets/images/';
                    echo in_array($thisrole->getVar('name'), explode(', ', $staff['roles'])) ? 'on.png' : 'off.png';
                    echo "'></td>";
                }
                echo "    <td>
                          <a href='" . XHELP_ADMIN_URL . '/staff.php?op=editStaff&amp;uid=' . $uid . "'><img src='" . XOOPS_URL . "/modules/xhelp/assets/images/button_edit.png' title='" . _AM_XHELP_TEXT_EDIT . "' name='editStaff'></a>&nbsp;
                          <a href='" . XHELP_ADMIN_URL . '/department.php?op=deleteStaffDept&amp;uid=' . $uid . '&amp;deptid=' . $deptID . "'><img src='" . XOOPS_URL . "/modules/xhelp/assets/images/button_delete.png' title='" . _AM_XHELP_TEXT_DELETE_STAFF_DEPT . "' name='deleteStaffDept'></a>
                      </td>
                  </tr>";
            }
            echo "<tr>
                      <td class='foot' colspan='" . (3 + count($roles)) . "'>
                          <input type='checkbox' name='checkallRoles' value='0' onclick='selectAll(this.form,\"staff[]\",this.checked);'>
                          <input type='submit' name='deleteStaff' id='deleteStaff' value='" . _AM_XHELP_BUTTON_DELETE . "'>
                      </td>
                  </tr>";
            echo '</table></form>';
            echo "<div id='staff_nav'>" . $nav->renderNav() . '</div>';
        } else {
            echo '</table></form>';
        }

        //now do the list of servers
        /** @var \XoopsModules\Xhelp\DepartmentMailBoxHandler $departmentMailBoxHandler */
        $departmentMailBoxHandler = $helper->getHandler('DepartmentMailBox');
        $deptServers              = $departmentMailBoxHandler->getByDepartment($deptID);
        //iterate
        if (count($deptServers) > 0) {
            echo "<br><table width='100%' cellspacing='1' class='outer'>
               <tr>
                 <th colspan='5'><label>" . _AM_XHELP_DEPARTMENT_SERVERS . "</label></th>
               </tr>
               <tr>
                 <td class='head' width='20%'><label>" . _AM_XHELP_DEPARTMENT_SERVERS_EMAIL . "</label></td>
                 <td class='head'><label>" . _AM_XHELP_DEPARTMENT_SERVERS_TYPE . "</label></td>
                 <td class='head'><label>" . _AM_XHELP_DEPARTMENT_SERVERS_SERVERNAME . "</label></td>
                 <td class='head'><label>" . _AM_XHELP_DEPARTMENT_SERVERS_PORT . "</label></td>
                 <td class='head'><label>" . _AM_XHELP_DEPARTMENT_SERVERS_ACTION . '</label></td>
               </tr>';
            $i = 0;
            foreach ($deptServers as $server) {
                if ($server->getVar('active')) {
                    $activ_link  = '".XHELP_ADMIN_URL."/department.php?op=activateMailbox&amp;setstate=0&amp;id=' . $server->getVar('id');
                    $activ_img   = $imagearray['online'];
                    $activ_title = _AM_XHELP_MESSAGE_DEACTIVATE;
                } else {
                    $activ_link  = '".XHELP_ADMIN_URL."/department.php?op=activateMailbox&amp;setstate=1&amp;id=' . $server->getVar('id');
                    $activ_img   = $imagearray['offline'];
                    $activ_title = _AM_XHELP_MESSAGE_ACTIVATE;
                }

                echo '<tr class="even">
                   <td>' . $server->getVar('emailaddress') . '</td>
                   <td>' . Xhelp\Utility::getMBoxType($server->getVar('mboxtype')) . '</td>
                   <td>' . $server->getVar('server') . '</td>
                   <td>' . $server->getVar('serverport') . '</td>
                   <td> <a href="' . $activ_link . '" title="' . $activ_title . '">' . $activ_img . '</a>
                        <a href="' . XHELP_ADMIN_URL . '/department.php?op=EditDepartmentServer&amp;id=' . $server->GetVar('id') . '">' . $imagearray['editimg'] . '</a>
                        <a href="' . XHELP_ADMIN_URL . '/department.php?op=DeleteDepartmentServer&amp;id=' . $server->GetVar('id') . '">' . $imagearray['deleteimg'] . '</a>

                   </td>
                 </tr>';
            }
            echo '</table>';
        }
        //finally add Mailbox form
        echo '<br><br>';

        $formElements = [
            'type_select',
            'server_text',
            'port_text',
            'username_text',
            'pass_text',
            'priority_radio',
            'email_text',
            'btn_tray',
        ];
        $form         = new Xhelp\Form(_AM_XHELP_DEPARTMENT_ADD_SERVER, 'add_server', Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/department.php', ['op' => 'AddDepartmentServer', 'id' => $deptID]));

        $type_select = new \XoopsFormSelect(_AM_XHELP_DEPARTMENT_SERVERS_TYPE, 'mboxtype');
        $type_select->setExtra("id='mboxtype'");
        $type_select->addOption((string)Constants::XHELP_MAILBOXTYPE_POP3, _AM_XHELP_MBOX_POP3);

        $server_text = new \XoopsFormText(_AM_XHELP_DEPARTMENT_SERVERS_SERVERNAME, 'server', 40, 50);
        $server_text->setExtra("id='txtServer'");

        $port_text = new \XoopsFormText(_AM_XHELP_DEPARTMENT_SERVERS_PORT, 'port', 5, 5, '110');
        $port_text->setExtra("id='txtPort'");

        $username_text = new \XoopsFormText(_AM_XHELP_DEPARTMENT_SERVER_USERNAME, 'username', 25, 50);
        $username_text->setExtra("id='txtUsername'");

        $pass_text = new \XoopsFormText(_AM_XHELP_DEPARTMENT_SERVER_PASSWORD, 'password', 25, 50);
        $pass_text->setExtra("id='txtPassword'");

        $priority_radio = new Xhelp\FormRadio(_AM_XHELP_DEPARTMENT_SERVERS_PRIORITY, 'priority', (string)XHELP_DEFAULT_PRIORITY);
        $priority_array = [
            1 => "<label for='priority1'><img src='" . XHELP_IMAGE_URL . "/priority1.png' title='" . Xhelp\Utility::getPriority(1) . "' alt='priority1'></label>",
            2 => "<label for='priority2'><img src='" . XHELP_IMAGE_URL . "/priority2.png' title='" . Xhelp\Utility::getPriority(2) . "' alt='priority2'></label>",
            3 => "<label for='priority3'><img src='" . XHELP_IMAGE_URL . "/priority3.png' title='" . Xhelp\Utility::getPriority(3) . "' alt='priority3'></label>",
            4 => "<label for='priority4'><img src='" . XHELP_IMAGE_URL . "/priority4.png' title='" . Xhelp\Utility::getPriority(4) . "' alt='priority4'></label>",
            5 => "<label for='priority5'><img src='" . XHELP_IMAGE_URL . "/priority5.png' title='" . Xhelp\Utility::getPriority(5) . "' alt='priority5'></label>",
        ];
        $priority_radio->addOptionArray($priority_array);

        $email_text = new \XoopsFormText(_AM_XHELP_DEPARTMENT_SERVER_EMAILADDRESS, 'emailaddress', 50, 255);
        $email_text->setExtra("id='txtEmailaddress'");

        $btn_tray    = new \XoopsFormElementTray('');
        $test_button = new \XoopsFormButton('', 'email_test', _AM_XHELP_BUTTON_TEST, 'button');
        $test_button->setExtra("id='test'");
        $submit_button  = new \XoopsFormButton('', 'updateDept2', _SUBMIT, 'submit');
        $cancel2_button = new \XoopsFormButton('', 'cancel2', _AM_XHELP_BUTTON_CANCEL, 'button');
        $cancel2_button->setExtra("onclick='history.go(-1)'");
        $btn_tray->addElement($test_button);
        $btn_tray->addElement($submit_button);
        $btn_tray->addElement($cancel2_button);

        $form->setLabelWidth('20%');
        foreach ($formElements as $element) {
            $form->addElement($$element);
        }
        echo $form->render();

        echo '<script type="text/javascript" language="javascript">
          <!--
          function xhelpEmailTest()
          {
            pop = openWithSelfMain("", "email_test", 250, 150);
            frm = xoopsGetElementById("add_server");
            newaction = "department.php?op=testMailbox";
            oldaction = frm.action;
            frm.action = newaction;
            frm.target = "email_test";
            frm.submit();
            frm.action = oldaction;
            frm.target = "main";

          }

          xhelpDOMAddEvent(xoopsGetElementById("email_test"), "click", xhelpEmailTest, false);

          //-->
          </script>';
        require_once __DIR__ . '/admin_footer.php';
    }
}

/**
 *
 */
function editDepartmentServer()
{
    $helper = Xhelp\Helper::getInstance();
    if (Request::hasVar('id', 'GET')) {
        $id = Request::getInt('id', 0, 'GET');
    } else {
        $helper->redirect('admin/department.php?op=manageDepartments', 3);       // TODO: Make message for no mbox_id
    }

    /** @var \XoopsModules\Xhelp\DepartmentMailBoxHandler $departmentMailBoxHandler */
    $departmentMailBoxHandler = $helper->getHandler('DepartmentMailBox');
    $deptServer               = $departmentMailBoxHandler->get($id);

    if (Request::hasVar('updateMailbox', 'POST')) {
        $deptServer->setVar('emailaddress', \Xmf\Request::getString('emailaddress', '', 'POST'));
        $deptServer->setVar('server', \Xmf\Request::getString('server', '', 'POST'));
        $deptServer->setVar('serverport', \Xmf\Request::getString('port', '', 'POST'));
        $deptServer->setVar('username', \Xmf\Request::getString('username', '', 'POST'));
        $deptServer->setVar('password', \Xmf\Request::getString('password', '', 'POST'));
        $deptServer->setVar('priority', $_POST['priority']);
        $deptServer->setVar('active', $_POST['activity']);

        if ($departmentMailBoxHandler->insert($deptServer)) {
            $helper->redirect('admin/department.php?op=editDepartment&deptid=' . $deptServer->getVar('departmentid'));
        } else {
            $helper->redirect('admin/department.php?op=editDepartment&deptid=' . $deptServer->getVar('departmentid'), 3);
        }
    } else {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manDept');
        $adminObject = Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));
        echo '<script type="text/javascript" src="' . XOOPS_URL . '/modules/xhelp/include/functions.js"></script>';
        echo "<form method='post' id='edit_server' action='department.php?op=EditDepartmentServer&amp;id=" . $id . "'>
               <table width='100%' cellspacing='1' class='outer'>
                 <tr>
                   <th colspan='2'><label>" . _AM_XHELP_DEPARTMENT_EDIT_SERVER . "</label></th>
                 </tr>
                 <tr>
                   <td class='head' width='20%'><label for='mboxtype'>" . _AM_XHELP_DEPARTMENT_SERVERS_TYPE . "</label></td>
                   <td class='even'>
                     <select name='mboxtype' id='mboxtype' onchange='xhelpPortOnChange(this.options[this.selectedIndex].text, \"txtPort\")'>
                       <option value='" . Constants::XHELP_MAILBOXTYPE_POP3 . "'>" . _AM_XHELP_MBOX_POP3 . "</option>
                       <!--<option value='" . _XHELP_MAILBOXTYPE_IMAP . "'>" . _AM_XHELP_MBOX_IMAP . "</option>-->
                     </select>
                   </td>
                 </tr>
                 <tr>
                   <td class='head'><label for='txtServer'>" . _AM_XHELP_DEPARTMENT_SERVERS_SERVERNAME . "</label></td>
                   <td class='even'><input type='text' id='txtServer' name='server' value='" . $deptServer->getVar('server') . "' size='40' maxlength='50'>
                 </tr>
                 <tr>
                   <td class='head'><label for='txtPort'>" . _AM_XHELP_DEPARTMENT_SERVERS_PORT . "</label></td>
                   <td class='even'><input type='text' id='txtPort' name='port' maxlength='5' size='5' value='" . $deptServer->getVar('serverport') . "'>
                 </tr>
                 <tr>
                   <td class='head'><label for='txtUsername'>" . _AM_XHELP_DEPARTMENT_SERVER_USERNAME . "</label></td>
                   <td class='even'><input type='text' id='txtUsername' name='username' value='" . $deptServer->getVar('username') . "' size='25' maxlength='50'>
                 </tr>
                 <tr>
                   <td class='head'><label for='txtPassword'>" . _AM_XHELP_DEPARTMENT_SERVER_PASSWORD . "</label></td>
                   <td class='even'><input type='text' id='txtPassword' name='password' value='" . $deptServer->getVar('password') . "' size='25' maxlength='50'>
                 </tr>
                 <tr>
                   <td width='38%' class='head'><label for='txtPriority'>" . _AM_XHELP_DEPARTMENT_SERVERS_PRIORITY . "</label></td>
                   <td width='62%' class='even'>";
        for ($i = 1; $i < 6; ++$i) {
            $checked = '';
            if ($deptServer->getVar('priority') == $i) {
                $checked = 'checked';
            }
            echo("<input type=\"radio\" value=\"$i\" id=\"priority$i\" name=\"priority\" $checked>");
            echo("<label for=\"priority$i\"><img src=\"../assets/images/priority$i.png\" title=\"" . Xhelp\Utility::getPriority($i) . "\" alt=\"priority$i\"></label>");
        }
        echo "</td>
                 </tr>
                 <tr>
                   <td class='head'><label for='txtEmailaddress'>" . _AM_XHELP_DEPARTMENT_SERVER_EMAILADDRESS . "</label></td>
                   <td class='even'><input type='text' id='txtEmailaddress' name='emailaddress' value='" . $deptServer->getVar('emailaddress') . "' size='50' maxlength='255'>
                 </tr>
                 <tr>
                   <td class='head'><label for='txtActive'>" . _AM_XHELP_TEXT_ACTIVITY . "</label></td>
                   <td class='even'>";
        if (1 == $deptServer->getVar('active')) {
            echo "<input type='radio' value='1' name='activity' checked>" . _AM_XHELP_TEXT_ACTIVE . "
                                      <input type='radio' value='0' name='activity'>" . _AM_XHELP_TEXT_INACTIVE;
        } else {
            echo "<input type='radio' value='1' name='activity'>" . _AM_XHELP_TEXT_ACTIVE . "
                                      <input type='radio' value='0' name='activity' checked>" . _AM_XHELP_TEXT_INACTIVE;
        }

        echo "</td>
                 </tr>

                 <tr class='foot'>
                   <td colspan='2'><div align='right'><span >
                       <input type='button' id='email_test' name='test' value='" . _AM_XHELP_BUTTON_TEST . "' class='formButton'>
                       <input type='submit' name='updateMailbox' value='" . _AM_XHELP_BUTTON_SUBMIT . "' class='formButton'>
                       <input type='button' name='cancel' value='" . _AM_XHELP_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton'>
                   </span></div></td>
                 </tr>
               </table>
             </form>";
        echo '<script type="text/javascript" language="javascript">
          <!--
          function xhelpEmailTest()
          {
            pop = openWithSelfMain("", "email_test", 250, 150);
            frm = xoopsGetElementById("edit_server");
            newaction = "department.php?op=testMailbox";
            oldaction = frm.action;
            frm.action = newaction;
            frm.target = "email_test";
            frm.submit();
            frm.action = oldaction;
            frm.target = "main";

          }

          xhelpDOMAddEvent(xoopsGetElementById("email_test"), "click", xhelpEmailTest, false);

          //-->
          </script>';
        require_once __DIR__ . '/admin_footer.php';
    }
}

/**
 *
 */
function manageDepartments()
{
    global $xoopsModule, $aSortBy, $aOrderBy, $aLimitBy, $order, $limit, $start, $sort, $dept_search;
    $module_id = $xoopsModule->getVar('mid');
    $helper    = Xhelp\Helper::getInstance();
    $deptID    = 0;

    /** @var \XoopsGroupHandler $groupHandler */
    $groupHandler = xoops_getHandler('group');
    /** @var \XoopsGroupPermHandler $grouppermHandler */
    $grouppermHandler = xoops_getHandler('groupperm');

    if (Request::hasVar('addDept', 'POST')) {
        $hasErrors = false;
        $errors    = [];
        $groups    = ($_POST['groups'] ?? []);
        /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
        $departmentHandler = $helper->getHandler('Department');

        //Department Name supplied?
        if ('' === trim(\Xmf\Request::getString('newDept', '', 'POST'))) {
            $hasErrors           = true;
            $errors['newDept'][] = _AM_XHELP_MESSAGE_NO_DEPT;
        } else {
            //Department Name unique?
            $criteria      = new \Criteria('department', \Xmf\Request::getString('newDept', '', 'POST'));
            $existingDepts = $departmentHandler->getCount($criteria);
            if ($existingDepts) {
                $hasErrors           = true;
                $errors['newDept'][] = _XHELP_MESSAGE_DEPT_EXISTS;
            }
        }

        if ($hasErrors) {
            $session = Xhelp\Session::getInstance();
            //Store existing dept info in session, reload addition page
            $aDept            = [];
            $aDept['newDept'] = \Xmf\Request::getString('newDept', '', 'POST');
            $aDept['groups']  = $groups;
            $session->set('xhelp_addDepartment', $aDept);
            $session->set('xhelp_addDepartmentErrors', $errors);
            redirect_header(Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/department.php', ['op' => 'manageDepartments'], false));
        }

        /** @var \XoopsModules\Xhelp\Department $department */
        $department = $departmentHandler->create();
        $department->setVar('department', \Xmf\Request::getString('newDept', '', 'POST'));

        if ($departmentHandler->insert($department)) {
            $deptID = $department->getVar('id');
            foreach ($groups as $group) {     // Add new group permissions
                $grouppermHandler->addRight(_XHELP_GROUP_PERM_DEPT, $deptID, $group, $module_id);
            }

            // Set as default department?
            if (Request::hasVar('defaultDept', 'POST') && (1 == $_POST['defaultDept'])) {
                Xhelp\Utility::setMeta('default_department', $deptID);
            }

            /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
            $staffHandler = $helper->getHandler('Staff');
            $allDeptStaff = $staffHandler->getByAllDepts();
            if (count($allDeptStaff) > 0) {
                /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
                $membershipHandler = $helper->getHandler('Membership');
                if ($membershipHandler->addStaffToDept($allDeptStaff, $department->getVar('id'))) {
                    $message = _XHELP_MESSAGE_ADD_DEPT;
                } else {
                    $message = _AM_XHELP_MESSAGE_STAFF_UPDATE_ERROR;
                }
            } else {
                $message = _XHELP_MESSAGE_ADD_DEPT;
            }

            // Add configoption for new department
            /** @var \XoopsConfigHandler $configHandler */
            $configHandler = xoops_getHandler('config');
            /** @var \XoopsModules\Xhelp\ConfigOptionHandler $configOptionHandler */
            $configOptionHandler = $helper->getHandler('ConfigOption');

            $criteria = new \Criteria('conf_name', 'xhelp_defaultDept');
            $config   = $configHandler->getConfigs($criteria);

            if (count($config) > 0) {
                $newOption = $configOptionHandler->create();
                $newOption->setVar('confop_name', $department->getVar('department'));
                $newOption->setVar('confop_value', $department->getVar('id'));
                $newOption->setVar('conf_id', $config[0]->getVar('conf_id'));

                if (!$configOptionHandler->insert($newOption)) {
                    $helper->redirect('admin/department.php?op=manageDepartments', 3, _AM_XHELP_MSG_ADD_CONFIG_ERR);
                }
            }
            clearAddSessionVars();
            $helper->redirect('admin/department.php?op=manageDepartments');
        } else {
            $message = _XHELP_MESSAGE_ADD_DEPT_ERROR . $department->getHtmlErrors();
        }

        $deptID = $department->getVar('id');

        /* Not sure if this is needed. Already exists in if block above (ej)
         foreach ($groups as $group) {
         $grouppermHandler->addRight(_XHELP_GROUP_PERM_DEPT, $deptID, $group, $module_id);
         }
         */

        $helper->redirect('admin/department.php?op=manageDepartments', 3, $message);
    } else {
        /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
        $departmentHandler = $helper->getHandler('Department');
        if (false !== $dept_search) {
            $criteria = new \Criteria('department', "%$dept_search%", 'LIKE');
        } else {
            $criteria = new \Criteria('', '');
        }
        $criteria->setOrder($order);
        $criteria->setSort($sort);
        $criteria->setLimit($limit);
        $criteria->setStart($start);
        $total          = $departmentHandler->getCount($criteria);
        $departmentInfo = $departmentHandler->getObjects($criteria);

        $nav = new \XoopsPageNav($total, $limit, $start, 'start', "op=manageDepartments&amp;limit=$limit");

        // Get list of all groups
        $criteria = new \Criteria('', '');
        $criteria->setSort('name');
        $criteria->setOrder('ASC');
        $groups = $groupHandler->getObjects($criteria, true);

        $aGroups = [];
        foreach ($groups as $group_id => $group) {
            $aGroups[$group_id] = $group->getVar('name');
        }
        asort($aGroups);    // Set groups in alphabetical order

        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manDept');
        $adminObject = Admin::getInstance();
        $adminObject->displayNavigation('department.php?op=manageDepartments');

        $session     = Xhelp\Session::getInstance();
        $sess_dept   = $session->get('xhelp_addDepartment');
        $sess_errors = $session->get('xhelp_addDepartmentErrors');

        //Display any form errors
        if (false === !$sess_errors) {
            xhelpRenderErrors($sess_errors, Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/department.php', ['op' => 'clearAddSession'], false));
        }

        if (false !== !$sess_dept) {
            $fld_newDept = '';
            $fld_groups  = [];
        } else {
            $fld_newDept = $sess_dept['newDept'];
            $fld_groups  = $sess_dept['groups'];
        }

        echo "<form method='post' action='" . XHELP_ADMIN_URL . "/department.php?op=manageDepartments'>";
        echo "<table width='100%' cellspacing='1' class='outer'>
              <tr><th colspan='2'><label for='newDept'>" . _AM_XHELP_LINK_ADD_DEPT . ' </label></th></tr>';
        echo "<tr><td class='head' width='20%' valign='top'>" . _AM_XHELP_TEXT_NAME . "</td><td class='even'>";
        echo "<input type='text' id='newDept' name='newDept' class='formButton' value='$fld_newDept'></td></tr>";
        echo "<tr><td class='head' width='20%' valign='top'>" . _AM_XHELP_TEXT_EDIT_DEPT_PERMS . "</td><td class='even'>";
        echo "<select name='groups[]' multiple='multiple'>";
        foreach ($aGroups as $group_id => $group) {
            if (in_array($group_id, $fld_groups)) {
                echo "<option value='$group_id' selected>$group</option>";
            } else {
                echo "<option value='$group_id'>$group</option>";
            }
        }
        echo '</select></td></tr>';
        echo "<tr><td class='head' width='20%' valign='top'>" . _AM_XHELP_TEXT_DEFAULT_DEPT . "?</td>
                  <td class='even'><input type='checkbox' name='defaultDept' id='defaultDept' value='1'></td></tr>";
        echo "<tr><td class='foot' colspan='2'><input type='submit' name='addDept' value='" . _AM_XHELP_BUTTON_SUBMIT . "' class='formButton'></td></tr>";
        echo '</table><br>';
        echo '</form>';
        if ($total > 0) {     // Make sure there are departments
            echo "<form action='" . XHELP_ADMIN_URL . "/department.php?op=manageDepartments' style='margin:0; padding:0;' method='post'>";
            echo $GLOBALS['xoopsSecurity']->getTokenHTML();
            echo "<table width='100%' cellspacing='1' class='outer'>";
            echo "<tr><td align='right'>" . _AM_XHELP_BUTTON_SEARCH . "
                          <input type='text' name='dept_search' value='$dept_search'>
                        &nbsp;&nbsp;&nbsp;
                        " . _AM_XHELP_TEXT_SORT_BY . "
                          <select name='sort'>";
            foreach ($aSortBy as $value => $text) {
                ($sort == $value) ? $selected = 'selected' : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            echo '</select>
                        &nbsp;&nbsp;&nbsp;
                          ' . _AM_XHELP_TEXT_ORDER_BY . "
                          <select name='order'>";
            foreach ($aOrderBy as $value => $text) {
                ($order == $value) ? $selected = 'selected' : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            echo '</select>
                          &nbsp;&nbsp;&nbsp;
                          ' . _AM_XHELP_TEXT_NUMBER_PER_PAGE . "
                          <select name='limit'>";
            foreach ($aLimitBy as $value => $text) {
                ($limit == $value) ? $selected = 'selected' : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            echo "</select>
                          <input type='submit' name='dept_sort' id='dept_sort' value='" . _AM_XHELP_BUTTON_SUBMIT . "'>
                      </td>
                  </tr>";
            echo '</table></form>';
            echo "<table width='100%' cellspacing='1' class='outer'>
                  <tr><th colspan='4'>" . _AM_XHELP_EXISTING_DEPARTMENTS . "</th></tr>
                  <tr><td class='head'>" . _AM_XHELP_TEXT_ID . "</td><td class='head'>" . _AM_XHELP_TEXT_DEPARTMENT . "</td><td class='head'>" . _AM_XHELP_TEXT_DEFAULT . "</td><td class='head'>" . _AM_XHELP_TEXT_ACTIONS . '</td></tr>';

            if (null !== $departmentInfo) {
                $defaultDept = Xhelp\Utility::getMeta('default_department');
                foreach ($departmentInfo as $dept) {
                    echo "<tr><td class='even'>" . $dept->getVar('id') . "</td><td class='even'>" . $dept->getVar('department') . '</td>';
                    if ($dept->getVar('id') != $defaultDept) {
                        echo "<td class='even' width='10%'><a href='"
                             . XHELP_ADMIN_URL
                             . '/department.php?op=updateDefault&amp;id='
                             . $dept->getVar('id')
                             . "'><img src='"
                             . XHELP_IMAGE_URL
                             . "/off.png' alt='"
                             . _AM_XHELP_TEXT_MAKE_DEFAULT_DEPT
                             . "' title='"
                             . _AM_XHELP_TEXT_MAKE_DEFAULT_DEPT
                             . "'></a></td>";
                    } else {
                        echo "<td class='even' width='10%'><img src='" . XHELP_IMAGE_URL . "/on.png'</td>";
                    }
                    //echo "<td class='even' width='10%'><img src='".XHELP_IMAGE_URL."/". (($dept->getVar('id') == $defaultDept) ? "on.png" : "off.png")."'</td>";
                    echo "<td class='even' width='70'><a href='"
                         . XHELP_ADMIN_URL
                         . '/department.php?op=editDepartment&amp;deptid='
                         . $dept->getVar('id')
                         . "'><img src='"
                         . XOOPS_URL
                         . "/modules/xhelp/assets/images/button_edit.png' title='"
                         . _AM_XHELP_TEXT_EDIT
                         . "' name='editDepartment'></a>&nbsp;&nbsp;";
                    echo "<a href='" . XHELP_ADMIN_URL . '/delete.php?deleteDept=1&amp;deptid=' . $dept->getVar('id') . "'><img src='" . XOOPS_URL . "/modules/xhelp/assets/images/button_delete.png' title='" . _AM_XHELP_TEXT_DELETE . "' name='deleteDepartment'></a></td></tr>";
                }
            }
        }
        echo '</td></tr></table>';
        echo "<div id='dept_nav'>" . $nav->renderNav() . '</div>';
        require_once __DIR__ . '/admin_footer.php';
    }
}

/**
 *
 */
function testMailbox()
{
    $helper = Xhelp\Helper::getInstance();
    /** @var \XoopsModules\Xhelp\DepartmentMailBoxHandler $departmentMailBoxHandler */
    $departmentMailBoxHandler = $helper->getHandler('DepartmentMailBox');
    $server                   = $departmentMailBoxHandler->create();
    $server->setVar('emailaddress', \Xmf\Request::getString('emailaddress', '', 'POST'));
    $server->setVar('server', \Xmf\Request::getString('server', '', 'POST'));
    $server->setVar('serverport', \Xmf\Request::getString('port', '', 'POST'));
    $server->setVar('username', \Xmf\Request::getString('username', '', 'POST'));
    $server->setVar('password', \Xmf\Request::getString('password', '', 'POST'));
    $server->setVar('priority', $_POST['priority']);
    echo '<html>';
    echo '<head>';
    echo "<link rel='stylesheet' type='text/css' media'screen' href='" . XOOPS_URL . "/xoops.css'>
          <link rel='stylesheet' type='text/css' media='screen' href='" . xoops_getcss() . "'>
          <link rel='stylesheet' type='text/css' media='screen' href='" . XOOPS_URL . "/modules/system/style.css'>";
    echo '</head>';
    echo '<body>';
    echo "<table style='margin:0; padding:0;' class='outer'>";
    if (@$server->connect()) {
        //Connection Succeeded
        echo "<tr><td class='head'>Connection Successful!</td></tr>";
    } else {
        //Connection Failed
        echo "<tr class='head'><td>Connection Failed!</td></tr>";
        echo "<tr class='even'><td>" . $server->getHtmlErrors() . '</td></tr>';
    }
    echo '</table>';
    echo '</body>';
    echo '</html>';
}

/**
 *
 */
function clearAddSession()
{
    clearAddSessionVars();
    redirect_header(Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/department.php', ['op' => 'manageDepartments'], false));
}

/**
 *
 */
function clearAddSessionVars()
{
    $session = Xhelp\Session::getInstance();
    $session->del('xhelp_addDepartment');
    $session->del('xhelp_addDepartmentErrors');
}

/**
 *
 */
function clearEditSession()
{
    $deptid = $_REQUEST['deptid'];
    clearEditSessionVars($deptid);
    redirect_header(Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/department.php', ['op' => 'editDepartment', 'deptid' => $deptid], false));
}

/**
 * @param int $id
 */
function clearEditSessionVars(int $id)
{
    $id      = $id;
    $session = Xhelp\Session::getInstance();
    $session->del("xhelp_editDepartment_$id");
    $session->del("xhelp_editDepartmentErrors_$id");
}

/**
 *
 */
function updateDefault()
{
    $id = Request::getInt('id', 0, 'REQUEST');
    Xhelp\Utility::setMeta('default_department', (string)$id);
    redirect_header(Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/department.php', ['op' => 'manageDepartments'], false));
}
