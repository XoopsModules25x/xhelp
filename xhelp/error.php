<?php
//$Id: error.php,v 1.4 2004/12/08 16:18:31 eric_juden Exp $
require_once('header.php');

$xoopsOption['template_main'] = 'xhelp_error.html';
include(XOOPS_ROOT_PATH . '/header.php');

$xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
$xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/images/');
$xoopsTpl->assign('xhelp_message', _XHELP_MESSAGE_NO_REGISTER);

include(XOOPS_ROOT_PATH . '/footer.php');
