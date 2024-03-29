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

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Xhelp;

require_once __DIR__ . '/admin_header.php';
xoops_load('XoopsPagenav');

global $xoopsModule;
$module_id = $xoopsModule->getVar('mid');
$helper    = Xhelp\Helper::getInstance();

$limit = Request::getInt('limit', 0, 'REQUEST');

$start = Request::getInt('start', 15, 'REQUEST');

if (Request::hasVar('order', 'REQUEST')) {
    $order = $_REQUEST['order'];
} else {
    $order = 'ASC';
}
if (Request::hasVar('sort', 'REQUEST')) {
    $sort = $_REQUEST['sort'];
} else {
    $sort = 'id';
}

$aSortBy  = [
    'id'       => _AM_XHELP_TEXT_ID,
    'ticketid' => _AM_XHELP_TEXT_TICKETID,
    'filename' => _AM_XHELP_TEXT_FILENAME,
    'mimetype' => _AM_XHELP_TEXT_MIMETYPE,
];
$aOrderBy = ['ASC' => _AM_XHELP_TEXT_ASCENDING, 'DESC' => _AM_XHELP_TEXT_DESCENDING];
$aLimitBy = ['10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100];

$op = 'default';

if (Request::hasVar('op', 'REQUEST')) {
    $op = \Xmf\Request::getString('op', '', 'REQUEST');
}

switch ($op) {
    case 'deleteFile':
        deleteFile();
        break;
    case 'deleteResolved':
        deleteResolved();
        break;
    case 'manageFiles':
        manageFiles();
        break;
    default:
        $helper->redirect('admin/index.php');
        break;
}

/**
 *
 */
function deleteFile()
{
    $helper = Xhelp\Helper::getInstance();
    /** @var \XoopsModules\Xhelp\FileHandler $fileHandler */
    $fileHandler = $helper->getHandler('File');

    if (!isset($_GET['fileid'])) {
        $helper->redirect('admin/file.php?op=manageFiles', 3, _XHELP_MESSAGE_DELETE_FILE_ERR);
    }
    $fileid = Request::getInt('fileid', 0, 'GET');
    if (isset($_POST['ok'])) {
        $file = $fileHandler->get($fileid);
        if ($fileHandler->delete($file, true)) {
            $helper->redirect('admin/file.php?op=manageFiles');
        }
        $helper->redirect('admin/file.php?op=manageFiles', 3, _XHELP_MESSAGE_DELETE_FILE_ERR);
    } else {
        xoops_cp_header();
        xoops_confirm(['op' => 'deleteFile', 'ok' => 1], XHELP_ADMIN_URL . '/file.php?fileid=' . $fileid, _AM_XHELP_MSG_DELETE_FILE);
        xoops_cp_footer();
    }
}

/**
 *
 */
function deleteResolved()
{
    $helper = Xhelp\Helper::getInstance();
    if (isset($_POST['ok'])) {
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = $helper->getHandler('Ticket');
        /** @var \XoopsModules\Xhelp\FileHandler $fileHandler */
        $fileHandler = $helper->getHandler('File');

        $tickets = $ticketHandler->getObjectsByState(1);     // Memory saver - unresolved should be less tickets

        $aTickets = [];
        foreach ($tickets as $ticket) {
            $aTickets[$ticket->getVar('id')] = $ticket->getVar('id');
        }

        // Retrieve all unresolved ticket attachments
        $criteria = new \CriteriaCompo();
        foreach ($aTickets as $ticket) {
            $criteria->add(new \Criteria('ticketid', $ticket, '!='));
        }
        if ($fileHandler->deleteAll($criteria)) {
            $helper->redirect('admin/file.php?op=manageFiles');
        } else {
            $helper->redirect('admin/file.php?op=manageFiles', 3, _XHELP_MESSAGE_DELETE_FILE_ERR);
        }
    } else {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('manFiles');
        $adminObject = Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));

        xoops_confirm(['op' => 'deleteResolved', 'ok' => 1], XHELP_BASE_URL . '/admin/file.php', _AM_XHELP_MSG_DELETE_RESOLVED);
        xoops_cp_footer();
    }
}

