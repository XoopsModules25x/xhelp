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

use Xmf\Request;
use XoopsModules\Xhelp;

require_once __DIR__ . '/header.php';
require_once XHELP_INCLUDE_PATH . '/events.php';
$eventService = Xhelp\EventService::getInstance();

if ($xoopsUser) {
    if (Request::hasVar('submit', 'POST')) {
        if (Request::hasVar('staffid', 'POST')) {
            $staffid = Request::getInt('staffid', 0, 'POST');
        }
        if (Request::hasVar('ticketid', 'POST')) {
            $ticketid = Request::getInt('ticketid', 0, 'POST');
        }
        if (Request::hasVar('responseid', 'POST')) {
            $responseid = Request::getInt('responseid', 0, 'POST');
        }
        if (Request::hasVar('rating', 'POST')) {
            $rating = Request::getInt('rating', 0, 'POST');
        }
        if (Request::hasVar('comments', 'POST')) {
            $comments = $_POST['comments'];
        }
        $staffReviewHandler = Xhelp\Helper::getInstance()
            ->getHandler('StaffReview');
        $ticketHandler      = Xhelp\Helper::getInstance()
            ->getHandler('Ticket');
        $responseHandler    = Xhelp\Helper::getInstance()
            ->getHandler('Response');

        $review = $staffReviewHandler->create();
        $review->setVar('staffid', $staffid);
        $review->setVar('rating', $rating);
        $review->setVar('ticketid', $ticketid);
        $review->setVar('responseid', $responseid);
        $review->setVar('comments', $comments);
        $review->setVar('submittedBy', $xoopsUser->getVar('uid'));
        $review->setVar('userIP', getenv('REMOTE_ADDR'));
        if ($staffReviewHandler->insert($review)) {
            $message  = _XHELP_MESSAGE_ADD_STAFFREVIEW;
            $ticket   = $ticketHandler->get($ticketid);
            $response = $responseHandler->get($responseid);
            $eventService->trigger('new_response_rating', [&$review, &$ticket, &$response]);
        } else {
            $message = _XHELP_MESSAGE_ADD_STAFFREVIEW_ERROR;
        }
        $helper->redirect("ticket.php?id=$ticketid", 3, $message);
    } else {
        $GLOBALS['xoopsOption']['template_main'] = 'xhelp_staffReview.tpl';   // Set template
        require_once XOOPS_ROOT_PATH . '/header.php';                         // Include

        if (Request::hasVar('staff', 'GET')) {
            $xoopsTpl->assign('xhelp_staffid', Request::getInt('staff', 0, 'GET'));
        }
        if (Request::hasVar('ticketid', 'GET')) {
            $xoopsTpl->assign('xhelp_ticketid', Request::getInt('ticketid', 0, 'GET'));
        }
        if (Request::hasVar('responseid', 'GET')) {
            $xoopsTpl->assign('xhelp_responseid', Request::getInt('responseid', 0, 'GET'));
        }

        $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
        $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
        $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);

        require_once XOOPS_ROOT_PATH . '/footer.php';
    }
} else {    // If not a user
    redirect_header(XOOPS_URL . '/user.php', 3);
}
