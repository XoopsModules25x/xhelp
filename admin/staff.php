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

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Xhelp;

require_once __DIR__ . '/admin_header.php';
// require_once XHELP_CLASS_PATH . '/PageNav.php';

global $xoopsModule;

$helper = Xhelp\Helper::getInstance();

$xhelp_id    = 0;
$module_id   = $xoopsModule->getVar('mid');
$displayName = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

$aLimitByS = ['10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100];
$aLimitByD = ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '10' => 10];

if (isset($_REQUEST['op'])) {
    $op = Request::getString('op', 'default', 'REQUEST');
}

switch ($op) {
    case 'addRole':
        addRole();
        break;
    case 'clearOrphanedStaff':
        clearOrphanedStaff();
        break;
    case 'clearRoles':
        clearRoles();
        break;
    case 'customDept':
        customDept();
        break;
    case 'editRole':
        editRole();
        break;
    case 'editStaff':
        editStaff();
        break;
    case 'manageStaff':
        manageStaff();
        break;
    default:
        $helper->redirect('admin/index.php');
        break;
}

function addRole()
{
    // require_once XHELP_CLASS_PATH . '/session.php';
    $session = Xhelp\Session::getInstance();
    $helper  = Xhelp\Helper::getInstance();

    if (isset($_POST['add'])) {
        /** @var \XoopsModules\Xhelp\RoleHandler $roleHandler */
        $roleHandler = $helper->getHandler('Role');

        /** @var \XoopsModules\Xhelp\Role $role */
        $role = $roleHandler->create();
        $role->setVar('name', \Xmf\Request::getString('roleName', '', 'POST'));
        $role->setVar('description', \Xmf\Request::getString('roleDescription', '', 'POST'));
        if (Request::hasVar('tasks', 'POST')) {
            $tasksValue = array_sum($_POST['tasks']);
        } else {
            $tasksValue = 0;
        }
        $role->setVar('tasks', $tasksValue);

        $lastPage = $session->get('xhelp_return_op');

        if ($roleHandler->insert($role)) {
            $message = _AM_XHELP_MESSAGE_ROLE_INSERT;
            $helper->redirect("staff.php?op=$lastPage");
        } else {
            $message = _AM_XHELP_MESSAGE_ROLE_INSERT_ERROR;
            $helper->redirect("staff.php?op=$lastPage", 3, $message);
        }
    } else {
        // Set array of security items
        $tasks = [
            XHELP_SEC_TICKET_ADD            => _AM_XHELP_SEC_TEXT_TICKET_ADD,
            XHELP_SEC_TICKET_EDIT           => _AM_XHELP_SEC_TEXT_TICKET_EDIT,
            XHELP_SEC_TICKET_DELETE         => _AM_XHELP_SEC_TEXT_TICKET_DELETE,
            XHELP_SEC_TICKET_MERGE          => _AM_XHELP_SEC_TEXT_TICKET_MERGE,
            XHELP_SEC_TICKET_OWNERSHIP      => _AM_XHELP_SEC_TEXT_TICKET_OWNERSHIP,
            XHELP_SEC_TICKET_STATUS         => _AM_XHELP_SEC_TEXT_TICKET_STATUS,
            XHELP_SEC_TICKET_PRIORITY       => _AM_XHELP_SEC_TEXT_TICKET_PRIORITY,
            XHELP_SEC_TICKET_LOGUSER        => _AM_XHELP_SEC_TEXT_TICKET_LOGUSER,
            XHELP_SEC_RESPONSE_ADD          => _AM_XHELP_SEC_TEXT_RESPONSE_ADD,
            XHELP_SEC_RESPONSE_EDIT         => _AM_XHELP_SEC_TEXT_RESPONSE_EDIT,
            XHELP_SEC_FILE_DELETE           => _AM_XHELP_SEC_TEXT_FILE_DELETE,
            XHELP_SEC_FAQ_ADD               => _AM_XHELP_SEC_TEXT_FAQ_ADD,
            XHELP_SEC_TICKET_TAKE_OWNERSHIP => _AM_XHELP_SEC_TEXT_TICKET_TAKE_OWNERSHIP,
        ];
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manStaff');
        $adminObject = Admin::getInstance();
        $adminObject->displayNavigation('staff.php?op=addRole');

        echo '<script type="text/javascript" src="' . XOOPS_URL . '/modules/xhelp/include/functions.js"></script>';
        echo "<form action='staff.php?op=addRole' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<table width='100%' cellspacing='1' class='outer'>";
        echo "<tr><th colspan='2'>" . _AM_XHELP_TEXT_CREATE_ROLE . '</th></tr>';
        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_NAME . "</td>
                  <td class='even'><input type='text' name='roleName' maxlength='35' value='' class='formButton'></td>
              </tr>";
        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_DESCRIPTION . "</td>
                  <td class='even'><textarea name='roleDescription' class='formButton'></textarea></td>
              </tr>";
        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_PERMISSIONS . "</td>
                  <td class='even'>
                     <table border='0'>
                     <tr><td>";
        foreach ($tasks as $bit_value => $task) {
            echo "<tr><td><input type='checkbox' name='tasks[]' value='" . (2 ** $bit_value) . "'>" . $task . '</td></tr>';
        }
        echo "<tr><td><input type='checkbox' name='allTasks' value='0' onclick='selectAll(this.form,\"tasks[]\",this.checked);'><b>" . _AM_XHELP_TEXT_SELECT_ALL . '</b></td></tr>';
        echo '</table>
                  </td>
              </tr>';
        echo "<tr>
                  <td colspan='2' class='foot'>
                      <input type='submit' name='add' value='" . _AM_XHELP_BUTTON_CREATE_ROLE . "' class='formButton'>
                      <input type='button' name='cancel' value='" . _AM_XHELP_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton'>
                  </td>
              </tr>";
        echo '</table></form>';
        require_once __DIR__ . '/admin_footer.php';
    }
}

function clearOrphanedStaff()
{
    $helper = Xhelp\Helper::getInstance();
    /** @var \XoopsMemberHandler $memberHandler */
    $memberHandler = xoops_getHandler('member');
    /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
    $staffHandler = $helper->getHandler('Staff');
    $users        = $memberHandler->getUserList();
    $staff        = $staffHandler->getObjects();
    $helper       = Xhelp\Helper::getInstance();

    $aUsers = [];
    foreach ($staff as $stf) {
        $staff_uid = $stf->getVar('uid');
        if (!array_key_exists($staff_uid, $users)) {
            $aUsers[$staff_uid] = $staff_uid;
        }
    }

    $criteria = new \Criteria('uid', '(' . implode(',', $aUsers) . ')', 'IN');
    $ret      = $staffHandler->deleteAll($criteria);

    if ($ret) {
        $helper->redirect('admin/staff.php?op=manageStaff');
    } else {
        $helper->redirect('admin/staff.php?op=manageStaff', 3, _AM_XHELP_MSG_CLEAR_ORPHANED_ERR);
    }
}

