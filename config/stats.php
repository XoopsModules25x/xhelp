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
 * @copyright    XOOPS Project (https://xoops.org)
 * @license      GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       XOOPS Development Team
 */

use Xmf\Module\Admin;
use XoopsModules\Xhelp\{
    Helper
};

$moduleDirName      = \basename(\dirname(__DIR__));
$moduleDirNameUpper = \mb_strtoupper($moduleDirName);
$helper             = Helper::getInstance();

return [
        'totaldepartments'     => $helper->getHandler('Department')
            ->getCount(),
        'totalfiles'           => $helper->getHandler('File')
            ->getCount(),
        'totallogmessages'     => $helper->getHandler('Logmessage')
            ->getCount(),
        'totalresponses'       => $helper->getHandler('Response')
            ->getCount(),
        'totalstaff'           => $helper->getHandler('Staff')
            ->getCount(),
        'totalstaffreview'     => $helper->getHandler('StaffReview')
            ->getCount(),
        'totaltickets'         => $helper->getHandler('Ticket')
            ->getCount(),
        'totalroles'           => $helper->getHandler('Role')
            ->getCount(),
        'totalnotifications'   => $helper->getHandler('Notification')
            ->getCount(),
        'totalticketsolutions' => $helper->getHandler('TicketSolution')
            ->getCount(),
];
