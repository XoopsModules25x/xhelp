<?php

namespace XoopsModules\Xhelp;

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
 * @package
 * @since
 * @author       XOOPS Development Team
 */

use XoopsModules\Xhelp;

/**
 * class WebLib
 */
class WebLib
{
    /**
     * @param $deptid
     * @return array
     */
    public function customFieldsByDept($deptid)
    {
        $deptid                      = (int)$deptid;
        $tickefielddepartmentHandler = Xhelp\Helper::getInstance()->getHandler('TicketFieldDepartment');
        $fields                      = &$tickefielddepartmentHandler->fieldsByDepartment($deptid);

        $aFields = [];
        foreach ($fields as $field) {
            $aFields[] = $field->toArray();
        }

        return $aFields;
    }

    /**
     * @param $deptid
     * @param $ticketid
     * @return array
     */
    public function editTicketCustFields($deptid, $ticketid)
    {
        $deptid                      = (int)$deptid;
        $tickefielddepartmentHandler = Xhelp\Helper::getInstance()->getHandler('TicketFieldDepartment');
        $ticketHandler               = Xhelp\Helper::getInstance()->getHandler('Ticket');
        $ticket                      = $ticketHandler->get($ticketid);
        $custValues                  = $ticket->getCustFieldValues();
        $fields                      = $tickefielddepartmentHandler->fieldsByDepartment($deptid);

        $aFields = [];
        foreach ($fields as $field) {
            $_arr                 = &$field->toArray();
            $_fieldname           = $_arr['fieldname'];
            $_arr['currentvalue'] = isset($custValues[$_fieldname]) ? $custValues[$_fieldname]['key'] : '';
            $aFields[]            = $_arr;
        }

        return $aFields;
    }

    /**
     * @param $deptid
     * @return array
     */
    public function staffByDept($deptid)
    {
        $mc    = Xhelp\Utility::getModuleConfig();
        $field = 1 == $mc['xhelp_displayName'] ? 'uname' : 'name';

        $deptid            = (int)$deptid;
        $membershipHandler = Xhelp\Helper::getInstance()->getHandler('Membership');
        $staff             = $membershipHandler->xoopsUsersByDept($deptid);

        $aStaff   = [];
        $aStaff[] = [
            'uid'  => 0,
            'name' => _XHELP_MESSAGE_NOOWNER,
        ];
        foreach ($staff as $s) {
            $aStaff[] = [
                'uid'  => $s->getVar('uid'),
                'name' => $s->getVar($field),
            ];
        }

        return $aStaff;
    }
}
