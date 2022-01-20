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

return (object)[
    'name'           => $moduleDirNameUpper . ' Module Configurator',
    'paths'          => [
        'dirname'    => $moduleDirName,
        'admin'      => XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/admin',
        'modPath'    => XOOPS_ROOT_PATH . '/modules/' . $moduleDirName,
        'modUrl'     => XOOPS_URL . '/modules/' . $moduleDirName,
        'uploadPath' => XOOPS_UPLOAD_PATH . '/' . $moduleDirName,
        'uploadUrl'  => XOOPS_UPLOAD_URL . '/' . $moduleDirName,
    ],
    'uploadFolders'  => [
        XOOPS_UPLOAD_PATH . '/' . $moduleDirName,
        XOOPS_UPLOAD_PATH . '/' . $moduleDirName . '/category',
        XOOPS_UPLOAD_PATH . '/' . $moduleDirName . '/screenshots',
        //XOOPS_UPLOAD_PATH . '/flags'
    ],
    'copyBlankFiles' => [
        XOOPS_UPLOAD_PATH . '/' . $moduleDirName,
        XOOPS_UPLOAD_PATH . '/' . $moduleDirName . '/category',
        XOOPS_UPLOAD_PATH . '/' . $moduleDirName . '/screenshots',
        //XOOPS_UPLOAD_PATH . '/flags'
    ],

    'copyTestFolders' => [
        [
            XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/testdata/uploads',
            XOOPS_UPLOAD_PATH . '/' . $moduleDirName,
        ],
        //            [
        //                XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/testdata/thumbs',
        //                XOOPS_UPLOAD_PATH . '/' . $moduleDirName . '/thumbs',
        //            ],
    ],

    'templateFolders' => [
        '/templates/',
        //            '/templates/blocks/',
        //            '/templates/admin/'
    ],
    'oldFiles'        => [
        '/class/request.php',
        '/class/registry.php',
        '/class/utilities.php',
        '/class/util.php',
        //            '/include/constants.php',
        //            '/include/functions.php',
        '/ajaxrating.txt',
    ],
    'oldFolders'      => [
        '/images',
        '/css',
        '/js',
        '/tcpdf',
    ],

    'renameTables'  => [//         'XX_archive'     => 'ZZZZ_archive',
    ],
    'renameColumns' => [//        'extcal_event' => ['from' => 'event_etablissement', 'to' => 'event_location'],
    ],
    'moduleStats'   => [
//        'totaldepartments'     => $helper->getHandler('Department')
//            ->getCount(),
//        'totalfiles'           => $helper->getHandler('File')
//            ->getCount(),
//        'totallogmessages'     => $helper->getHandler('Logmessage')
//            ->getCount(),
//        'totalresponses'       => $helper->getHandler('Response')
//            ->getCount(),
//        'totalstaff'           => $helper->getHandler('Staff')
//            ->getCount(),
//        'totalstaffreview'     => $helper->getHandler('StaffReview')
//            ->getCount(),
//        'totaltickets'         => $helper->getHandler('Ticket')
//            ->getCount(),
//        'totalroles'           => $helper->getHandler('Role')
//            ->getCount(),
//        'totalnotifications'   => $helper->getHandler('Notification')
//            ->getCount(),
//        'totalticketsolutions' => $helper->getHandler('TicketSolution')
//            ->getCount(),

    ],
    'modCopyright'  => "<a href='https://xoops.org' title='XOOPS Project' target='_blank'>
                     <img src='" . Admin::iconUrl('xoopsmicrobutton.gif') . "' alt='XOOPS Project'></a>",
];