function clearRoles()
{
    // require_once XHELP_CLASS_PATH . '/session.php';
    $session = Xhelp\Session::getInstance();
    $helper  = Xhelp\Helper::getInstance();

    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    $depts             = $departmentHandler->getObjects();

    foreach ($depts as $dept) {
        $deptid    = $dept->getVar('id');
        $deptRoles = $session->get("xhelp_dept_$deptid");
        if ($deptRoles) {
            $session->del("xhelp_dept_$deptid");
        }
    }

    if (!$returnPage = $session->get('xhelp_return_page')) {
        $returnPage = false;
    }

    $session->del('xhelp_return_page');
    $session->del('xhelp_mainRoles');
    $session->del('xhelp_mainDepts');
    $session->del('xhelp_return_op');

    if ($returnPage) {
        $helper->redirect((string)$returnPage);
    } else {
        $helper->redirect('admin/staff.php?op=manageStaff');
    }
}

function customDept()
{
    // require_once XHELP_CLASS_PATH . '/session.php';
    $session = Xhelp\Session::getInstance();
    global $xoopsUser, $displayName;
    $helper = Xhelp\Helper::getInstance();

    $lastPage = $session->get('xhelp_return_op');

    $uid    = Request::getInt('uid', 0, 'REQUEST');
    $deptid = 0;
    if (0 == $uid) {
        $helper->redirect("admin/staff.php?op=$lastPage", 3, _AM_XHELP_MSG_NEED_UID);
    }
    if (Request::hasVar('deptid', 'REQUEST')) {
        $deptid = Request::getInt('deptid', 0, 'REQUEST');
    }

    if (isset($_POST['submit'])) {
        /** @var \XoopsModules\Xhelp\RoleHandler $roleHandler */
        $roleHandler = $helper->getHandler('Role');

        if (Request::hasVar('roles', 'POST')) {
            foreach ($_POST['roles'] as $role) {
                $thisRole     = $roleHandler->get($role);
                $aRoleNames[] = $thisRole->getVar('name');
            }
        }

        $session->set("xhelp_dept_$deptid",       // Store roles for customized dept
                      [
                          'id'        => $deptid,
                          'roles'     => !empty($_POST['roles']) ? $_POST['roles'] : -1,
                          'roleNames' => !empty($aRoleNames) ? $aRoleNames : -1,
                      ]);

        $xhelp_has_deptRoles = false;
        $hasRoles            = $session->get("xhelp_dept_$deptid");
        if ($hasRoles) {
            $xhelp_has_deptRoles = true;
            if (-1 == $hasRoles['roles']) {                   // No perms for this dept
                //$session->del("xhelp_dept_$deptid");  // Delete custom roles for dept
                $xhelp_has_deptRoles = false;
            }
        }

        [$mainDepts] = $session->get('xhelp_mainDepts');
        if ($mainDepts) {
            if ($xhelp_has_deptRoles) {           // If dept has roles
                if (!in_array($deptid, $mainDepts)) {             // Does dept already exist in array?
                    $mainDepts[] = $deptid;                       // Add dept to array
                    $session->set('xhelp_mainDepts', $mainDepts); // Set session with new dept value
                }
            } else {
                // Unset element in array with current dept value
                foreach ($mainDepts as $dept) {
                    if ($dept == $deptid) {
                        unset($dept);
                    }
                }
                $session->set('xhelp_mainDepts', $mainDepts);
            }
            // If mainDepts is not set
        } elseif ($xhelp_has_deptRoles) {   // If dept has any roles
            $session->set('xhelp_mainDepts', [$deptid]);
        }

        if (!$lastPage = $session->get('xhelp_return_op2')) {
            $lastPage = $session->get('xhelp_return_op');
        }
        $helper->redirect("admin/staff.php?op=$lastPage&uid=$uid");
    } else {
        if (Request::hasVar('addRole', 'POST')) {
            $session->set('xhelp_return_op2', $lastPage);
            $session->set('xhelp_return_op', mb_substr(mb_strstr($_SERVER['REQUEST_URI'], 'op='), 3));
            $helper->redirect('admin/staff.php?op=addRole');
        }

        if (Request::hasVar('xhelp_role', 'GET')) {
            $aRoles = explode(',', $_GET['xhelp_role']);
            foreach ($aRoles as $role) {
                $role = (int)$role;
            }
            $session->set('xhelp_mainRoles', $aRoles);    // Store roles from the manage staff page
        }

        if (Request::hasVar('xhelp_depts', 'GET')) {
            $aDepts = explode(',', $_GET['xhelp_depts']);
            foreach ($aDepts as $dept) {
                $dept = (int)$dept;
            }
            $session->set('xhelp_mainDepts', $aDepts);    // Store depts from the manage staff page
        }

        /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
        $departmentHandler = $helper->getHandler('Department');
        /** @var \XoopsModules\Xhelp\RoleHandler $roleHandler */
        $roleHandler = $helper->getHandler('Role');

        $dept = $departmentHandler->get($deptid);

        $criteria = new \Criteria('', '');
        $criteria->setOrder('ASC');
        $criteria->setSort('name');
        $roles = $roleHandler->getObjects($criteria);

        $lastPage = $session->get('xhelp_return_op');
        xoops_cp_header();

        echo '<script type="text/javascript" src="' . XOOPS_URL . '/modules/xhelp/include/functions.js"></script>';
        echo "<form action='staff.php?op=customDept&amp;deptid=" . $deptid . '&amp;uid=' . $uid . "' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<table width='100%' cellspacing='1' class='outer'>";
        echo "<tr><th colspan='2'>" . _AM_XHELP_TEXT_DEPT_PERMS . '</th></tr>';
        echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_USER . "</td>
                  <td class='even'>" . Xhelp\Utility::getUsername($uid, $displayName) . '</td></tr>';
        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_DEPARTMENT . "</td>
                  <td class='even'>" . $dept->getVar('department') . '</td></tr>';
        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_ROLES . "</td>
                  <td class='even'><table width='75%'>";

        $bFound      = false;
        $storedRoles = $session->get("xhelp_dept_$deptid");
        if ($storedRoles) {    // If editing previously customized dept
            foreach ($roles as $role) {
                if (-1 != $storedRoles['roles']) {
                    foreach ($storedRoles['roles'] as $storedRole) {
                        if ($role->getVar('id') == $storedRole) {
                            $bFound = true;
                            break;
                        }

                        $bFound = false;
                    }
                }
                if ($bFound) {
                    echo "<tr><td><input type='checkbox' name='roles[]' checked value='" . $role->getVar('id') . "'><a href='staff.php?op=editRole&amp;id=" . $role->getVar('id') . '&amp;uid=' . $uid . "'>" . $role->getVar('name') . '</a> - ' . $role->getVar('description') . '</td></tr>';
                } else {
                    echo "<tr><td><input type='checkbox' name='roles[]' value='" . $role->getVar('id') . "'><a href='staff.php?op=editRole&amp;id=" . $role->getVar('id') . '&amp;uid=' . $uid . "'>" . $role->getVar('name') . '</a> - ' . $role->getVar('description') . '</td></tr>';
                }
            }
        } elseif ($mainRoles = $session->get('xhelp_mainRoles')) {    // If roles set on manage staff page
            foreach ($roles as $role) {
                if (in_array($role->getVar('id'), $mainRoles)) {
                    echo "<tr><td><input type='checkbox' name='roles[]' value='" . $role->getVar('id') . "' checked><a href='staff.php?op=editRole&amp;id=" . $role->getVar('id') . '&amp;uid=' . $uid . "'>" . $role->getVar('name') . '</a> - ' . $role->getVar('description') . '</td></tr>';
                } else {
                    echo "<tr><td><input type='checkbox' name='roles[]' value='" . $role->getVar('id') . "'><a href='staff.php?op=editRole&amp;id=" . $role->getVar('id') . '&amp;uid=' . $uid . "'>" . $role->getVar('name') . '</a> - ' . $role->getVar('description') . '</td></tr>';
                }
            }
        } elseif ('editStaff' === $lastPage && (!$storedRoles = $session->get("xhelp_dept_$deptid"))) {
            /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
            $staffHandler = $helper->getHandler('Staff');
            $myRoles      = $staffHandler->getRolesByDept($uid, $deptid);

            $bFound = false;
            foreach ($roles as $role) {
                if (!empty($myRoles)) {
                    foreach ($myRoles as $myRole) {
                        if ($role->getVar('id') == $myRole->getVar('roleid')) {
                            $bFound = true;
                            break;
                        }

                        $bFound = false;
                    }
                }
                if ($bFound) {
                    echo "<tr><td><input type='checkbox' name='roles[]' checked value='" . $role->getVar('id') . "'><a href='staff.php?op=editRole&amp;id=" . $role->getVar('id') . '&amp;uid=' . $uid . "'>" . $role->getVar('name') . '</a> - ' . $role->getVar('description') . '</td></tr>';
                } else {
                    echo "<tr><td><input type='checkbox' name='roles[]' value='" . $role->getVar('id') . "'><a href='staff.php?op=editRole&amp;id=" . $role->getVar('id') . '&amp;uid=' . $uid . "'>" . $role->getVar('name') . '</a> - ' . $role->getVar('description') . '</td></tr>';
                }
            }
        } else {
            foreach ($roles as $role) {     // If no roles set
                echo "<tr><td><input type='checkbox' name='roles[]' value='" . $role->getVar('id') . "'><a href='staff.php?op=editRole&amp;id=" . $role->getVar('id') . '&amp;uid=' . $uid . "'>" . $role->getVar('name') . '</a> - ' . $role->getVar('description') . '</td></tr>';
            }
        }
        echo "<tr><td><input type='checkbox' name='checkallRoles' value='0' onclick='selectAll(this.form,\"roles[]\",this.checked);'><b>" . _AM_XHELP_TEXT_SELECT_ALL . '</b></td></tr>';
        echo '</table></td></tr>';
        echo "<tr><td colspan='2' class='foot'>
                      <input type='submit' name='submit' value='" . _AM_XHELP_BUTTON_UPDATE . "' class='formButton'>
                      <input type='submit' name='addRole' value='" . _AM_XHELP_BUTTON_CREATE_ROLE . "' class='formButton'>
                      <input type='button' name='cancel' value='" . _AM_XHELP_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton'>
                  </td>
              </tr>";
        echo '</table>';
        require_once __DIR__ . '/admin_footer.php';
    }
}

