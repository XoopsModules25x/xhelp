<?php

use Xoopsmodules\xhelp;

//require_once('header.php');
require_once __DIR__ . '/../../../include/cp_header.php';
require_once __DIR__ . '/admin_header.php';

global $xoopsUser;
$uid = $xoopsUser->getVar('uid');

if (isset($_REQUEST['deleteDept'])) {
    if (isset($_REQUEST['deptid'])) {
        $deptID = $_REQUEST['deptid'];
    } else {
        redirect_header(XHELP_ADMIN_URL . '/department.php?op=manageDepartments', 3, _AM_XHELP_MESSAGE_NO_DEPT);
    }

    if (!isset($_POST['ok'])) {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manDept');
        xoops_confirm(['deleteDept' => 1, 'deptid' => $deptID, 'ok' => 1], XHELP_BASE_URL . '/admin/delete.php', sprintf(_AM_XHELP_MSG_DEPT_DEL_CFRM, $deptID));
        xoops_cp_footer();
    } else {
        $hDepartments = new xhelp\DepartmentHandler($GLOBALS['xoopsDB']);
        $hGroupPerm   = xoops_getHandler('groupperm');
        $dept         =& $hDepartments->get($deptID);

        $crit = new \CriteriaCompo(new \Criteria('gperm_name', _XHELP_GROUP_PERM_DEPT));
        $crit->add(new \Criteria('gperm_itemid', $deptID));
        $hGroupPerm->deleteAll($crit);

        $deptCopy = $dept;

        if ($hDepartments->delete($dept)) {
            $_eventsrv->trigger('delete_department', [&$dept]);
            $message = _XHELP_MESSAGE_DEPT_DELETE;

            // Remove configoption for department
            $hConfigOption = xoops_getHandler('configoption');
            $crit          = new \CriteriaCompo(new \Criteria('confop_name', $deptCopy->getVar('department')));
            $crit->add(new \Criteria('confop_value', $deptCopy->getVar('id')));
            $configOption = $hConfigOption->getObjects($crit);

            if (count($configOption) > 0) {
                if (!$hConfigOption->delete($configOption[0])) {
                    $message = '';
                }
                unset($deptCopy);
            }

            // Change default department
            $depts  = $hDepartments->getObjects();
            $aDepts = [];
            foreach ($depts as $dpt) {
                $aDepts[] = $dpt->getVar('id');
            }
            if (isset($aDepts[0])) {
                xhelp\Utility::setMeta('default_department', $aDepts[0]);
            }
        } else {
            $message = _XHELP_MESSAGE_DEPT_DELETE_ERROR . $dept->getHtmlErrors();
        }
        redirect_header(XHELP_ADMIN_URL . '/department.php?op=manageDepartments', 3, $message);
    }
} elseif (isset($_REQUEST['deleteStaff'])) {
    if (isset($_REQUEST['uid'])) {
        $staffid = $_REQUEST['uid'];

        if (!isset($_POST['ok'])) {
            xoops_cp_header();
            //echo $oAdminButton->renderButtons('manDept');
            xoops_confirm(['deleteStaff' => 1, 'uid' => $staffid, 'ok' => 1], XHELP_BASE_URL . '/admin/delete.php', sprintf(_AM_XHELP_MSG_STAFF_DEL_CFRM, $staffid));
            xoops_cp_footer();
        } else {
            $hStaff = new xhelp\StaffHandler($GLOBALS['xoopsDB']);
            $staff  = $hStaff->getByUid($staffid);

            if ($hStaff->delete($staff)) {
                $_eventsrv->trigger('delete_staff', [&$staff]);
                $message = _XHELP_MESSAGE_STAFF_DELETE;
            } else {
                $message = _XHELP_MESSAGE_STAFF_DELETE_ERROR . $staff->getHtmlErrors();
            }
            redirect_header(XHELP_ADMIN_URL . '/staff.php?op=manageStaff', 3, $message);
        }
    }
}
