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
 * @copyright    {@link https://xoops.org/ XOOPS Project}
 * @license      {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

require_once __DIR__ . '/header.php';

$GLOBALS['xoopsOption']['template_main'] = 'xhelp_error.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

$xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
$xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
$xoopsTpl->assign('xhelp_message', _XHELP_MESSAGE_NO_REGISTER);

require_once XOOPS_ROOT_PATH . '/footer.php';