/**
 * @param int|string $xhelp_id
 * @param string     $return_op
 */
function deleteRole($xhelp_id, string $return_op)
{
    $xhelp_id = (int)$xhelp_id;
    $helper   = Xhelp\Helper::getInstance();

    /** @var \XoopsModules\Xhelp\RoleHandler $roleHandler */
    $roleHandler = $helper->getHandler('Role');
    $role        = $roleHandler->get($xhelp_id);

    if ($roleHandler->delete($role, true)) {
        $message = _AM_XHELP_MESSAGE_ROLE_DELETE;
        $helper->redirect("admin/staff.php?op=$return_op");
    } else {
        $message = _AM_XHELP_MESSAGE_ROLE_DELETE_ERROR;
        $helper->redirect("admin/staff.php?op=$return_op", 3, $message);
    }
}

function editRole()
{
    // require_once XHELP_CLASS_PATH . '/session.php';
    $session = Xhelp\Session::getInstance();
    $helper  = Xhelp\Helper::getInstance();

    $lastPage = $session->get('xhelp_return_op');
    $xhelp_id = 0;

    if (Request::hasVar('id', 'REQUEST')) {
        $xhelp_id = Request::getInt('id', 0, 'REQUEST');
    }

    $uid = Request::getInt('uid', 0, 'REQUEST');

    /** @var \XoopsModules\Xhelp\RoleHandler $roleHandler */
    $roleHandler = $helper->getHandler('Role');
    $role        = $roleHandler->get($xhelp_id);

    if (Request::hasVar('deleteRole', 'POST')) {
        deleteRole($xhelp_id, 'manageStaff');
        exit();
    }

    if (isset($_POST['edit'])) {
        $role->setVar('name', \Xmf\Request::getString('roleName', '', 'POST'));
        $role->setVar('description', \Xmf\Request::getString('roleDescription', '', 'POST'));
        if (Request::hasVar('tasks', 'POST')) {
            $tasksValue = array_sum($_POST['tasks']);
        } else {
            $tasksValue = 0;
        }
        $role->setVar('tasks', $tasksValue);

        if (!$lastPage = $session->get('xhelp_return_op2')) {
            $lastPage = $session->get('xhelp_return_op');
        }

        if ($roleHandler->insert($role)) {
            Xhelp\Utility::resetStaffUpdatedTime();

            $message = _AM_XHELP_MESSAGE_ROLE_UPDATE;
            $helper->redirect("admin/staff.php?op=$lastPage&uid=$uid");
        } else {
            $message = _AM_XHELP_MESSAGE_ROLE_UPDATE_ERROR;
            $helper->redirect("admin/staff.php?op=$lastPage&uid=$uid", 3, $message);
        }
    } else {
        $session->set('xhelp_return_op2', $lastPage);
        $session->set('xhelp_return_op', mb_substr(mb_strstr($_SERVER['REQUEST_URI'], 'op='), 3));

        // Set array of security items
        $tasks = [
            XHELP_SEC_TICKET_ADD            => _AM_XHELP_SEC_TEXT_TICKET_ADD,
            XHELP_SEC_TICKET_EDIT           => _AM_XHELP_SEC_TEXT_TICKET_EDIT,
            XHELP_SEC_TICKET_DELETE         => _AM_XHELP_SEC_TEXT_TICKET_DELETE,
            XHELP_SEC_TICKET_OWNERSHIP      => _AM_XHELP_SEC_TEXT_TICKET_OWNERSHIP,
            XHELP_SEC_TICKET_STATUS         => _AM_XHELP_SEC_TEXT_TICKET_STATUS,
            XHELP_SEC_TICKET_PRIORITY       => _AM_XHELP_SEC_TEXT_TICKET_PRIORITY,
            XHELP_SEC_TICKET_LOGUSER        => _AM_XHELP_SEC_TEXT_TICKET_LOGUSER,
            XHELP_SEC_RESPONSE_ADD          => _AM_XHELP_SEC_TEXT_RESPONSE_ADD,
            XHELP_SEC_RESPONSE_EDIT         => _AM_XHELP_SEC_TEXT_RESPONSE_EDIT,
            XHELP_SEC_TICKET_MERGE          => _AM_XHELP_SEC_TEXT_TICKET_MERGE,
            XHELP_SEC_FILE_DELETE           => _AM_XHELP_SEC_TEXT_FILE_DELETE,
            XHELP_SEC_FAQ_ADD               => _AM_XHELP_SEC_TEXT_FAQ_ADD,
            XHELP_SEC_TICKET_TAKE_OWNERSHIP => _AM_XHELP_SEC_TEXT_TICKET_TAKE_OWNERSHIP,
        ];
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manStaff');
        $adminObject = Admin::getInstance();
        $adminObject->displayNavigation('staff.php?op=editRole');

        echo '<script type="text/javascript" src="' . XOOPS_URL . '/modules/xhelp/include/functions.js"></script>';
        echo "<form action='staff.php?op=editRole&amp;id=" . $xhelp_id . '&amp;uid=' . $uid . "' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<table width='100%' cellspacing='1' class='outer'>";
        echo "<tr><th colspan='2'>" . _AM_XHELP_TEXT_EDIT_ROLE . '</th></tr>';
        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_NAME . "</td>
                  <td class='even'><input type='text' name='roleName' maxlength='35' value='" . $role->getVar('name') . "' class='formButton'></td>
              </tr>";
        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_DESCRIPTION . "</td>
                  <td class='even'><textarea name='roleDescription' class='formButton'>" . $role->getVar('description') . '</textarea></td>
              </tr>';
        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_PERMISSIONS . "</td>
                  <td class='even'>
                     <table border='0'>
                     <tr><td>";
        foreach ($tasks as $bit_value => $task) {
            if (($role->getVar('tasks') & (2 ** $bit_value)) > 0) {
                echo "<tr><td><input type='checkbox' name='tasks[]' value='" . (2 ** $bit_value) . "' checked>" . $task . '</td></tr>';
            } else {
                echo "<tr><td><input type='checkbox' name='tasks[]' value='" . (2 ** $bit_value) . "'>" . $task . '</td></tr>';
            }
        }
        echo "<tr><td><input type='checkbox' name='allTasks' value='0' onclick='selectAll(this.form,\"tasks[]\",this.checked);'><b>" . _AM_XHELP_TEXT_SELECT_ALL . '</b></td></tr>';
        echo '</table>
                  </td>
              </tr>';
        echo "<tr>
                  <td colspan='2' class='foot'>
                      <input type='submit' name='edit' value='" . _AM_XHELP_BUTTON_UPDATE . "' class='formButton'>
                      <input type='button' name='cancel' value='" . _AM_XHELP_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton'>
                      <input type='submit' name='deleteRole' value='" . _AM_XHELP_BUTTON_DELETE . "' class='formButton'>

                  </td>
              </tr>";
        echo '</table></form>';
        require_once __DIR__ . '/admin_footer.php';
    }
}