/**
 *
 */
function manageFiles()
{
    global $aSortBy, $aOrderBy, $aLimitBy, $order, $limit, $start, $sort;
    $xhelpUploadDir = XHELP_UPLOAD_PATH;
    $dir_status     = xhelp_admin_getPathStatus($xhelpUploadDir, true);
    $helper         = Xhelp\Helper::getInstance();

    if (-1 == $dir_status) {
        $can_upload = xhelp_admin_mkdir($xhelpUploadDir);
    }

    /** @var \XoopsModules\Xhelp\FileHandler $fileHandler */
    $fileHandler = $helper->getHandler('File');

    if (Request::hasVar('deleteFiles', 'POST')) {   // Delete all selected files
        $aFiles   = $_POST['files'];
        $criteria = new \Criteria('id', '(' . implode(',', $aFiles) . ')', 'IN');

        if ($fileHandler->deleteAll($criteria)) {
            $helper->redirect('admin/file.php?op=manageFiles');
        }
        $helper->redirect('admin/file.php?op=manageFiles', 3, _XHELP_MESSAGE_DELETE_FILE_ERR);
    }
    xoops_cp_header();
    //echo $oAdminButton->renderButtons('manFiles');
    $adminObject = Admin::getInstance();
    $adminObject->displayNavigation('file.php?op=manageFiles');

    echo '<script type="text/javascript" src="' . XOOPS_URL . '/modules/xhelp/include/functions.js"></script>';
    echo "<form method='post' action='" . XHELP_ADMIN_URL . "/file.php?op=manageFiles'>";

    echo "<table width='100%' cellspacing='1' class='outer'>
          <tr><th colspan='2'><label>" . _AM_XHELP_TEXT_TOTAL_USED_SPACE . '</label></th></tr>';

    echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_ALL_ATTACH . "</td>
              <td class='even'>" . xhelpDirsize($xhelpUploadDir) . '
              </td>
          </tr>';

    $resolvedSize = xhelpDirsize($xhelpUploadDir, true);
    echo "<tr><td class='head'>" . _AM_XHELP_TEXT_RESOLVED_ATTACH . "</td>
              <td class='even'>";
    if ($resolvedSize > 0) {
        echo $resolvedSize . ' <b>(' . _AM_XHELP_TEXT_DELETE_RESOLVED . "
                  <a href='" . XHELP_ADMIN_URL . "/file.php?op=deleteResolved'><img src='" . XHELP_IMAGE_URL . "/button_delete.png' title='" . _AM_XHELP_TEXT_DELETE . "' name='deleteFile'></a>)</b>";
    } else {
        echo $resolvedSize;
    }
    echo '</td>
          </tr>';
    echo '</table></form>';

    $criteria = new \Criteria('', '');
    $criteria->setOrder($order);
    $criteria->setSort($sort);
    $criteria->setLimit($limit);
    $criteria->setStart($start);
    $files = $fileHandler->getObjects($criteria);
    $total = $fileHandler->getCount($criteria);

    $nav = new \XoopsPageNav($total, $limit, $start, 'start', "op=manageFiles&amp;limit=$limit");

    echo "<form action='" . XHELP_ADMIN_URL . "/file.php?op=manageFiles' style='margin:0; padding:0;' method='post'>";
    echo $GLOBALS['xoopsSecurity']->getTokenHTML();
    echo "<table width='100%' cellspacing='1' class='outer'>";
    echo "<tr><td align='right'>" . _AM_XHELP_TEXT_SORT_BY . "
                  <select name='sort'>";
    foreach ($aSortBy as $value => $text) {
        ($sort == $value) ? $selected = 'selected' : $selected = '';
        echo "<option value='$value' $selected>$text</option>";
    }
    echo '</select>
                &nbsp;&nbsp;&nbsp;
                  ' . _AM_XHELP_TEXT_ORDER_BY . "
                  <select name='order'>";
    foreach ($aOrderBy as $value => $text) {
        ($order == $value) ? $selected = 'selected' : $selected = '';
        echo "<option value='$value' $selected>$text</option>";
    }
    echo '</select>
                  &nbsp;&nbsp;&nbsp;
                  ' . _AM_XHELP_TEXT_NUMBER_PER_PAGE . "
                  <select name='limit'>";
    foreach ($aLimitBy as $value => $text) {
        ($limit == $value) ? $selected = 'selected' : $selected = '';
        echo "<option value='$value' $selected>$text</option>";
    }
    echo "</select>
                  <input type='submit' name='file_sort' id='file_sort' value='" . _AM_XHELP_BUTTON_SUBMIT . "'>
              </td>
          </tr>";
    echo '</table></form>';

    echo "<form method='post' action='" . XHELP_ADMIN_URL . "/file.php?op=manageFiles'>";
    echo "<table width='100%' cellspacing='1' class='outer'>
          <tr><th colspan='6'><label>" . _AM_XHELP_TEXT_MANAGE_FILES . '</label></th></tr>';
    if (0 != $total) {
        echo "<tr class='head'>
                  <td>" . _AM_XHELP_TEXT_ID . '</td>
                  <td>' . _AM_XHELP_TEXT_TICKETID . '</td>
                  <td>' . _AM_XHELP_TEXT_FILENAME . '</td>
                  <td>' . _AM_XHELP_TEXT_SIZE . '</td>
                  <td>' . _AM_XHELP_TEXT_MIMETYPE . '</td>
                  <td>' . _AM_XHELP_TEXT_ACTIONS . '</td>
              </tr>';

        foreach ($files as $file) {
            $filepath   = XHELP_BASE_URL . '/viewFile.php?id=' . $file->getVar('id');
            $ticketpath = XHELP_BASE_URL . '/ticket.php?id=' . $file->getVar('ticketid');
            $filesize   = filesize($xhelpUploadDir . '/' . $file->getVar('filename'));

            echo "<tr class='even'>
                      <td><input type='checkbox' name='files[]' value='" . $file->getVar('id') . "'> " . $file->getVar('id') . "</td>
                      <td><a href='" . $ticketpath . "' target='_BLANK'>" . $file->getVar('ticketid') . "</a></td>
                      <td><a href='" . $filepath . "'>" . $file->getVar('filename') . '</a></td>
                      <td>' . Xhelp\Utility::prettyBytes($filesize) . '</td>
                      <td>' . $file->getVar('mimetype') . "</td>
                      <td>
                          <a href='" . XHELP_ADMIN_URL . '/file.php?op=deleteFile&amp;fileid=' . $file->getVar('id') . "'><img src='" . XOOPS_URL . "/modules/xhelp/assets/images/button_delete.png' title='" . _AM_XHELP_TEXT_DELETE . "' name='deleteFile'></a>
                      </td>
                 </tr>";
        }
        echo "<tr class='foot'><td colspan='6'>
                                   <input type='checkbox' name='checkAllFiles' value='0' onclick='selectAll(this.form,\"files[]\",this.checked);'>
                                   <input type='submit' name='deleteFiles' id='deleteFiles' value='" . _AM_XHELP_BUTTON_DELETE . "'></td></tr>";
        echo '</table></form>';
        echo "<div id='status_nav'>" . $nav->renderNav() . '</div>';
    } else {
        echo "<tr class='even'<td colspan='6'>" . _AM_XHELP_TEXT_NO_FILES . '</td></tr>';
        echo '</table></form>';
    }

    require_once __DIR__ . '/admin_footer.php';
}
