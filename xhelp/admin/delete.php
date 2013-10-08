<?php
//$Id: delete.php,v 1.24 2005/12/01 22:36:21 ackbarr Exp $
//require_once('header.php');
require('../../../include/cp_header.php');
require_once('admin_header.php');

global $xoopsUser;
$uid = $xoopsUser->getVar('uid');

if(isset($_REQUEST['deleteDept'])){
    if (isset( $_REQUEST['deptid'])){
        $deptID = $_REQUEST['deptid'];
    } else {
        redirect_header(XHELP_ADMIN_URL."/department.php?op=manageDepartments", 3, _AM_XHELP_MESSAGE_NO_DEPT);
    }

    if (!isset($_POST['ok'])) {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manDept');
        xoops_confirm(array('deleteDept' => 1, 'deptid' => $deptID, 'ok' => 1), XHELP_BASE_URL .'/admin/delete.php', sprintf(_AM_XHELP_MSG_DEPT_DEL_CFRM, $deptID));
        xoops_cp_footer();
    } else {
        $hDepartments =& xhelpGetHandler('department');
        $hGroupPerm =& xoops_gethandler('groupperm');
        $dept =& $hDepartments->get($deptID);

        $crit = new CriteriaCompo(new Criteria('gperm_name', _XHELP_GROUP_PERM_DEPT));
        $crit->add(new Criteria('gperm_itemid', $deptID));
        $hGroupPerm->deleteAll($crit);

        $deptCopy = $dept;

        if($hDepartments->delete($dept)){
            $_eventsrv->trigger('delete_department', array(&$dept));
            $message = _XHELP_MESSAGE_DEPT_DELETE;

            // Remove configoption for department
            $hConfigOption =& xoops_gethandler('configoption');
            $crit = new CriteriaCompo(new Criteria('confop_name', $deptCopy->getVar('department')));
            $crit->add(new Criteria('confop_value', $deptCopy->getVar('id')));
            $configOption =& $hConfigOption->getObjects($crit);

            if(count($configOption) > 0){
                if(!$hConfigOption->delete($configOption[0])){
                    $message = '';
                }
                unset($deptCopy);
            }

            // Change default department
            $depts =& $hDepartments->getObjects();
            $aDepts = array();
            foreach($depts as $dpt){
                $aDepts[] = $dpt->getVar('id');
            }
            if (isset($aDepts[0])) {
                xhelpSetMeta("default_department", $aDepts[0]);
            }
        } else {
            $message = _XHELP_MESSAGE_DEPT_DELETE_ERROR . $dept->getHtmlErrors();
        }
        redirect_header(XHELP_ADMIN_URL."/department.php?op=manageDepartments", 3, $message);
    }
} elseif(isset($_REQUEST['deleteStaff'])){
    if (isset( $_REQUEST['uid'])){
        $staffid = $_REQUEST['uid'];

        if (!isset($_POST['ok'])) {
            xoops_cp_header();
            //echo $oAdminButton->renderButtons('manDept');
            xoops_confirm(array('deleteStaff' => 1, 'uid' => $staffid, 'ok' => 1), XHELP_BASE_URL .'/admin/delete.php', sprintf(_AM_XHELP_MSG_STAFF_DEL_CFRM, $staffid));
            xoops_cp_footer();
        } else {
            $hStaff =& xhelpGetHandler('staff');
            $staff =& $hStaff->getByUid($staffid);

            if($hStaff->delete($staff)){
                $_eventsrv->trigger('delete_staff', array(&$staff));
                $message = _XHELP_MESSAGE_STAFF_DELETE;
            } else {
                $message = _XHELP_MESSAGE_STAFF_DELETE_ERROR . $staff->getHtmlErrors();
            }
            redirect_header(XHELP_ADMIN_URL."/staff.php?op=manageStaff", 3, $message);
        }
    }


}
?>