function editStaff()
{
    global $_POST, $_GET, $xoopsModule, $xoopsUser, $displayName;
    // require_once XHELP_CLASS_PATH . '/session.php';
    $session = Xhelp\Session::getInstance();
    $helper  = Xhelp\Helper::getInstance();

    if (Request::hasVar('uid', 'REQUEST')) {
        $uid = Request::getInt('uid', 0);
    }
    /*
     if (\Xmf\Request::hasVar('user', 'REQUEST')) {       // Remove me
     $uid = $_REQUEST['user'];
     }
     */
    if (Request::hasVar('clearRoles', 'POST')) {
        $helper->redirect('admin/staff.php?op=clearRoles');
    }

    $session->set('xhelp_return_op', 'editStaff');

    if (isset($_POST['updateStaff'])) {
        $uid       = Request::getInt('uid', 0, 'POST');
        $depts     = $_POST['departments'];
        $roles     = $_POST['roles'];
        $custroles = $_POST['custrole'];

        /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
        $staffHandler = $helper->getHandler('Staff');
        /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
        $membershipHandler = $helper->getHandler('Membership');

        //Remove existing dept membership
        if (!$membershipHandler->clearStaffMembership($uid)) {
            $message = _XHELP_MESSAGE_EDITSTAFF_NOCLEAR_ERROR;
            $helper->redirect('admin/staff.php?op=manageStaff', 3, $message);
        }

        //Add staff member to selected depts
        if ($membershipHandler->addDeptToStaff($depts, $uid)) {
            $message = _XHELP_MESSAGE_EDITSTAFF;
        } else {
            $message = _XHELP_MESSAGE_EDITSTAFF_ERROR;
        }

        //Clear Existing Staff Role Permissions
        $removedRoles = $staffHandler->removeStaffRoles($uid);

        //Add Global Role Permissions
        foreach ($roles as $role) {
            $staffHandler->addStaffRole($uid, $role, 0);
        }

        //Add Department Specific Roles
        foreach ($depts as $dept) {
            if ('' != $custroles[$dept]) {
                $dept_roles = explode(',', $custroles[$dept]);
            } else {
                $dept_roles = $roles;
            }

            foreach ($dept_roles as $role) {
                $staffHandler->addStaffRole($uid, (int)$role, $dept);
            }
        }

        $staff = $staffHandler->getByUid($uid);
        $staff->setVar('permTimestamp', time());
        if (!$staffHandler->insert($staff)) {
            $message = _XHELP_MESSAGE_EDITSTAFF;
        }

        $helper->redirect('admin/staff.php?op=clearRoles', 3, $message);
    } else {
        //xoops_cp_header();
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = xoops_getHandler('member');          // Get member handler
        $member        = $memberHandler->getUser($uid);

        /** @var \XoopsModules\Xhelp\RoleHandler $roleHandler */
        $roleHandler = $helper->getHandler('Role');
        $criteria    = new \Criteria('', '');
        $criteria->setOrder('ASC');
        $criteria->setSort('name');
        $roles = $roleHandler->getObjects($criteria, true);

        /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
        $departmentHandler = $helper->getHandler('Department');    // Get department handler
        $criteria          = new \Criteria('', '');
        $criteria->setSort('department');
        $criteria->setOrder('ASC');
        $total          = $departmentHandler->getCount($criteria);
        $departmentInfo = $departmentHandler->getObjects($criteria);

        /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
        $staffHandler = $helper->getHandler('Staff');       // Get staff handler
        $staff        = $staffHandler->getByUid($uid);
        /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
        $membershipHandler = $helper->getHandler('Membership');
        $staffDepts        = $membershipHandler->membershipByStaff($uid);
        $staffRoles        = $staff->getAllRoleRights();
        $global_roles      = (isset($staffRoles[0]['roles']) ? array_keys($staffRoles[0]['roles']) : []);  //Get all Global Roles

        $xhelp_depts = [];
        foreach ($staffDepts as $myDept) {
            $deptid = $myDept->getVar('id');
            if (0 != $deptid) {
                $xhelp_depts[] = $deptid;
            }
        }
        $xhelp_depts = implode(',', $xhelp_depts);

        //$myRoles =& $staffHandler->getRoles($staff->getVar('uid'));
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manStaff');
        $adminObject = Admin::getInstance();
        $adminObject->displayNavigation('staff.php?op=editStaff');

        echo '<script type="text/javascript" src="' . XOOPS_URL . '/modules/xhelp/include/functions.js"></script>';
        echo "<form name='frmEditStaff' method='post' action='staff.php?op=editStaff&amp;uid=" . $uid . "'>";
        echo "<table width='100%' border='0' cellspacing='1' class='outer'>
              <tr><th colspan='2'><label>" . _AM_XHELP_EDIT_STAFF . '</label></th></tr>';
        echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_USER . "</td>
                  <td class='even'>" . Xhelp\Utility::getUsername($member, $displayName);
        echo '</td></tr>';
        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_ROLES . "</td>
                  <td class='even'><table width='75%'>";

        foreach ($roles as $role) {
            $roleid = $role->getVar('id');
            if (in_array($roleid, $global_roles)) {
                echo "<tr><td><input type='checkbox' name='roles[]' checked value='"
                     . $role->getVar('id')
                     . "' onclick=\"Xhelp\RoleCustOnClick('frmEditStaff', 'roles[]', 'xhelp_role', '&amp;', 'xhelp_dept_cust');\"><a href='staff.php?op=editRole&amp;id="
                     . $role->getVar('id')
                     . '&amp;uid='
                     . $uid
                     . "'>"
                     . $role->getVar('name')
                     . '</a> - '
                     . $role->getVar('description')
                     . '</td></tr>';
            } else {
                $mainRoles = $session->get('xhelp_mainRoles');
                if ($mainRoles) {
                    if (in_array($roleid, $mainRoles)) {
                        echo "<tr><td><input type='checkbox' name='roles[]' checked value='"
                             . $role->getVar('id')
                             . "' onclick=\"Xhelp\RoleCustOnClick('frmEditStaff', 'roles[]', 'xhelp_role', '&amp;', 'xhelp_dept_cust');\"><a href='staff.php?op=editRole&amp;id="
                             . $role->getVar('id')
                             . '&amp;uid='
                             . $uid
                             . "'>"
                             . $role->getVar('name')
                             . '</a> - '
                             . $role->getVar('description')
                             . '</td></tr>';
                    } else {
                        echo "<tr><td><input type='checkbox' name='roles[]'  value='"
                             . $role->getVar('id')
                             . "' onclick=\"Xhelp\RoleCustOnClick('frmEditStaff', 'roles[]', 'xhelp_role', '&amp;', 'xhelp_dept_cust');\"><a href='staff.php?op=editRole&amp;id="
                             . $role->getVar('id')
                             . '&amp;uid='
                             . $uid
                             . "'>"
                             . $role->getVar('name')
                             . '</a> - '
                             . $role->getVar('description')
                             . '</td></tr>';
                    }
                } else {
                    echo "<tr><td><input type='checkbox' name='roles[]'  value='"
                         . $role->getVar('id')
                         . "' onclick=\"Xhelp\RoleCustOnClick('frmEditStaff', 'roles[]', 'xhelp_role', '&amp;', 'xhelp_dept_cust');\"><a href='staff.php?op=editRole&amp;id="
                         . $role->getVar('id')
                         . '&amp;uid='
                         . $uid
                         . "'>"
                         . $role->getVar('name')
                         . '</a> - '
                         . $role->getVar('description')
                         . '</td></tr>';
                }
            }
        }
        echo "<tr><td><input type='checkbox' name='checkallRoles' value='0' onclick='selectAll(this.form,\"roles[]\",this.checked); Xhelp\RoleCustOnClick(\"frmEditStaff\", \"roles[]\", \"xhelp_role\", \"&amp;\", \"xhelp_dept_cust\");'><b>" . _AM_XHELP_TEXT_SELECT_ALL . '</b></td></tr>';
        echo '</table></td></tr>';
        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_DEPARTMENTS . "</td>
                  <td class='even'><table width='75%'>";

        // This block is used to append custom role names to each department
        foreach ($departmentInfo as $dept) {
            $deptid   = $dept->getVar('id');
            $deptname = $dept->getVar('department');
            $inDept   = false;  //Is the user a member of the dept

            $deptroleids   = [];
            $deptrolenames = [];

            $sess_roles = $session->get("xhelp_dept_$deptid");
            if ($sess_roles) {  //Customized roles stored in session?
                if (-1 != $sess_roles['roles']) {                           //Is the user assigned to any roles in the dept?
                    $inDept = true;
                    foreach ($sess_roles['roles'] as $roleid) {   // Check if customized roles match global roles
                        if (in_array($roleid, $global_roles)) {   // If found role in global roles
                            $deptroleids[] = $roleid;             // Add role to array of checked roles
                        }
                    }
                    $deptroleids = implode(',', $sess_roles['roles']);  // Put all roles into 1 string separated by a ','

                    //An empty string means dept roles match global roles
                    if ('' !== $deptroleids) { //Customized Roles
                        $deptrolenames = implode(', ', $sess_roles['roleNames']);
                    }
                } else {                                //Not a member of the dept
                    $inDept = false;
                }
            } elseif (isset($staffRoles[$deptid])) {    //User has assigned dept roles
                $inDept = true;

                if ($staffRoles[$deptid]['roles'] == $staffRoles[0]['roles']) { // If global roles same as dept roles
                    //                    $deptrolenames = [];
                    //                    $deptroleids   = [];
                    foreach ($staffRoles[$deptid]['roles'] as $roleid => $tasks) {
                        if (isset($roles[$roleid])) {
                            $deptroleids[] = $roleid;
                        }
                    }
                    $deptroleids   = implode(',', $deptroleids);
                    $deptrolenames = '';
                } else {
                    //                    $deptrolenames = [];
                    //                    $deptroleids   = [];
                    foreach ($staffRoles[$deptid]['roles'] as $roleid => $tasks) {
                        if (isset($roles[$roleid])) {
                            $deptroleids[]   = $roleid;
                            $deptrolenames[] = $roles[$roleid]->getVar('name');
                        }
                    }
                    $deptrolenames = implode(', ', $deptrolenames);
                    $deptroleids   = implode(',', $deptroleids);
                }
            } else {        //Not a member of the dept
                $deptroleids = [];
                foreach ($staffRoles[0]['roles'] as $roleid => $tasks) {
                    if (isset($roles[$roleid])) {
                        $deptroleids[] = $roleid;
                    }
                }
                $deptroleids   = implode(',', $deptroleids);
                $deptrolenames = '';

                $inDept = false;
            }

            //Should element be checked?
            $checked = ($inDept ? 'checked' : '');

            printf(
                "<tr><td><input type='checkbox' name='departments[]' value='%u' %s onclick=\"Xhelp\RoleCustOnClick('frmEditStaff', 'departments[]', 'xhelp_depts', '&amp;', 'xhelp_dept_cust');\">%s [<a href='staff.php?op=customDept&amp;deptid=%u&amp;uid=%u&amp;xhelp_role=%s&amp;xhelp_depts=%s' class='xhelp_dept_cust'>Customize</a>] <i>%s</i><input type='hidden' name='custrole[%u]' value='%s'></td></tr>",
                $deptid,
                $checked,
                $deptname,
                $deptid,
                $uid,
                $deptroleids,
                $xhelp_depts,
                $deptrolenames,
                $deptid,
                $deptroleids
            );
        }
        echo "<tr><td>
                  <input type='checkbox' name='checkAll' value='0' onclick='selectAll(this.form,\"departments[]\", this.checked);Xhelp\RoleCustOnClick(\"frmEditStaff\", \"departments[]\", \"xhelp_depts\", \"&amp;\", \"xhelp_dept_cust\");'><b>" . _AM_XHELP_TEXT_SELECT_ALL . '</b></td></tr>';
        echo '<tr><td>';
        echo '</td></tr>';
        echo '</table>';
        echo '</td></tr>';
        echo "<tr><td colspan='2' class='foot'>
                  <input type='hidden' name='uid' value='" . $uid . "'>
                  <input type='submit' name='updateStaff' value='" . _AM_XHELP_BUTTON_UPDATESTAFF . "'>
                  <input type='button' name='cancel' value='" . _AM_XHELP_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton'>
              </td></tr>";
        echo '</table></form>';

        require_once __DIR__ . '/admin_footer.php';
    }//end if
}//end function

