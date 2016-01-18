<?php
//$Id: department.php,v 1.22 2005/11/29 17:48:12 ackbarr Exp $
include('../../../include/cp_header.php');
include_once('admin_header.php');
include_once(XOOPS_ROOT_PATH . '/class/pagenav.php');
require_once(XHELP_CLASS_PATH . '/xhelpForm.php');
require_once(XHELP_CLASS_PATH . '/xhelpFormRadio.php');
require_once(XHELP_CLASS_PATH . '/xhelpFormCheckbox.php');

global $xoopsModule;
$module_id = $xoopsModule->getVar('mid');

$start = $limit = 0;
if (isset($_REQUEST['limit'])) {
    $limit = intval($_REQUEST['limit']);
}
if (isset($_REQUEST['start'])) {
    $start = intval($_REQUEST['start']);
}
if (!$limit) {
    $limit = 15;
}
if(isset($_REQUEST['order'])){
    $order = $_REQUEST['order'];
} else {
    $order = "ASC";
}
if(isset($_REQUEST['sort'])) {
    $sort = $_REQUEST['sort'];
} else {
    $sort = "department";
}
$dept_search = false;
if(isset($_REQUEST['dept_search'])) {
    $dept_search = $_REQUEST['dept_search'];
}

$aSortBy = array('id' => _AM_XHELP_TEXT_ID, 'department' => _AM_XHELP_TEXT_DEPARTMENT);
$aOrderBy = array('ASC' => _AM_XHELP_TEXT_ASCENDING, 'DESC' => _AM_XHELP_TEXT_DESCENDING);
$aLimitBy = array('10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100);

$op = 'default';

if ( isset( $_REQUEST['op'] ) )
{
    $op = $_REQUEST['op'];
}

switch ( $op )
{
    case "activateMailbox":
        activateMailbox();
        break;

    case "AddDepartmentServer":
        addDepartmentServer();
        break;

    case "DeleteDepartmentServer":
        DeleteDepartmentServer();
        break;

    case "deleteStaffDept":
        deleteStaffDept();
        break;

    case "editDepartment":
        editDepartment();
        break;

    case "EditDepartmentServer":
        EditDepartmentServer();
        break;

    case "manageDepartments":
        manageDepartments();
        break;

    case "testMailbox":
        testMailbox();
        break;

    case "clearAddSession":
        clearAddSession();
        break;

    case "clearEditSession":
        clearEditSession();
        break;

    case "updateDefault":
        updateDefault();
        break;

    default:
        header("Location: ".XHELP_BASE_URL."/admin/index.php");
        break;
}

function activateMailbox()
{
    $id = intval($_GET['id']);
    $setstate = intval($_GET['setstate']);

    $hMailbox =& xhelpGetHandler('departmentMailBox');
    if ($mailbox =& $hMailbox->get($id)) {
        $url = XHELP_BASE_URL.'/admin/department.php?op=editDepartment&id='. $mailbox->getVar('departmentid');
        $mailbox->setVar('active', $setstate);
        if ($hMailbox->insert($mailbox, true)) {
            header("Location: $url");
        } else {
            redirect_header($url, 3, _AM_XHELP_DEPARTMENT_SERVER_ERROR);
        }
    } else {
        redirect_header(XHELP_BASE_URL.'/admin/department.php?op=manageDepartments', 3, _XHELP_NO_MAILBOX_ERROR);
    }
}

function addDepartmentServer()
{
    if(isset($_GET['id'])){
        $deptID = intval($_GET['id']);
    } else {
        redirect_header(XHELP_ADMIN_URL."/department.php?op=manageDepartments", 3, _AM_XHELP_DEPARTMENT_NO_ID);
    }

    $hDeptServers =& xhelpGetHandler('departmentMailBox');
    $server = $hDeptServers->create();
    $server->setVar('departmentid',$deptID);
    $server->setVar('emailaddress', $_POST['emailaddress']);
    $server->setVar('server',       $_POST['server']);
    $server->setVar('serverport',   $_POST['port']);
    $server->setVar('username',     $_POST['username']);
    $server->setVar('password',     $_POST['password']);
    $server->setVar('priority',     $_POST['priority']);
    //
    if ($hDeptServers->insert($server))   {
        header("Location: ".XHELP_ADMIN_URL."/department.php?op=manageDepartments");
    } else {
        redirect_header(XHELP_ADMIN_URL.'/department.php?op=manageDepartments', 3, _AM_XHELP_DEPARTMENT_SERVER_ERROR);
    }
}

function DeleteDepartmentServer() {

    if(isset($_REQUEST['id'])){
        $emailID = intval($_REQUEST['id']);
    } else {
        redirect_header(XHELP_ADMIN_URL.'/department.php?op=manageDepartments', 3, _AM_XHELP_DEPARTMENT_SERVER_NO_ID);
    }

    $hDeptServers =& xhelpGetHandler('departmentMailBox');
    $server       =& $hDeptServers->get($emailID);

    if (!isset($_POST['ok'])) {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manDept');
        $indexAdmin = new ModuleAdmin();
        echo $indexAdmin->addNavigation('department.php');

        xoops_confirm(array('op' => 'DeleteDepartmentServer', 'id' => $emailID, 'ok' => 1), XHELP_BASE_URL .'/admin/department.php', sprintf(_AM_XHELP_MSG_DEPT_MBOX_DEL_CFRM, $server->getVar('emailaddress')));
        xoops_cp_footer();
    } else {
        //get handler
        if ($hDeptServers->delete($server,true)) {
            header("Location: ".XHELP_ADMIN_URL."/department.php?op=manageDepartments");
        } else {
            redirect_header(XHELP_ADMIN_URL.'/department.php?op=manageDepartments', 3, _AM_XHELP_DEPARTMENT_SERVER_DELETE_ERROR);
        }
    }
}

function deleteStaffDept()
{
    if(isset($_GET['deptid'])){
        $deptID = intval($_GET['deptid']);
    } else {
        redirect_header(XHELP_ADMIN_URL."/department.php?op=manageDepartments", 3, _AM_XHELP_MSG_NO_DEPTID);
    }
    if(isset($_GET['uid'])){
        $staffID = intval($_GET['uid']);
    } elseif(isset($_POST['staff'])){
        $staffID = $_POST['staff'];
    } else {
        redirect_header(XHELP_ADMIN_URL."/department.php?op=editDepartment&deptid=$deptID", 3, _AM_XHELP_MSG_NO_UID);
    }

    $hMembership =& xhelpGetHandler('membership');
    if(is_array($staffID)){
        foreach($staffID as $sid){
            $ret = $hMembership->removeDeptFromStaff($deptID, $sid);
        }
    } else {
        $ret = $hMembership->removeDeptFromStaff($deptID, $staffID);
    }

    if($ret){
        header("Location: ".XHELP_ADMIN_URL."/department.php?op=editDepartment&deptid=$deptID");
    } else {
        redirect_header(XHELP_ADMIN_URL."/department.php??op=editDepartment&deptid=$deptID", 3, _AM_XHELP_MSG_REMOVE_STAFF_DEPT_ERR);
    }
}

function editDepartment()
{
    $_xhelpSession = Session::singleton();
    global $imagearray, $xoopsModule, $limit, $start, $xoopsModuleConfig;
    $module_id = $xoopsModule->getVar('mid');
    $displayName =& $xoopsModuleConfig['xhelp_displayName'];    // Determines if username or real name is displayed

    $_xhelpSession->set("xhelp_return_page", substr(strstr($_SERVER['REQUEST_URI'], 'admin/'), 6));

    if(isset($_REQUEST["deptid"])){
        $deptID = $_REQUEST['deptid'];
    } else {
        redirect_header(XHELP_ADMIN_URL."/department.php?op=manageDepartments", 3, _AM_XHELP_MSG_NO_DEPTID);
    }

    $hDepartments  =& xhelpGetHandler('department');
    $hGroups =& xoops_gethandler('group');
    $hGroupPerm =& xoops_gethandler('groupperm');

    if(isset($_POST['updateDept'])){
        $groups = (isset($_POST['groups']) ? $_POST['groups'] : array());

        $hasErrors = false;
        //Department Name supplied?
        if (trim($_POST['newDept']) == '') {
            $hasErrors = true;
            $errors['newDept'][] = _AM_XHELP_MESSAGE_NO_DEPT;
        } else {

            //Department Name unique?
            $crit = new CriteriaCompo(new Criteria('department', $_POST['newDept']));
            $crit->add(new Criteria('id', $deptID, '!='));
            if($existingDepts = $hDepartments->getCount($crit)){
                $hasErrors = true;
                $errors['newDept'][] = _XHELP_MESSAGE_DEPT_EXISTS;

            }
        }

        if ($hasErrors) {
            $session =& Session::singleton();
            //Store existing dept info in session, reload addition page
            $aDept = array();
            $aDept['newDept'] = $_POST['newDept'];
            $aDept['groups'] = $groups;
            $session->set("xhelp_editDepartment_$deptID", $aDept);
            $session->set("xhelp_editDepartmentErrors_$deptID", $errors);
            header('Location: '. xhelpMakeURI(XHELP_ADMIN_URL.'/department.php', array('op'=>'editDepartment', 'deptid'=>$deptID), false));
            exit();
        }

        $dept =& $hDepartments->get($deptID);

        $oldDept = $dept;
        $groups = $_POST['groups'];

        // Need to remove old group permissions first
        $crit = new CriteriaCompo(new Criteria('gperm_modid', $module_id));
        $crit->add(new Criteria('gperm_itemid', $deptID));
        $crit->add(new Criteria('gperm_name', _XHELP_GROUP_PERM_DEPT));
        $hGroupPerm->deleteAll($crit);

        foreach($groups as $group){     // Add new group permissions
            $hGroupPerm->addRight(_XHELP_GROUP_PERM_DEPT, $deptID, $group, $module_id);
        }

        $dept->setVar('department', $_POST['newDept']);

        if($hDepartments->insert($dept)){
            $message = _XHELP_MESSAGE_UPDATE_DEPT;

            // Update default dept
            if(isset($_POST['defaultDept']) && ($_POST['defaultDept'] == 1)){
                xhelpSetMeta("default_department", $dept->getVar('id'));
            } else {
                $depts =& $hDepartments->getObjects();
                $aDepts = array();
                foreach($depts as $dpt){
                    $aDepts[] = $dpt->getVar('id');
                }
                xhelpSetMeta("default_department", $aDepts[0]);
            }

            // Edit configoption for department
            $hConfigOption =& xoops_gethandler('configoption');
            $crit = new CriteriaCompo(new Criteria('confop_name', $oldDept->getVar('department')));
            $crit->add(new Criteria('confop_value', $oldDept->getVar('id')));
            $confOption =& $hConfigOption->getObjects($crit);

            if(count($confOption) > 0){
                $confOption[0]->setVar('confop_name', $dept->getVar('department'));

                if(!$hConfigOption->insert($confOption[0])){
                    redirect_header(XHELP_ADMIN_URL."/department.php?op=manageDepartments", 3, _AM_XHELP_MSG_UPDATE_CONFIG_ERR);
                }
            }
            _clearEditSessionVars($deptID);
            header("Location: ".XHELP_ADMIN_URL."/department.php?op=manageDepartments");
        } else {
            $message = _XHELP_MESSAGE_UPDATE_DEPT_ERROR . $dept->getHtmlErrors();
            redirect_header(XHELP_ADMIN_URL."/department.php?op=manageDepartments", 3, $message);
        }

    } else {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manDept');

        $dept =& $hDepartments->get($deptID);

        $session =& Session::singleton();
        $sess_dept = $session->get("xhelp_editDepartment_$deptID");
        $sess_errors = $session->get("xhelp_editDepartmentErrors_$deptID");

        //Display any form errors
        if (! $sess_errors === false) {
            xhelpRenderErrors($sess_errors, xhelpMakeURI(XHELP_ADMIN_URL.'/department.php', array('op'=>'clearEditSession', 'deptid'=>$deptID)));
        }

        $indexAdmin = new ModuleAdmin();
        echo $indexAdmin->addNavigation('department.php?op=editDepartment');

        // Get list of groups with permission
        $crit = new CriteriaCompo(new Criteria('gperm_modid', $module_id));
        $crit->add(new Criteria('gperm_itemid', $deptID));
        $crit->add(new Criteria('gperm_name', _XHELP_GROUP_PERM_DEPT));
        $group_perms =& $hGroupPerm->getObjects($crit);

        $aPerms = array();      // Put group_perms in usable format
        foreach($group_perms as $perm){
            $aPerms[$perm->getVar('gperm_groupid')] = $perm->getVar('gperm_groupid');
        }

        if (! $sess_dept === false) {
            $fld_newDept = $sess_dept['newDept'];
            $fld_groups  = $sess_dept['groups'];
        } else {
            $fld_newDept = $dept->getVar('department');
            $fld_groups = $aPerms;
        }

        // Get list of all groups
        $crit = new Criteria('', '');
        $crit->setSort('name');
        $crit->setOrder('ASC');
        $groups =& $hGroups->getObjects($crit, true);

        $aGroups = array();
        foreach($groups as $group_id=>$group){
            $aGroups[$group_id] = $group->getVar('name');
        }
        asort($aGroups);    // Set groups in alphabetical order

        echo '<script type="text/javascript" src="'.XOOPS_URL.'/modules/xhelp/include/functions.js"></script>';
        $form = new xhelpForm(_AM_XHELP_EDIT_DEPARTMENT, 'edit_dept', xhelpMakeURI(XHELP_ADMIN_URL.'/department.php', array('op'=>'editDepartment', 'deptid' => $deptID)));
        $dept_name = new XoopsFormText(_AM_XHELP_TEXT_EDIT_DEPT, 'newDept', 20, 35, $fld_newDept);
        $group_select = new XoopsFormSelect(_AM_XHELP_TEXT_EDIT_DEPT_PERMS, 'groups', $fld_groups, 6, true);
        $group_select->addOptionArray($aGroups);
        $defaultDeptID = xhelpGetMeta("default_department");
        $defaultDept = new xhelpFormCheckbox(_AM_XHELP_TEXT_DEFAULT_DEPT, 'defaultDept', (($defaultDeptID == $deptID) ? 1 : 0), 'defaultDept');
        $defaultDept->addOption(1, "");
        $btn_tray = new XoopsFormElementTray('');
        $btn_tray->addElement(new XoopsFormButton('', 'updateDept', _AM_XHELP_BUTTON_SUBMIT, 'submit'));
        $form->addElement($dept_name);
        $form->addElement($group_select);
        $form->addElement($defaultDept);
        $form->addElement($btn_tray);
        $form->setLabelWidth('20%');
        echo $form->render();

        // Get dept staff members
        $hMembership =& xhelpGetHandler('membership');
        $hMember =& xoops_gethandler('member');
        $hStaffRole =& xhelpGetHandler('staffRole');
        $hRole =& xhelpGetHandler('role');

        $staff = $hMembership->membershipByDept($deptID, $limit, $start);
        $crit = new Criteria('j.department', $deptID);
        $staffCount =& $hMembership->getCount($crit);
        $roles =& $hRole->getObjects(null, true);

        echo "<form action='".XHELP_ADMIN_URL."/department.php?op=deleteStaffDept&amp;deptid=".$deptID."' method='post'>";
        echo "<table width='100%' cellspacing='1' class='outer'>
              <tr><th colspan='".(3+count($roles))."'><label>". _AM_XHELP_MANAGE_STAFF ."</label></th></tr>";

        if($staffCount > 0){
            $aStaff = array();
            foreach($staff as $stf){
                $aStaff[$stf->getVar('uid')] = $stf->getVar('uid');     // Get array of staff uid
            }

            // Get user list
            $crit = new Criteria('uid', "(". implode($aStaff, ',') .")", "IN");
            //$members =& $hMember->getUserList($crit);
            $members =& xhelpGetUsers($crit, $displayName);

            // Get staff roles
            $crit = new CriteriaCompo(new Criteria('uid', "(". implode($aStaff, ',') .")", "IN"));
            $crit->add(new Criteria('deptid', $deptID));
            $staffRoles =& $hStaffRole->getObjects($crit);
            unset($aStaff);

            $staffInfo = array();
            foreach($staff as $stf){
                $staff_uid = $stf->getVar('uid');
                $staffInfo[$staff_uid]['uname'] = $members[$staff_uid];
                $aRoles = array();
                foreach($staffRoles as $role){
                    $role_id = $role->getVar('roleid');
                    if($role->getVar('uid') == $staff_uid){
                        $aRoles[$role_id] = $roles[$role_id]->getVar('name');
                    }
                    $staffInfo[$staff_uid]['roles'] = implode($aRoles, ', ');
                }
            }
            $nav = new XoopsPageNav($staffCount, $limit, $start, 'start', "op=editDepartment&amp;deptid=$deptID&amp;limit=$limit");

            echo "<tr class='head'><td rowspan='2'>"._AM_XHELP_TEXT_ID."</td><td rowspan='2'>"._AM_XHELP_TEXT_USER."</td><td colspan='".count($roles)."'>"._AM_XHELP_TEXT_ROLES."</td><td rowspan='2'>"._AM_XHELP_TEXT_ACTIONS."</td></tr>";
            echo "<tr class='head'>";
            foreach ($roles as $thisrole) echo "<td>".$thisrole->getVar('name')."</td>";
            echo "</tr>";
            foreach($staffInfo as $uid=>$staff){
                echo "<tr class='even'>
                          <td><input type='checkbox' name='staff[]' value='".$uid."' />".$uid."</td>
                          <td>".$staff['uname']."</td>";
                foreach ($roles as $thisrole) {
                    echo "<td><img src='".XHELP_BASE_URL."/images/";
                    echo (in_array($thisrole->getVar('name'),explode(', ',$staff['roles']))) ? "on.png" : "off.png";
                    echo "' /></td>";
                }
                echo "    <td>
                          <a href='".XHELP_ADMIN_URL."/staff.php?op=editStaff&amp;uid=".$uid."'><img src='".XOOPS_URL."/modules/xhelp/images/button_edit.png' title='"._AM_XHELP_TEXT_EDIT."' name='editStaff' /></a>&nbsp;
                          <a href='".XHELP_ADMIN_URL."/department.php?op=deleteStaffDept&amp;uid=".$uid."&amp;deptid=".$deptID."'><img src='".XOOPS_URL."/modules/xhelp/images/button_delete.png' title='"._AM_XHELP_TEXT_DELETE_STAFF_DEPT."' name='deleteStaffDept' /></a>
                      </td>
                  </tr>";
            }
            echo "<tr>
                      <td class='foot' colspan='".(3+count($roles))."'>
                          <input type='checkbox' name='checkallRoles' value='0' onclick='selectAll(this.form,\"staff[]\",this.checked);' />
                          <input type='submit' name='deleteStaff' id='deleteStaff' value='"._AM_XHELP_BUTTON_DELETE."' />
                      </td>
                  </tr>";
            echo "</table></form>";
            echo "<div id='staff_nav'>".$nav->renderNav()."</div>";
        } else {
            echo "</table></form>";
        }

        //now do the list of servers
        $hDeptServers =& xhelpGetHandler('departmentMailBox');
        $deptServers  =& $hDeptServers->getByDepartment($deptID);
        //iterate
        if (count($deptServers) > 0) {
            echo "<br /><table width='100%' cellspacing='1' class='outer'>
               <tr>
                 <th colspan='5'><label>". _AM_XHELP_DEPARTMENT_SERVERS ."</label></th>
               </tr>
               <tr>
                 <td class='head' width='20%'><label>". _AM_XHELP_DEPARTMENT_SERVERS_EMAIL ."</label></td>
                 <td class='head'><label>". _AM_XHELP_DEPARTMENT_SERVERS_TYPE ."</label></td>
                 <td class='head'><label>". _AM_XHELP_DEPARTMENT_SERVERS_SERVERNAME ."</label></td>
                 <td class='head'><label>". _AM_XHELP_DEPARTMENT_SERVERS_PORT ."</label></td>
                 <td class='head'><label>". _AM_XHELP_DEPARTMENT_SERVERS_ACTION ."</label></td>
               </tr>";
            $i = 0;
            foreach($deptServers as $server){
                if ($server->getVar('active')) {
                    $activ_link = '".XHELP_ADMIN_URL."/department.php?op=activateMailbox&amp;setstate=0&amp;id='. $server->getVar('id');
                    $activ_img = $imagearray['online'];
                    $activ_title = _AM_XHELP_MESSAGE_DEACTIVATE;
                } else {
                    $activ_link = '".XHELP_ADMIN_URL."/department.php?op=activateMailbox&amp;setstate=1&amp;id='. $server->getVar('id');
                    $activ_img = $imagearray['offline'];
                    $activ_title = _AM_XHELP_MESSAGE_ACTIVATE;
                }

                echo '<tr class="even">
                   <td>'.$server->getVar('emailaddress').'</td>
                   <td>'.xhelpGetMBoxType($server->getVar('mboxtype')).'</td>
                   <td>'.$server->getVar('server').'</td>
                   <td>'.$server->getVar('serverport').'</td>
                   <td> <a href="'. $activ_link.'" title="'. $activ_title.'">'. $activ_img.'</a>
                        <a href="'.XHELP_ADMIN_URL.'/department.php?op=EditDepartmentServer&amp;id='.$server->GetVar('id').'">'.$imagearray['editimg'].'</a>
                        <a href="'.XHELP_ADMIN_URL.'/department.php?op=DeleteDepartmentServer&amp;id='.$server->GetVar('id').'">'.$imagearray['deleteimg'].'</a>

                   </td>
                 </tr>';
            }
            echo '</table>';
        }
        //finally add Mailbox form
        echo "<br /><br />";

        $formElements = array('type_select', 'server_text', 'port_text', 'username_text', 'pass_text', 'priority_radio',
                              'email_text', 'btn_tray');
        $form = new xhelpForm(_AM_XHELP_DEPARTMENT_ADD_SERVER, 'add_server', xhelpMakeURI(XHELP_ADMIN_URL.'/department.php', array('op'=>'AddDepartmentServer', 'id' => $deptID)));

        $type_select = new XoopsFormSelect(_AM_XHELP_DEPARTMENT_SERVERS_TYPE, 'mboxtype');
        $type_select->setExtra("id='mboxtype'");
        $type_select->addOption(_XHELP_MAILBOXTYPE_POP3, _AM_XHELP_MBOX_POP3);

        $server_text = new XoopsFormText(_AM_XHELP_DEPARTMENT_SERVERS_SERVERNAME, 'server', 40, 50);
        $server_text->setExtra("id='txtServer'");

        $port_text = new XoopsFormText(_AM_XHELP_DEPARTMENT_SERVERS_PORT, 'port', 5, 5, "110");
        $port_text->setExtra("id='txtPort'");

        $username_text = new XoopsFormText(_AM_XHELP_DEPARTMENT_SERVER_USERNAME, 'username', 25, 50);
        $username_text->setExtra("id='txtUsername'");

        $pass_text = new XoopsFormText(_AM_XHELP_DEPARTMENT_SERVER_PASSWORD, 'password', 25, 50);
        $pass_text->setExtra("id='txtPassword'");

        $priority_radio = new xhelpFormRadio(_AM_XHELP_DEPARTMENT_SERVERS_PRIORITY, 'priority', XHELP_DEFAULT_PRIORITY);
        $priority_array = array('1' => "<label for='priority1'><img src='".XHELP_IMAGE_URL."/priority1.png' title='". xhelpGetPriority(1)."' alt='priority1' /></label>",
                                '2' => "<label for='priority2'><img src='".XHELP_IMAGE_URL."/priority2.png' title='". xhelpGetPriority(2)."' alt='priority2' /></label>",
                                '3' => "<label for='priority3'><img src='".XHELP_IMAGE_URL."/priority3.png' title='". xhelpGetPriority(3)."' alt='priority3' /></label>",
                                '4' => "<label for='priority4'><img src='".XHELP_IMAGE_URL."/priority4.png' title='". xhelpGetPriority(4)."' alt='priority4' /></label>",
                                '5' => "<label for='priority5'><img src='".XHELP_IMAGE_URL."/priority5.png' title='". xhelpGetPriority(5)."' alt='priority5' /></label>");
        $priority_radio->addOptionArray($priority_array);

        $email_text = new XoopsFormText(_AM_XHELP_DEPARTMENT_SERVER_EMAILADDRESS, 'emailaddress', 50, 255);
        $email_text->setExtra("id='txtEmailaddress'");

        $btn_tray = new XoopsFormElementTray('');
        $test_button = new XoopsFormButton('', 'email_test', _AM_XHELP_BUTTON_TEST, 'button');
        $test_button->setExtra("id='test'");
        $submit_button = new XoopsFormButton('', 'updateDept2', _AM_XHELP_BUTTON_SUBMIT, 'submit');
        $cancel2_button = new XoopsFormButton('', 'cancel2', _AM_XHELP_BUTTON_CANCEL, 'button');
        $cancel2_button->setExtra("onclick='history.go(-1)'");
        $btn_tray->addElement($test_button);
        $btn_tray->addElement($submit_button);
        $btn_tray->addElement($cancel2_button);

        $form->setLabelWidth('20%');
        foreach($formElements as $element){
            $form->addElement($$element);
        }
        echo $form->render();

        echo "<script type=\"text/javascript\" language=\"javascript\">
          <!--
          function xhelpEmailTest()
          {
            pop = openWithSelfMain(\"\", \"email_test\", 250, 150);
            frm = xoopsGetElementById(\"add_server\");
            newaction = \"department.php?op=testMailbox\";
            oldaction = frm.action;
            frm.action = newaction;
            frm.target = \"email_test\";
            frm.submit();
            frm.action = oldaction;
            frm.target = \"main\";

          }

          xhelpDOMAddEvent(xoopsGetElementById(\"email_test\"), \"click\", xhelpEmailTest, false);

          //-->
          </script>";
include_once "admin_footer.php";
    }
}

function EditDepartmentServer()
{
    if(isset($_GET['id'])){
        $id = intval($_GET['id']);
    } else {
        redirect_header(XHELP_ADMIN_URL."/department.php?op=manageDepartments", 3);       // TODO: Make message for no mbox_id
    }

    $hDeptServers =& xhelpGetHandler('departmentMailBox');
    $deptServer =& $hDeptServers->get($id);

    if(isset($_POST['updateMailbox'])){
        $deptServer->setVar('emailaddress', $_POST['emailaddress']);
        $deptServer->setVar('server',       $_POST['server']);
        $deptServer->setVar('serverport',   $_POST['port']);
        $deptServer->setVar('username',     $_POST['username']);
        $deptServer->setVar('password',     $_POST['password']);
        $deptServer->setVar('priority',     $_POST['priority']);
        $deptServer->setVar('active',       $_POST['activity']);

        if($hDeptServers->insert($deptServer)){
            header("Location: ".XHELP_ADMIN_URL."/department.php?op=editDepartment&deptid=".$deptServer->getVar('departmentid'));
        } else {
            redirect_header(XHELP_ADMIN_URL."/department.php?op=editDepartment&deptid=".$deptServer->getVar('departmentid'),3);
        }
    } else {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manDept');
        $indexAdmin = new ModuleAdmin();
        echo $indexAdmin->addNavigation('department.php');
        echo '<script type="text/javascript" src="'.XOOPS_URL.'/modules/xhelp/include/functions.js"></script>';
        echo "<form method='post' id='edit_server' action='department.php?op=EditDepartmentServer&amp;id=".$id."'>
               <table width='100%' cellspacing='1' class='outer'>
                 <tr>
                   <th colspan='2'><label>". _AM_XHELP_DEPARTMENT_EDIT_SERVER ."</label></th>
                 </tr>
                 <tr>
                   <td class='head' width='20%'><label for='mboxtype'>"._AM_XHELP_DEPARTMENT_SERVERS_TYPE."</label></td>
                   <td class='even'>
                     <select name='mboxtype' id='mboxtype' onchange='xhelpPortOnChange(this.options[this.selectedIndex].text, \"txtPort\")'>
                       <option value='"._XHELP_MAILBOXTYPE_POP3."'>"._AM_XHELP_MBOX_POP3."</option>
                       <!--<option value='"._XHELP_MAILBOXTYPE_IMAP."'>"._AM_XHELP_MBOX_IMAP."</option>-->
                     </select>
                   </td>
                 </tr>
                 <tr>
                   <td class='head'><label for='txtServer'>"._AM_XHELP_DEPARTMENT_SERVERS_SERVERNAME."</label></td>
                   <td class='even'><input type='text' id='txtServer' name='server' value='".$deptServer->getVar('server')."' size='40' maxlength='50' />
                 </tr>
                 <tr>
                   <td class='head'><label for='txtPort'>"._AM_XHELP_DEPARTMENT_SERVERS_PORT."</label></td>
                   <td class='even'><input type='text' id='txtPort' name='port' maxlength='5' size='5' value='".$deptServer->getVar('serverport')."' />
                 </tr>
                 <tr>
                   <td class='head'><label for='txtUsername'>"._AM_XHELP_DEPARTMENT_SERVER_USERNAME."</label></td>
                   <td class='even'><input type='text' id='txtUsername' name='username' value='".$deptServer->getVar('username')."' size='25' maxlength='50' />
                 </tr>
                 <tr>
                   <td class='head'><label for='txtPassword'>"._AM_XHELP_DEPARTMENT_SERVER_PASSWORD."</label></td>
                   <td class='even'><input type='text' id='txtPassword' name='password' value='".$deptServer->getVar('password')."' size='25' maxlength='50' />
                 </tr>
                 <tr>
                   <td width='38%' class='head'><label for='txtPriority'>"._AM_XHELP_DEPARTMENT_SERVERS_PRIORITY."</label></td>
                   <td width='62%' class='even'>";
        for($i = 1; $i < 6; $i++) {
            $checked = '';
            if($deptServer->getVar('priority') == $i){
                $checked = 'checked="checked"';
            }
            echo("<input type=\"radio\" value=\"$i\" id=\"priority$i\" name=\"priority\" $checked />");
            echo("<label for=\"priority$i\"><img src=\"../images/priority$i.png\" title=\"". xhelpGetPriority($i)."\" alt=\"priority$i\" /></label>");
        }
        echo "</td>
                 </tr>
                 <tr>
                   <td class='head'><label for='txtEmailaddress'>"._AM_XHELP_DEPARTMENT_SERVER_EMAILADDRESS."</label></td>
                   <td class='even'><input type='text' id='txtEmailaddress' name='emailaddress' value='".$deptServer->getVar('emailaddress')."' size='50' maxlength='255' />
                 </tr>
                 <tr>
                   <td class='head'><label for='txtActive'>"._AM_XHELP_TEXT_ACTIVITY."</label></td>
                   <td class='even'>";
        if($deptServer->getVar('active') == 1){
            echo "<input type='radio' value='1' name='activity' checked='checked' />"._AM_XHELP_TEXT_ACTIVE."
                                      <input type='radio' value='0' name='activity' />"._AM_XHELP_TEXT_INACTIVE;
        } else {
            echo "<input type='radio' value='1' name='activity' />"._AM_XHELP_TEXT_ACTIVE."
                                      <input type='radio' value='0' name='activity' checked='checked' />"._AM_XHELP_TEXT_INACTIVE;
        }

        echo "</td>
                 </tr>

                 <tr class='foot'>
                   <td colspan='2'><div align='right'><span >
                       <input type='button' id='email_test' name='test' value='"._AM_XHELP_BUTTON_TEST."' class='formButton' />
                       <input type='submit' name='updateMailbox' value='"._AM_XHELP_BUTTON_SUBMIT."' class='formButton' />
                       <input type='button' name='cancel' value='"._AM_XHELP_BUTTON_CANCEL."' onclick='history.go(-1)' class='formButton' />
                   </span></div></td>
                 </tr>
               </table>
             </form>";
        echo "<script type=\"text/javascript\" language=\"javascript\">
          <!--
          function xhelpEmailTest()
          {
            pop = openWithSelfMain(\"\", \"email_test\", 250, 150);
            frm = xoopsGetElementById(\"edit_server\");
            newaction = \"department.php?op=testMailbox\";
            oldaction = frm.action;
            frm.action = newaction;
            frm.target = \"email_test\";
            frm.submit();
            frm.action = oldaction;
            frm.target = \"main\";

          }

          xhelpDOMAddEvent(xoopsGetElementById(\"email_test\"), \"click\", xhelpEmailTest, false);

          //-->
          </script>";
include_once "admin_footer.php";
    }
}

function manageDepartments()
{
    global $xoopsModule, $aSortBy, $aOrderBy, $aLimitBy, $order, $limit, $start, $sort, $dept_search;
    $module_id = $xoopsModule->getVar('mid');

    $hGroups =& xoops_gethandler('group');
    $hGroupPerm =& xoops_gethandler('groupperm');

    if(isset($_POST['addDept'])){
        $hasErrors = false;
        $errors = array();
        $groups = (isset($_POST['groups']) ? $_POST['groups'] : array());
        $hDepartments  =& xhelpGetHandler('department');

        //Department Name supplied?
        if (trim($_POST['newDept']) == '') {
            $hasErrors = true;
            $errors['newDept'][] = _AM_XHELP_MESSAGE_NO_DEPT;
        } else {

            //Department Name unique?
            $crit = new Criteria('department', $_POST['newDept']);
            if($existingDepts = $hDepartments->getCount($crit)){
                $hasErrors = true;
                $errors['newDept'][] = _XHELP_MESSAGE_DEPT_EXISTS;

            }
        }

        if ($hasErrors) {
            $session =& Session::singleton();
            //Store existing dept info in session, reload addition page
            $aDept = array();
            $aDept['newDept'] = $_POST['newDept'];
            $aDept['groups'] = $groups;
            $session->set('xhelp_addDepartment', $aDept);
            $session->set('xhelp_addDepartmentErrors', $errors);
            header('Location: '. xhelpMakeURI(XHELP_ADMIN_URL.'/department.php', array('op'=>'manageDepartments'), false));
            exit();
        }

        $department =& $hDepartments->create();
        $department->setVar('department', $_POST['newDept']);

        if($hDepartments->insert($department)){
            $deptID = $department->getVar('id');
            foreach($groups as $group){     // Add new group permissions
                $hGroupPerm->addRight(_XHELP_GROUP_PERM_DEPT, $deptID, $group, $module_id);
            }

            // Set as default department?
            if(isset($_POST['defaultDept']) && ($_POST['defaultDept'] == 1)){
                xhelpSetMeta("default_department", $deptID);
            }

            $hStaff =& xhelpGetHandler('staff');
            $allDeptStaff =& $hStaff->getByAllDepts();
            if (count($allDeptStaff) > 0) {
                $hMembership =& xhelpGetHandler('membership');
                if($hMembership->addStaffToDept($allDeptStaff, $department->getVar('id'))){
                    $message = _XHELP_MESSAGE_ADD_DEPT;
                } else {
                    $message = _AM_XHELP_MESSAGE_STAFF_UPDATE_ERROR;
                }
            } else {
                $message = _XHELP_MESSAGE_ADD_DEPT;
            }

            // Add configoption for new department
            $hConfig =& xoops_gethandler('config');
            $hConfigOption =& xoops_gethandler('configoption');

            $crit = new Criteria('conf_name', 'xhelp_defaultDept');
            $config =& $hConfig->getConfigs($crit);

            if(count($config) > 0){
                $newOption =& $hConfigOption->create();
                $newOption->setVar('confop_name', $department->getVar('department'));
                $newOption->setVar('confop_value', $department->getVar('id'));
                $newOption->setVar('conf_id', $config[0]->getVar('conf_id'));

                if(!$hConfigOption->insert($newOption)){
                    redirect_header(XHELP_ADMIN_URL."/department.php?op=manageDepartments", 3, _AM_XHELP_MSG_ADD_CONFIG_ERR);
                }
            }
            _clearAddSessionVars();
            header("Location: ".XHELP_ADMIN_URL."/department.php?op=manageDepartments");
        } else {
            $message = _XHELP_MESSAGE_ADD_DEPT_ERROR . $department->getHtmlErrors();
        }

        $deptID = $department->getVar('id');

        /* Not sure if this is needed. Already exists in if block above (ej)
         foreach($groups as $group){
         $hGroupPerm->addRight(_XHELP_GROUP_PERM_DEPT, $deptID, $group, $module_id);
         }
         */

        redirect_header(XHELP_ADMIN_URL.'/department.php?op=manageDepartments', 3, $message);
    } else {
        $hDepartments  =& xhelpGetHandler('department');
        if($dept_search == false){
            $crit = new Criteria('','');
        } else {
            $crit = new Criteria('department',"%$dept_search%", 'LIKE');
        }
        $crit->setOrder($order);
        $crit->setSort($sort);
        $crit->setLimit($limit);
        $crit->setStart($start);
        $total = $hDepartments->getCount($crit);
        $departmentInfo =& $hDepartments->getObjects($crit);

        $nav = new XoopsPageNav($total, $limit, $start, 'start', "op=manageDepartments&amp;limit=$limit");

        // Get list of all groups
        $crit = new Criteria('', '');
        $crit->setSort('name');
        $crit->setOrder('ASC');
        $groups =& $hGroups->getObjects($crit, true);

        $aGroups = array();
        foreach($groups as $group_id=>$group){
            $aGroups[$group_id] = $group->getVar('name');
        }
        asort($aGroups);    // Set groups in alphabetical order

        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manDept');
        $indexAdmin = new ModuleAdmin();
        echo $indexAdmin->addNavigation('department.php?op=manageDepartments');

        $session =& Session::singleton();
        $sess_dept = $session->get('xhelp_addDepartment');
        $sess_errors = $session->get('xhelp_addDepartmentErrors');

        //Display any form errors
        if (! $sess_errors === false) {
            xhelpRenderErrors($sess_errors, xhelpMakeURI(XHELP_ADMIN_URL.'/department.php', array('op'=>'clearAddSession'), false));
        }

        if (! $sess_dept === false) {
            $fld_newDept = $sess_dept['newDept'];
            $fld_groups  = $sess_dept['groups'];
        } else {
            $fld_newDept = '';
            $fld_groups = array();
        }

        echo "<form method='post' action='".XHELP_ADMIN_URL."/department.php?op=manageDepartments'>";
        echo "<table width='100%' cellspacing='1' class='outer'>
              <tr><th colspan='2'><label for='newDept'>". _AM_XHELP_LINK_ADD_DEPT ." </label></th></tr>";
        echo "<tr><td class='head' width='20%' valign='top'>". _AM_XHELP_TEXT_NAME ."</td><td class='even'>";
        echo "<input type='text' id='newDept' name='newDept' class='formButton' value='$fld_newDept' /></td></tr>";
        echo "<tr><td class='head' width='20%' valign='top'>"._AM_XHELP_TEXT_EDIT_DEPT_PERMS."</td><td class='even'>";
        echo "<select name='groups[]' multiple='multiple'>";
        foreach($aGroups as $group_id=>$group){
            if (in_array($group_id, $fld_groups, true)) {
                echo "<option value='$group_id' selected='selected'>$group</option>";
            } else {
                echo "<option value='$group_id'>$group</option>";
            }
        }
        echo "</select></td></tr>";
        echo "<tr><td class='head' width='20%' valign='top'>"._AM_XHELP_TEXT_DEFAULT_DEPT."?</td>
                  <td class='even'><input type='checkbox' name='defaultDept' id='defaultDept' value='1' /></td></tr>";
        echo "<tr><td class='foot' colspan='2'><input type='submit' name='addDept' value='"._AM_XHELP_BUTTON_SUBMIT."' class='formButton' /></td></tr>";
        echo "</table><br />";
        echo "</form>";
        if($total > 0){     // Make sure there are departments
            echo "<form action='". XHELP_ADMIN_URL."/department.php?op=manageDepartments' style='margin:0; padding:0;' method='post'>";
            echo "<table width='100%' cellspacing='1' class='outer'>";
            echo "<tr><td align='right'>"._AM_XHELP_BUTTON_SEARCH."
                          <input type='text' name='dept_search' value='$dept_search'/>
                        &nbsp;&nbsp;&nbsp;
                        "._AM_XHELP_TEXT_SORT_BY."
                          <select name='sort'>";
            foreach($aSortBy as $value=>$text){
                ($sort == $value) ? $selected = "selected='selected'" : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            echo "</select>
                        &nbsp;&nbsp;&nbsp;
                          "._AM_XHELP_TEXT_ORDER_BY."
                          <select name='order'>";
            foreach($aOrderBy as $value=>$text){
                ($order == $value) ? $selected = "selected='selected'" : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            echo "</select>
                          &nbsp;&nbsp;&nbsp;
                          "._AM_XHELP_TEXT_NUMBER_PER_PAGE."
                          <select name='limit'>";
            foreach($aLimitBy as $value=>$text){
                ($limit == $value) ? $selected = "selected='selected'" : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            echo "</select>
                          <input type='submit' name='dept_sort' id='dept_sort' value='"._AM_XHELP_BUTTON_SUBMIT."' />
                      </td>
                  </tr>";
            echo "</table></form>";
            echo "<table width='100%' cellspacing='1' class='outer'>
                  <tr><th colspan='4'>"._AM_XHELP_EXISTING_DEPARTMENTS."</th></tr>
                  <tr><td class='head'>"._AM_XHELP_TEXT_ID."</td><td class='head'>"._AM_XHELP_TEXT_DEPARTMENT."</td><td class='head'>"._AM_XHELP_TEXT_DEFAULT."</td><td class='head'>"._AM_XHELP_TEXT_ACTIONS."</td></tr>";

            if(isset($departmentInfo)){
                $defaultDept = xhelpGetMeta("default_department");
                foreach($departmentInfo as $dept){
                    echo "<tr><td class='even'>". $dept->getVar('id')."</td><td class='even'>". $dept->getVar('department') ."</td>";
                    if($dept->getVar('id') != $defaultDept){
                        echo "<td class='even' width='10%'><a href='".XHELP_ADMIN_URL."/department.php?op=updateDefault&amp;id=".$dept->getVar('id')."'><img src='".XHELP_IMAGE_URL."/off.png' alt='"._AM_XHELP_TEXT_MAKE_DEFAULT_DEPT."' title='"._AM_XHELP_TEXT_MAKE_DEFAULT_DEPT."' /></a></td>";
                    } else {
                        echo "<td class='even' width='10%'><img src='".XHELP_IMAGE_URL."/on.png'</td>";
                    }
                    //echo "<td class='even' width='10%'><img src='".XHELP_IMAGE_URL."/". (($dept->getVar('id') == $defaultDept) ? "on.png" : "off.png")."'</td>";
                    echo "<td class='even' width='70'><a href='".XHELP_ADMIN_URL."/department.php?op=editDepartment&amp;deptid=".$dept->getVar('id')."'><img src='".XOOPS_URL."/modules/xhelp/images/button_edit.png' title='"._AM_XHELP_TEXT_EDIT."' name='editDepartment' /></a>&nbsp;&nbsp;";
                    echo "<a href='".XHELP_ADMIN_URL."/delete.php?deleteDept=1&amp;deptid=".$dept->getVar('id')."'><img src='".XOOPS_URL."/modules/xhelp/images/button_delete.png' title='"._AM_XHELP_TEXT_DELETE."' name='deleteDepartment' /></a></td></tr>";
                }

            }
        }
        echo "</td></tr></table>";
        echo "<div id='dept_nav'>".$nav->renderNav()."</div>";
include_once "admin_footer.php";
    }
}

function testMailbox()
{
    $hDeptServers =& xhelpGetHandler('departmentMailBox');
    $server = $hDeptServers->create();
    $server->setVar('emailaddress', $_POST['emailaddress']);
    $server->setVar('server',       $_POST['server']);
    $server->setVar('serverport',   $_POST['port']);
    $server->setVar('username',     $_POST['username']);
    $server->setVar('password',     $_POST['password']);
    $server->setVar('priority',     $_POST['priority']);
    echo "<html>";
    echo "<head>";
    echo "<link rel='stylesheet' type='text/css' media'screen' href='".XOOPS_URL."/xoops.css' />
          <link rel='stylesheet' type='text/css' media='screen' href='". xoops_getcss() ."' />
          <link rel='stylesheet' type='text/css' media='screen' href='".XOOPS_URL."/modules/system/style.css' />";
    echo "</head>";
    echo "<body>";
    echo "<table style='margin:0; padding:0;' class='outer'>";
    if (@$server->connect()) {
        //Connection Succeeded
        echo "<tr><td class='head'>Connection Successful!</td></tr>";
    } else {
        //Connection Failed
        echo "<tr class='head'><td>Connection Failed!</td></tr>";
        echo "<tr class='even'><td>". $server->getHtmlErrors()."</td></tr>";
    }
    echo "</table>";
    echo "</body>";
    echo "</html>";
}

function clearAddSession()
{
    _clearAddSessionVars();
    header('Location: ' . xhelpMakeURI(XHELP_ADMIN_URL.'/department.php', array('op'=>'manageDepartments'), false));
}

function _clearAddSessionVars()
{
    $session = Session::singleton();
    $session->del('xhelp_addDepartment');
    $session->del('xhelp_addDepartmentErrors');
}

function clearEditSession()
{
    $deptid = $_REQUEST['deptid'];
    _clearEditSessionVars($deptid);
    header('Location: ' . xhelpMakeURI(XHELP_ADMIN_URL.'/department.php', array('op'=>'editDepartment', 'deptid'=>$deptid), false));
}

function _clearEditSessionVars($id)
{
    $id = intval($id);
    $session = Session::singleton();
    $session->del("xhelp_editDepartment_$id");
    $session->del("xhelp_editDepartmentErrors_$id");
}

function updateDefault()
{
    $id = intval($_REQUEST['id']);
    xhelpSetMeta("default_department", $id);
    header('Location: '. xhelpMakeURI(XHELP_ADMIN_URL.'/department.php', array('op'=>'manageDepartments'), false));
}
