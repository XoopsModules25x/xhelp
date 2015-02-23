<?php
//$Id: lookup.php,v 1.16 2005/05/13 16:24:17 eric_juden Exp $
require_once('header.php');

$hStaff =& xhelpGetHandler('staff');

//Allow only staff members to view this page
if(!$xoopsUser) {
    redirect_header(XOOPS_URL, 3, _NOPERM);
}

$inadmin = 0;
if(isset($_REQUEST['admin']) && $_REQUEST['admin'] == 1){
    $inadmin = 1;
}

if(!$inadmin && !$xoopsUser->isAdmin($xoopsModule->getVar('mid'))){
    if (!$hStaff->isStaff($xoopsUser->getVar('uid'))) {
        redirect_header(XOOPS_URL."/modules/xhelp/index.php", 3, _NOPERM);
    }
}

// Initialize Smarty Template Engine
require_once XOOPS_ROOT_PATH.'/class/template.php';
$xoopsTpl = new XoopsTpl();
$xoopsTpl->assign('xhelp_imagePath', XOOPS_URL .'/modules/xhelp/images/');
$xoopsTpl->assign('sitename', $xoopsConfig['sitename']);
$xoopsTpl->assign('xoops_themecss', xoops_getcss());
$xoopsTpl->assign('xoops_url', XOOPS_URL);
$xoopsTpl->assign('xhelp_inadmin', $inadmin);
$xoopsTpl->assign('xhelp_adminURL', XHELP_ADMIN_URL);

if(isset($_POST['search'])){
    if(isset($_POST['searchText'])){
        $text = $_POST['searchText'];
    }
    if(isset($_POST['subject'])){
        $subject = $_POST['subject'];
    }
    $xoopsTpl->assign('xhelp_viewResults', true);

    $user_handler =& xoops_gethandler('user');
    $crit = new Criteria($subject, "%". $text ."%", 'LIKE');
    $crit->setSort($subject);
    $users =& $user_handler->getObjects($crit);
    foreach($users as $user){
        $aUsers[] = array('uid'=>$user->getVar('uid'),
                          'uname'=>$user->getVar('uname'),
                          'name'=>$user->getVar('name'),
                          'email'=>$user->getVar('email'));
    }

    $xoopsTpl->assign('xhelp_matches', $aUsers);
    $xoopsTpl->assign('xhelp_matchCount', count($aUsers));
} else {
    $xoopsTpl->assign('xhelp_viewResults', false);
}
$xoopsTpl->display('db:xhelp_lookup.html');

exit();
?>