function manageStaff()
{
    global $xoopsModule, $xoopsUser, $displayName, $aLimitByS, $aLimitByD;
    // require_once XHELP_CLASS_PATH . '/session.php';
    $session = Xhelp\Session::getInstance();
    $session->del('xhelp_return_page');
    $helper = Xhelp\Helper::getInstance();

    $start        = $limit = 0;
    $dstart       = $dlimit = 0;
    $staff_search = false;
    $dept_search  = false;

    if (Request::hasVar('addRole', 'POST')) {
        $helper->redirect('admin/staff.php?op=addRole');
    }
    if (Request::hasVar('clearRoles', 'POST')) {
        $helper->redirect('admin/staff.php?op=clearRoles');
    }

    if (Request::hasVar('limit', 'REQUEST')) {
        $limit = Request::getInt('limit', 0, 'REQUEST');
    }

    if (Request::hasVar('start', 'REQUEST')) {
        $start = Request::getInt('start', 0, 'REQUEST');
    }
    if (Request::hasVar('staff_search', 'REQUEST')) {
        $staff_search = $_REQUEST['staff_search'];
    }

    if (Request::hasVar('dept_search', 'REQUEST')) {
        $dept_search = $_REQUEST['dept_search'];
    }

    if (!$limit) {
        $limit = 20;
    }

    if (Request::hasVar('dlimit', 'REQUEST')) {
        $dlimit = Request::getInt('dlimit', 0, 'REQUEST');
    }

    if (Request::hasVar('dstart', 'REQUEST')) {
        $dstart = Request::getInt('dstart', 0, 'REQUEST');
    }

    if (!$dlimit) {
        $dlimit = 10;
    }

    $session->set('xhelp_return_op', 'manageStaff');

    if (isset($_POST['addStaff'])) {
        $uid   = $_POST['user_id'];
        $depts = $_POST['departments'];
        $roles = $_POST['roles'] ?? null;
        //$selectAll = $_POST['selectall'];

        /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
        $staffHandler = $helper->getHandler('Staff');

        if (null === $uid || '' == $uid) {
            $helper->redirect('admin/staff.php?op=manageStaff', 3, _AM_XHELP_STAFF_ERROR_USERS);
        }
        if (null === $depts) {
            $helper->redirect('admin/staff.php?op=manageStaff', 3, _AM_XHELP_STAFF_ERROR_DEPTARTMENTS);
        }
        if (null === $roles) {
            $helper->redirect('admin/staff.php?op=manageStaff', 3, _AM_XHELP_STAFF_ERROR_ROLES);
        }
        if ($staffHandler->isStaff($uid)) {
            $helper->redirect('admin/staff.php?op=manageStaff', 3, _AM_XHELP_STAFF_EXISTS);
        }

        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = xoops_getHandler('member');          // Get member handler
        $newUser       = $memberHandler->getUser($uid);

        $email = $newUser->getVar('email');
        if ($staffHandler->addStaff($uid, $email)) {    // $selectAll
            $message = _XHELP_MESSAGE_ADDSTAFF;
            /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
            $membershipHandler = $helper->getHandler('Membership');

            //Set Department Membership
            if ($membershipHandler->addDeptToStaff($depts, $uid)) {
                $message = _XHELP_MESSAGE_ADDSTAFF;
            } else {
                $message = _XHELP_MESSAGE_ADDSTAFF_ERROR;
            }

            //Set Global Roles
            foreach ($roles as $role) {
                $staffHandler->addStaffRole($uid, $role, 0);
            }

            //Set Department Roles
            foreach ($depts as $dept) {
                $custRoles = $session->get("xhelp_dept_$dept");
                if ($custRoles) {
                    if (-1 != $custRoles['roles']) {
                        foreach ($custRoles['roles'] as $role) {
                            $staffHandler->addStaffRole($uid, $role, $dept);
                        }
                    } else {
                        // If dept still checked, but no custom depts, give global roles to dept
                        foreach ($roles as $role) {
                            $staffHandler->addStaffRole($uid, $role, $dept);
                        }
                    }
                } else {
                    foreach ($roles as $role) {
                        $staffHandler->addStaffRole($uid, $role, $dept);
                    }
                }
            }
            /** @var \XoopsModules\Xhelp\TicketListHandler $ticketListHandler */
            $ticketListHandler = $helper->getHandler('TicketList');
            $hasTicketLists    = $ticketListHandler->createStaffGlobalLists($uid);

            $helper->redirect('admin/staff.php?op=clearRoles');
        } else {
            $message = _XHELP_MESSAGE_ADDSTAFF_ERROR;
            $helper->redirect('admin/staff.php?op=clearRoles', 3, $message);
        }
    } else {
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = xoops_getHandler('member');          // Get member handler
        /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
        $staffHandler = $helper->getHandler('Staff');       // Get staff handler
        /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
        $departmentHandler = $helper->getHandler('Department');    // Get department handler
        /** @var \XoopsModules\Xhelp\RoleHandler $roleHandler */
        $roleHandler = $helper->getHandler('Role');

        //Get List of depts in system
        $criteria = new \Criteria('', '');
        $criteria->setSort('department');
        $criteria->setOrder('ASC');

        $dept_count = $departmentHandler->getCount($criteria);
        $dept_obj   = $departmentHandler->getObjects($criteria);
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manStaff');
        $adminObject = Admin::getInstance();
        $adminObject->displayNavigation('staff.php?op=manageStaff');

        if (Request::hasVar('uid', 'GET')) {
            $userid = Request::getInt('uid', 0, 'GET');
            $uname  = $xoopsUser::getUnameFromId($userid);
        } else {
            $userid = 0;
            $uname  = '';
        }

        if ($dept_count > 0) {
            $userid = Request::getInt('uid', 0, 'GET');

            //Get List of staff members
            $criteria = new \Criteria('', '');
            $criteria->setStart($start);
            $criteria->setLimit($limit);

            $staff_obj   = $staffHandler->getObjects($criteria);
            $staff_count = $staffHandler->getCount($criteria);
            $user_count  = $memberHandler->getUserCount();

            $nav = new Xhelp\PageNav($staff_count, $limit, $start, 'start', "op=manageStaff&amp;limit=$limit");

            //Get List of Staff Roles
            $criteria = new \Criteria('', '');
            $criteria->setOrder('ASC');
            $criteria->setSort('name');
            $roles = $roleHandler->getObjects($criteria);

            echo '<script type="text/javascript" src="' . XOOPS_URL . '/modules/xhelp/include/functions.js"></script>';
            echo "<form method='post' id='manageStaff' name='manageStaff' action='staff.php?op=manageStaff'>";
            echo "<table width='100%' cellspacing='1' class='outer'>
                  <tr><th colspan='2'>" . _AM_XHELP_ADD_STAFF . '</th></tr>';

            echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_USER . "</td>
                      <td class='even'>
                          <input type='text' id='fullname' name='fullname' class='formButton' value='" . $uname . "' disabled='disabled' style='background-color:#E1E1E1;' onchange=\"window.location='staff.php?op=manageStaff&amp;uid='+user_id.value;\">
                          <input type='hidden' id='user_id' name='user_id' class='formButton' value='" . $userid . "'>";
            echo "&nbsp;<a href=\"javascript:openWithSelfMain('" . XHELP_BASE_URL . "/lookup.php?admin=1', 'lookup',400, 300);\" title='" . _AM_XHELP_TEXT_FIND_USERS . "'>" . _AM_XHELP_TEXT_FIND_USERS . '</a>
                      </td>
                  </tr>';

            echo '</td></tr>';
            echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_ROLES . "</td>
                      <td class='even'><table width='75%'>";
            $mainRoles = $session->get('xhelp_mainRoles');
            if ($mainRoles) {
                foreach ($roles as $role) {
                    if (in_array($role->getVar('id'), $mainRoles)) {
                        echo "<tr><td><input type='checkbox' name='roles[]' value='" . $role->getVar('id') . "' checked onclick=\"Xhelp\RoleCustOnClick('manageStaff', 'roles[]', 'xhelp_role', '&amp;', 'xhelp_dept_cust');\">
                              <a href='staff.php?op=editRole&amp;id=" . $role->getVar('id') . '&amp;uid=' . $userid . "'>" . $role->getVar('name') . '</a> - ' . $role->getVar('description') . '</td></tr>';
                    } else {
                        echo "<tr><td><input type='checkbox' name='roles[]' value='" . $role->getVar('id') . "' onclick=\"Xhelp\RoleCustOnClick('manageStaff', 'roles[]', 'xhelp_role', '&amp;', 'xhelp_dept_cust');\">
                              <a href='staff.php?op=editRole&amp;id=" . $role->getVar('id') . '&amp;uid=' . $userid . "'>" . $role->getVar('name') . '</a> - ' . $role->getVar('description') . '</td></tr>';
                    }
                }
            } else {
                foreach ($roles as $role) {
                    echo "<tr><td><input type='checkbox' name='roles[]' value='" . $role->getVar('id') . "' onclick=\"Xhelp\RoleCustOnClick('manageStaff', 'roles[]', 'xhelp_role', '&amp;', 'xhelp_dept_cust');\">
                          <a href='staff.php?op=editRole&amp;id=" . $role->getVar('id') . '&amp;uid=' . $userid . "'>" . $role->getVar('name') . '</a> - ' . $role->getVar('description') . '</td></tr>';
                }
            }
            echo "<tr><td><input type='checkbox' name='checkallRoles' value='0' onclick='selectAll(this.form,\"roles[]\",this.checked); Xhelp\RoleCustOnClick(\"manageStaff\", \"roles[]\", \"xhelp_role\", \"&amp;\", \"xhelp_dept_cust\");'><b>" . _AM_XHELP_TEXT_SELECT_ALL . '</b></td></tr>';
            echo '</table></td></tr>';
            echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_DEPARTMENTS . "</td>
                  <td class='even' width='50%'><table width='75%'>";
            $mainDepts = $session->get('xhelp_mainDepts');
            if ($mainDepts) {
                foreach ($dept_obj as $dept) {
                    $deptid     = $dept->getVar('id');
                    $aDept      = $session->get("xhelp_dept_$deptid");
                    $aDeptRoles = $aDept['roleNames'];
                    if (!empty($aDeptRoles) && is_array($aDeptRoles)) {
                        $deptRoles = implode(', ', $aDeptRoles);
                    } else {
                        $deptRoles = '';
                    }
                    if (in_array($dept->getVar('id'), $mainDepts)) {
                        echo "<tr><td>
                              <input type='checkbox' name='departments[]' checked value='" . $dept->getVar('id') . "' onclick=\"Xhelp\RoleCustOnClick('manageStaff', 'departments[]', 'xhelp_depts', '&amp;', 'xhelp_dept_cust');\">
                              " . $dept->getVar('department') . " [<a href='staff.php?op=customDept&amp;deptid=" . $dept->getVar('id') . '&amp;uid=' . $userid . "' class='xhelp_dept_cust'>" . _AM_XHELP_TEXT_CUSTOMIZE . '</a>] <i>' . $deptRoles . '</i>
                              </td></tr>';
                    } else {
                        echo "<tr><td>
                              <input type='checkbox' name='departments[]' value='" . $dept->getVar('id') . "' onclick=\"Xhelp\RoleCustOnClick('manageStaff', 'departments[]', 'xhelp_depts', '&amp;', 'xhelp_dept_cust');\">
                              " . $dept->getVar('department') . " [<a href='staff.php?op=customDept&amp;deptid=" . $dept->getVar('id') . '&amp;uid=' . $userid . "' class='xhelp_dept_cust'>" . _AM_XHELP_TEXT_CUSTOMIZE . '</a>] <i>' . $deptRoles . '</i>
                              </td></tr>';
                    }
                }
            } else {
                foreach ($dept_obj as $dept) {
                    $deptid     = $dept->getVar('id');
                    $aDept      = $session->get("xhelp_dept_$deptid");
                    $aDeptRoles = $aDept['roleNames'] ?? '';
                    if (!empty($aDeptRoles)) {
                        $deptRoles = implode(', ', $aDeptRoles);
                    } else {
                        $deptRoles = '';
                    }
                    echo "<tr><td>
                          <input type='checkbox' name='departments[]' value='" . $dept->getVar('id') . "' onclick=\"Xhelp\RoleCustOnClick('manageStaff', 'departments[]', 'xhelp_depts', '&amp;', 'xhelp_dept_cust');\">
                          " . $dept->getVar('department') . " [<a href='staff.php?op=customDept&amp;deptid=" . $dept->getVar('id') . '&amp;uid=' . $userid . "' class='xhelp_dept_cust'>" . _AM_XHELP_TEXT_CUSTOMIZE . '</a>] <i>' . $deptRoles . '</i>
                          </td></tr>';
                }
            }
            echo "<tr><td><input type='checkbox' name='checkallDepts' value='0' onclick='selectAll(this.form,\"departments[]\",this.checked);Xhelp\RoleCustOnClick(\"manageStaff\", \"departments[]\", \"xhelp_depts\", \"&amp;\", \"xhelp_dept_cust\");'><b>"
                 . _AM_XHELP_TEXT_SELECT_ALL
                 . '</b></td></tr>';
            echo '</table></td></tr>';
            echo "<tr><td colspan='2' class='foot'>
                  <input type='submit' name='addStaff' value='" . _AM_XHELP_BUTTON_ADDSTAFF . "'>
                  <input type='submit' name='addRole' value='" . _AM_XHELP_BUTTON_CREATE_ROLE . "' class='formButton'>
                  <input type='submit' name='clearRoles' value='" . _AM_XHELP_BUTTON_CLEAR_PERMS . "' class='formButton'>
                  </td></tr>";
            echo '</table></form>';

            echo "<form method='post' id='cleanStaff' name='cleanStaff' action='staff.php?op=clearOrphanedStaff'>";
            echo "<table width='100%' cellspacing='1' class='outer'>
                  <tr><th colspan='2'>" . _AM_XHELP_TEXT_MAINTENANCE . '</th></tr>';
            echo "<tr><td class='head' width='40%'>" . _AM_XHELP_TEXT_ORPHANED . "</td>
                      <td class='even'><input type='submit' name='cleanStaff' value='" . _AM_XHELP_BUTTON_SUBMIT . "'></td>
                  </tr>";
            echo '</table></form>';

            if ($staff_count > 0) {
                //Get User Information for each staff member
                $staff_uids = [];
                foreach ($staff_obj as $obj) {
                    $staff_uids[] = $obj->getVar('uid');
                }
                if (false !== $staff_search) {
                    $criteria = new \CriteriaCompo(new \Criteria('uname', "%$staff_search%", 'LIKE'), 'OR');
                    $criteria->add(new \Criteria('name', "%$staff_search%", 'LIKE'), 'OR');
                    $criteria->add(new \Criteria('email', "%$staff_search%", 'LIKE'), 'OR');
                } else {
                    $criteria = new \Criteria('uid', '(' . implode(',', $staff_uids) . ')', 'IN');
                }
                $staff_users = $memberHandler->getUsers($criteria);

                if (false !== $dept_search) {
                    $criteria = new \Criteria('department', "%$dept_search%", 'LIKE');
                } else {
                    $criteria = new \Criteria('', '');
                }
                $criteria->setStart($dstart);
                $criteria->setLimit($dlimit);

                $allDepts = $departmentHandler->getObjects($criteria, true);
                $dnav     = new Xhelp\PageNav($departmentHandler->getCount($criteria), $dlimit, $dstart, 'dstart', "op=manageStaff&amp;start=$start&amp;limit=$limit&amp;dlimit=$dlimit", 'tblManageStaff');

                echo "<form action='" . XHELP_ADMIN_URL . "/staff.php?op=manageStaff' style='margin:0; padding:0;' method='post'>";
                echo $GLOBALS['xoopsSecurity']->getTokenHTML();
                echo "<table width='100%' cellspacing='1' class='outer'>";
                echo "<tr><td align='right'>" . _AM_XHELP_TEXT_STAFF . ': ' . _AM_XHELP_BUTTON_SEARCH . " <input type='text' name='staff_search' value='$staff_search'>
                          " . _AM_XHELP_TEXT_NUMBER_PER_PAGE . "<select name='limit'>";
                foreach ($aLimitByS as $value => $text) {
                    ($limit == $value) ? $selected = 'selected' : $selected = '';
                    echo "<option value='$value' $selected>$text</option>";
                }
                echo '</select>
                            &nbsp;&nbsp;&nbsp;
                            ' . _AM_XHELP_TEXT_DEPARTMENTS . ': ' . _AM_XHELP_BUTTON_SEARCH . "
                              <input type='text' name='dept_search' value='$dept_search'>
                            " . _AM_XHELP_TEXT_NUMBER_PER_PAGE . "
                              <select name='dlimit'>";
                foreach ($aLimitByD as $value => $text) {
                    ($dlimit == $value) ? $selected = 'selected' : $selected = '';
                    echo "<option value='$value' $selected>$text</option>";
                }
                echo "</select>
                            &nbsp;&nbsp;
                              <input type='submit' name='staff_select' id='staff_select' value='" . _AM_XHELP_BUTTON_SUBMIT . "'>
                          </td>
                      </tr>";
                echo '</table></form>';

                echo "<table width='100%' cellspacing='1' class='outer' id='tblManageStaff'>
                      <tr><th colspan='" . (3 + count($allDepts)) . "'><label>" . _AM_XHELP_MANAGE_STAFF . '</label></th></tr>';
                echo "<tr class='head'><td rowspan='2'>" . _AM_XHELP_TEXT_ID . "</td><td rowspan='2'>" . _AM_XHELP_TEXT_USER . "</td><td colspan='" . count($allDepts) . "'>" . _AM_XHELP_TEXT_DEPARTMENTS . ' ' . $dnav->renderNav() . "</td><td rowspan='2'>" . _AM_XHELP_TEXT_ACTIONS . '</td></tr>';
                echo "<tr class='head'>";
                foreach ($allDepts as $thisdept) {
                    echo '<td>' . $thisdept->getVar('department') . '</td>';
                }
                echo '</tr>';
                /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
                /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
                $membershipHandler = $helper->getHandler('Membership');
                $staffRoleHandler  = $helper->getHandler('StaffRole');
                foreach ($staff_users as $staff) {
                    $departments = $membershipHandler->membershipByStaff($staff->getVar('uid'), true);
                    echo "<tr class='even'><td>" . $staff->getVar('uid') . '</td><td>' . $staff->getVar('uname') . '</td>';
                    foreach ($allDepts as $thisdept) {
                        echo "<td><img src='" . XOOPS_URL . '/modules/xhelp/assets/images/';
                        echo array_key_exists($thisdept->getVar('id'), $departments) ? 'on' : 'off';
                        echo ".png'></td>";
                    }
                    echo "<td><a href='staff.php?op=editStaff&amp;uid=" . $staff->getVar('uid') . "'><img src='" . XOOPS_URL . "/modules/xhelp/assets/images/button_edit.png' title='" . _AM_XHELP_TEXT_EDIT . "' name='editStaff'></a>&nbsp;
                              <a href='delete.php?deleteStaff=1&amp;uid=" . $staff->getVar('uid') . "'><img src='" . XOOPS_URL . "/modules/xhelp/assets/images/button_delete.png' title='" . _AM_XHELP_TEXT_DELETE . "' name='deleteStaff'></a>
                          </td></tr>";
                }
                echo '</table><br>';
                echo "<div id='staff_nav'>" . $nav->renderNav() . '</div>';
            }
        } else {
            echo "<div id='readOnly' class='errorMsg'>";
            echo _AM_XHELP_TEXT_MAKE_DEPTS;
            echo '</div>';
            echo "<br><a href='department.php?op=manageDepartments'>" . _AM_XHELP_LINK_ADD_DEPT . '</a>';
        }

        require_once __DIR__ . '/admin_footer.php';
    }//end if
}
