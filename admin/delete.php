<?php declare(strict_types=1);

use Xmf\Request;
use XoopsModules\Xhelp;

require_once __DIR__ . '/admin_header.php';

global $xoopsUser;
$uid = $xoopsUser->getVar('uid');

if (Request::hasVar('deleteDept', 'REQUEST')) {
    if (Request::hasVar('deptid', 'REQUEST')) {
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
        $departmentHandler = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);
        /** @var \XoopsGroupPermHandler $grouppermHandler */
        $grouppermHandler = xoops_getHandler('groupperm');
        $dept             = $departmentHandler->get($deptID);

        $criteria = new \CriteriaCompo(new \Criteria('gperm_name', _XHELP_GROUP_PERM_DEPT));
        $criteria->add(new \Criteria('gperm_itemid', $deptID));
        $grouppermHandler->deleteAll($criteria);

        $deptCopy = $dept;

        if ($departmentHandler->delete($dept)) {
            $_eventsrv->trigger('delete_department', [&$dept]);
            $message = _XHELP_MESSAGE_DEPT_DELETE;

            // Remove configoption for department
            $configOptionHandler = Xhelp\Helper::getInstance()->getHandler('ConfigOption');
            $criteria                = new \CriteriaCompo(new \Criteria('confop_name', $deptCopy->getVar('department')));
            $criteria->add(new \Criteria('confop_value', $deptCopy->getVar('id')));
            $configOption = $configOptionHandler->getObjects($criteria);

            if (count($configOption) > 0) {
                if (!$configOptionHandler->delete($configOption[0])) {
                    $message = '';
                }
                unset($deptCopy);
            }

            // Change default department
            $depts  = $departmentHandler->getObjects();
            $aDepts = [];
            foreach ($depts as $dpt) {
                $aDepts[] = $dpt->getVar('id');
            }
            if (isset($aDepts[0])) {
                Xhelp\Utility::setMeta('default_department', $aDepts[0]);
            }
        } else {
            $message = _XHELP_MESSAGE_DEPT_DELETE_ERROR . $dept->getHtmlErrors();
        }
        redirect_header(XHELP_ADMIN_URL . '/department.php?op=manageDepartments', 3, $message);
    }
} elseif (Request::hasVar('deleteStaff', 'REQUEST')) {
    if (Request::hasVar('uid', 'REQUEST')) {
        $staffid = $_REQUEST['uid'];

        if (!isset($_POST['ok'])) {
            xoops_cp_header();
            //echo $oAdminButton->renderButtons('manDept');
            xoops_confirm(['deleteStaff' => 1, 'uid' => $staffid, 'ok' => 1], XHELP_BASE_URL . '/admin/delete.php', sprintf(_AM_XHELP_MSG_STAFF_DEL_CFRM, $staffid));
            xoops_cp_footer();
        } else {
            $staffHandler = new Xhelp\StaffHandler($GLOBALS['xoopsDB']);
            $staff        = $staffHandler->getByUid($staffid);

            if ($staffHandler->delete($staff)) {
                $_eventsrv->trigger('delete_staff', [&$staff]);
                $message = _XHELP_MESSAGE_STAFF_DELETE;
            } else {
                $message = _XHELP_MESSAGE_STAFF_DELETE_ERROR . $staff->getHtmlErrors();
            }
            redirect_header(XHELP_ADMIN_URL . '/staff.php?op=manageStaff', 3, $message);
        }
    }
}
