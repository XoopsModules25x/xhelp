<?php declare(strict_types=1);

require_once __DIR__ . '/header.php';

$GLOBALS['xoopsOption']['template_main'] = 'xhelp_error.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

$xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
$xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
$xoopsTpl->assign('xhelp_message', _XHELP_MESSAGE_NO_REGISTER);

require_once XOOPS_ROOT_PATH . '/footer.php';
