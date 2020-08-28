<?php

use Xmf\Request;
use XoopsModules\Xhelp;

require_once __DIR__ . '/header.php';

/** @var Xhelp\Helper $helper */
$helper = Xhelp\Helper::getInstance();

$op = 'default';
require_once XHELP_INCLUDE_PATH . '/events.php';
// require_once XHELP_CLASS_PATH . '/faqAdapterFactory.php';
// require_once XHELP_CLASS_PATH . '/faqCategory.php';
// require_once XHELP_CLASS_PATH . '/Tree.php';

if (Request::hasVar('op', 'REQUEST')) {
    $op = $_REQUEST['op'];
}

if (!$xoopsUser) {
    redirect_header(XHELP_BASE_URL, 3, _NOPERM);
} elseif (!$xhelp_isStaff) {
    redirect_header(XHELP_BASE_URL, 3, _NOPERM);
}

switch ($op) {
    case 'add':
        if (!isset($_POST['addFaq'])) {
            addFaq_display();
        } else {
            addFaq_action();
        }
        break;
    default:
        addFaq_display();
        break;
}

function addFaq_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xoopsLogger, $xoopsUserIsAdmin, $_xhelpSession, $xhelp_staff;
    /** @var Xhelp\Helper $helper */
    $helper = Xhelp\Helper::getInstance();

    if (!isset($_POST['ticketid']) && 0 == Request::getInt('ticketid', 0, 'POST')) {
        redirect_header(XHELP_BASE_URL, 3, _XHELP_MSG_NO_ID);
    }
    $ticketid = Request::getInt('ticketid', 0, 'POST');

    $ticketHandler = Xhelp\Helper::getInstance()->getHandler('Ticket');
    $hResponses    = Xhelp\Helper::getInstance()->getHandler('Responses');
    $ticket        = $ticketHandler->get($ticketid);

    if (!$hasRights = $xhelp_staff->checkRoleRights(XHELP_SEC_FAQ_ADD, $ticket->getVar('department'))) {
        redirect_header(XHELP_BASE_URL . "/ticket.php?id=$ticketid", 3, XHELP_MESSAGE_NO_ADD_FAQ);
    }

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_addFaq.tpl';
    require_once XOOPS_ROOT_PATH . '/header.php';

    $crit         = new \Criteria('ticketid', $ticketid);
    $responses    = $hResponses->getObjects($crit, true);
    $responseText = '';

    $allUsers = [];
    foreach ($responses as $response) {
        $allUsers[$response->getVar('uid')] = '';
    }

    $crit  = new \Criteria('uid', '(' . implode(',', array_keys($allUsers)) . ')', 'IN');
    $users = Xhelp\Utility::getUsers($crit, $helper->getConfig('xhelp_displayName'));
    unset($allUsers);

    foreach ($responses as $response) {
        $responseText .= sprintf(_XHELP_TEXT_USER_SAID, $users[$response->getVar('uid')]) . "\n";
        $responseText .= $response->getVar('message', 'e') . "\n";
    }

    // Get current faq adapter
    $oAdapter = Xhelp\FaqAdapterFactory::getFaqAdapter();
    if (!$oAdapter) {
        redirect_header(XHELP_BASE_URL, 3, _XHELP_MESSAGE_NO_FAQ);
    }
    $categories = &$oAdapter->getCategories();

    $tree = new Xhelp\Tree($categories, 'id', 'parent');

    $xoopsTpl->assign('xhelp_categories', $tree->makeSelBox('categories', 'name', '--', 0, false, 0, $oAdapter->categoryType));
    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL . '/');
    $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
    $xoopsTpl->assign('xhelp_faqProblem', $ticket->getVar('description', 'e'));
    $xoopsTpl->assign('xhelp_faqSolution', $responseText);
    $xoopsTpl->assign('xhelp_hasMultiCats', $oAdapter->categoryType);
    $xoopsTpl->assign('xhelp_ticketID', $ticketid);
    $xoopsTpl->assign('xhelp_faqSubject', $ticket->getVar('subject', 'e'));

    require_once XOOPS_ROOT_PATH . '/footer.php';
}

function addFaq_action()
{
    global $xoopsUser, $_eventsrv;
    $ticketHandler = Xhelp\Helper::getInstance()->getHandler('Ticket');

    // Retrieve ticket information
    $ticketid = $_POST['ticketid'];
    $ticket   = $ticketHandler->get($ticketid);

    $adapter = Xhelp\FaqAdapterFactory::getFaqAdapter();
    $faq     = $adapter->createFaq();

    // @todo - Make subject user editable
    $faq->setVar('subject', $_POST['subject']);
    $faq->setVar('problem', $_POST['problem']);
    $faq->setVar('solution', $_POST['solution']);
    // BTW - XOBJ_DTYPE_ARRAY vars must be serialized prior to calling setVar in XOOPS 2.0
    $faq->setVar('categories', serialize($_POST['categories']));

    if ($adapter->storeFaq($faq)) {
        // Todo: Run events here
        $_eventsrv->trigger('new_faq', [&$ticket, &$faq]);

        redirect_header(XHELP_BASE_URL . "/ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ADD_FAQ);
    } else {
        redirect_header(XHELP_BASE_URL . "/ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ERR_ADD_FAQ);
    }
}
