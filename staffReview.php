<?php
//
require_once __DIR__ . '/header.php';
require_once XHELP_INCLUDE_PATH . '/events.php';

if ($xoopsUser) {
    if (isset($_POST['submit'])) {
        if (isset($_POST['staffid'])) {
            $staffid = (int)$_POST['staffid'];
        }
        if (isset($_POST['ticketid'])) {
            $ticketid = (int)$_POST['ticketid'];
        }
        if (isset($_POST['responseid'])) {
            $responseid = (int)$_POST['responseid'];
        }
        if (isset($_POST['rating'])) {
            $rating = (int)$_POST['rating'];
        }
        if (isset($_POST['comments'])) {
            $comments = $_POST['comments'];
        }
        $hStaffReview = xhelpGetHandler('staffReview');
        $hTicket      = xhelpGetHandler('ticket');
        $hResponse    = xhelpGetHandler('responses');

        $review = $hStaffReview->create();
        $review->setVar('staffid', $staffid);
        $review->setVar('rating', $rating);
        $review->setVar('ticketid', $ticketid);
        $review->setVar('responseid', $responseid);
        $review->setVar('comments', $comments);
        $review->setVar('submittedBy', $xoopsUser->getVar('uid'));
        $review->setVar('userIP', getenv('REMOTE_ADDR'));
        if ($hStaffReview->insert($review)) {
            $message  = _XHELP_MESSAGE_ADD_STAFFREVIEW;
            $ticket   =& $hTicket->get($ticketid);
            $response =& $hResponse->get($responseid);
            $_eventsrv->trigger('new_response_rating', [&$review, &$ticket, &$response]);
        } else {
            $message = _XHELP_MESSAGE_ADD_STAFFREVIEW_ERROR;
        }
        redirect_header(XHELP_BASE_URL . "/ticket.php?id=$ticketid", 3, $message);
    } else {
        $GLOBALS['xoopsOption']['template_main'] = 'xhelp_staffReview.tpl';   // Set template
        require XOOPS_ROOT_PATH . '/header.php';                     // Include

        if (isset($_GET['staff'])) {
            $xoopsTpl->assign('xhelp_staffid', (int)$_GET['staff']);
        }
        if (isset($_GET['ticketid'])) {
            $xoopsTpl->assign('xhelp_ticketid', (int)$_GET['ticketid']);
        }
        if (isset($_GET['responseid'])) {
            $xoopsTpl->assign('xhelp_responseid', (int)$_GET['responseid']);
        }

        $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
        $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
        $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);

        require XOOPS_ROOT_PATH . '/footer.php';
    }
} else {    // If not a user
    redirect_header(XOOPS_URL . '/user.php', 3);
}
