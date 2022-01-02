<?php declare(strict_types=1);

use Xmf\Request;
use XoopsModules\Xhelp;

require_once __DIR__ . '/header.php';

$staffHandler = Xhelp\Helper::getInstance()->getHandler('Staff');

//Allow only staff members to view this page
if (!$xoopsUser) {
    redirect_header(XOOPS_URL, 3, _NOPERM);
}

$inadmin = 0;
if (Request::hasVar('admin', 'REQUEST') && 1 == $_REQUEST['admin']) {
    $inadmin = 1;
}

if (!$inadmin && !$xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
    if (!$staffHandler->isStaff($xoopsUser->getVar('uid'))) {
        redirect_header(XOOPS_URL . '/modules/xhelp/index.php', 3, _NOPERM);
    }
}

// Initialize Smarty Template Engine
require_once XOOPS_ROOT_PATH . '/class/template.php';
$xoopsTpl = new \XoopsTpl();
$xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
$xoopsTpl->assign('sitename', $xoopsConfig['sitename']);
$xoopsTpl->assign('xoops_themecss', xoops_getcss());
$xoopsTpl->assign('xoops_url', XOOPS_URL);
$xoopsTpl->assign('xhelp_inadmin', $inadmin);
$xoopsTpl->assign('xhelp_adminURL', XHELP_ADMIN_URL);

if (Request::hasVar('search', 'POST')) {
    if (Request::hasVar('searchText', 'POST')) {
        $text = $_POST['searchText'];
    }
    if (Request::hasVar('subject', 'POST')) {
        $subject = $_POST['subject'];
    }
    $xoopsTpl->assign('xhelp_viewResults', true);

    $userHandler = xoops_getHandler('user');
    $criteria        = new \Criteria($subject, '%' . $text . '%', 'LIKE');
    $criteria->setSort($subject);
    $users = $userHandler->getObjects($criteria);
    foreach ($users as $user) {
        $aUsers[] = [
            'uid'   => $user->getVar('uid'),
            'uname' => $user->getVar('uname'),
            'name'  => $user->getVar('name'),
            'email' => $user->getVar('email'),
        ];
    }

    $xoopsTpl->assign('xhelp_matches', $aUsers);
    $xoopsTpl->assign('xhelp_matchCount', count($aUsers));
} else {
    $xoopsTpl->assign('xhelp_viewResults', false);
}
$xoopsTpl->display('db:xhelp_lookup.tpl');

exit();
