<?php declare(strict_types=1);

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
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

use XoopsModules\Xhelp;

/**
 * class WebLib
 */
class WebLib
{
    /**
     * @param int $deptid
     * @return array
     */
    public function customFieldsByDept(int $deptid): array
    {
        $deptid = $deptid;
        /** @var \XoopsModules\Xhelp\TicketFieldDepartmentHandler $ticketFieldDepartmentHandler */
        $ticketFieldDepartmentHandler = Xhelp\Helper::getInstance()
            ->getHandler('TicketFieldDepartment');
        $fields                       = $ticketFieldDepartmentHandler->fieldsByDepartment($deptid);

        $aFields = [];
        foreach ($fields as $field) {
            $aFields[] = $field->toArray();
        }

        return $aFields;
    }

    /**
     * @param int $deptid
     * @param int $ticketid
     * @return array
     */
    public function editTicketCustFields(int $deptid, int $ticketid): array
    {
        $deptid = $deptid;
        /** @var \XoopsModules\Xhelp\TicketFieldDepartmentHandler $ticketFieldDepartmentHandler */
        $ticketFieldDepartmentHandler = Xhelp\Helper::getInstance()
            ->getHandler('TicketFieldDepartment');
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = Xhelp\Helper::getInstance()
            ->getHandler('Ticket');
        $ticket        = $ticketHandler->get($ticketid);
        $custValues    = $ticket->getCustFieldValues();
        $fields        = $ticketFieldDepartmentHandler->fieldsByDepartment($deptid);

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
     * @param int $deptid
     * @return array
     */
    public function staffByDept(int $deptid): array
    {
        $mc    = Xhelp\Utility::getModuleConfig();
        $field = 1 == $mc['xhelp_displayName'] ? 'uname' : 'name';

        $deptid = $deptid;
        /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
        $membershipHandler = Xhelp\Helper::getInstance()
            ->getHandler('Membership');
        $staff             = $membershipHandler->xoopsUsersByDept($deptid);

        $aStaff   = [];
        $aStaff[] = [
            'uid'  => 0,
            'name' => \_XHELP_MESSAGE_NOOWNER,
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
