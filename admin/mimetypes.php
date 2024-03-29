<?php declare(strict_types=1);
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       XOOPS Project https://xoops.org
 * @license         https://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          The SmartFactory <www.smartfactory.ca>
 * @author          trabis <lusopoemas@gmail.com>
 */

//use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Xhelp;
use XoopsModules\Xhelp\MimetypesUtility;

require_once __DIR__ . '/admin_header.php';
xoops_load('XoopsPagenav');

global $xoopsModule;
$helper    = Xhelp\Helper::getInstance();
$module_id = $xoopsModule->getVar('mid');
$start     = Request::getInt('start', 0, 'GET');
$limit     = Request::getInt('limit', Request::getInt('limit', 15, 'GET'), 'POST');

$aSortBy   = [
    'mime_id'    => _AM_XHELP_MIME_ID,
    'mime_name'  => _AM_XHELP_MIME_NAME,
    'mime_ext'   => _AM_XHELP_MIME_EXT,
    'mime_admin' => _AM_XHELP_MIME_ADMIN,
    'mime_user'  => _AM_XHELP_MIME_USER,
];
$aOrderBy  = ['ASC' => _AM_XHELP_TEXT_ASCENDING, 'DESC' => _AM_XHELP_TEXT_DESCENDING];
$aLimitBy  = ['10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100];
$aSearchBy = [
    'mime_id'   => _AM_XHELP_MIME_ID,
    'mime_name' => _AM_XHELP_MIME_NAME,
    'mime_ext'  => _AM_XHELP_MIME_EXT,
];

$error = [];

$op = Request::getString('op', 'default', 'GET');

// all post requests should have a valid token
if ('POST' === Request::getMethod() && !$GLOBALS['xoopsSecurity']->check()) {
    redirect_header(XHELP_ADMIN_URL . '/mimetypes.php?op=manage', 3, CO_XHELP_BAD_TOKEN);
}

switch ($op) {
    case 'add':
        MimetypesUtility::add();
        break;
    case 'delete':
        MimetypesUtility::delete();
        break;
    case 'edit':
        MimetypesUtility::edit();
        break;
    case 'search':
        MimetypesUtility::search($icons);
        break;
    case 'updateMimeValue':
        MimetypesUtility::updateMimeValue();
        break;
    case 'confirmUpdateMimeValue':
        MimetypesUtility::confirmUpdateMimeValue();
        break;
    case 'clearAddSession':
        MimetypesUtility::clearAddSession();
        break;
    case 'clearEditSession':
        MimetypesUtility::clearEditSession();
        break;
    case 'manage':
    default:
        MimetypesUtility::manage($icons);
        break;
}

//function add()
//{
//    global $mimetypeHandler, $limit, $start;
//    $helper = Xhelp\Helper::getInstance();
//
//    if (!isset($_POST['add_mime'])) {
//        xoops_cp_header();
//        //echo $oAdminButton->renderButtons('mimetypes');
//        $adminObject = Admin::getInstance();
//        $adminObject->displayNavigation(basename(__FILE__));
//
//        $session     = Xhelp\Session::getInstance();
//        $mime_type   = $session->get('xhelp_addMime');
//        $mime_errors = $session->get('xhelp_addMimeErr');
//
//        //Display any form errors
//        if (false === !$mime_errors) {
//            xhelpRenderErrors($mime_errors, Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'clearAddSession']));
//        }
//
//        if (false === $mime_type) {
//            $mime_ext   = '';
//            $mime_name  = '';
//            $mime_types = '';
//            $mime_admin = 1;
//            $mime_user  = 1;
//        } else {
//            $mime_ext   = $mime_type['mime_ext'];
//            $mime_name  = $mime_type['mime_name'];
//            $mime_types = $mime_type['mime_types'];
//            $mime_admin = $mime_type['mime_admin'];
//            $mime_user  = $mime_type['mime_user'];
//        }
//
//        // Display add form
//        echo "<form action='mimetypes.php?op=add' method='post'>";
//        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
//        echo "<table width='100%' cellspacing='1' class='outer'>";
//        echo "<tr><th colspan='2'>" . _AM_XHELP_MIME_CREATEF . '</th></tr>';
//        echo "<tr valign='top'>
//                  <td class='head'>" . _AM_XHELP_MIME_EXTF . "</td>
//                  <td class='even'><input type='text' name='mime_ext' id='mime_ext' value='$mime_ext' size='5'></td>
//              </tr>";
//        echo "<tr valign='top'>
//                  <td class='head'>" . _AM_XHELP_MIME_NAMEF . "</td>
//                  <td class='even'><input type='text' name='mime_name' id='mime_name' value='$mime_name'></td>
//              </tr>";
//        echo "<tr valign='top'>
//                  <td class='head'>" . _AM_XHELP_MIME_TYPEF . "</td>
//                  <td class='even'><textarea name='mime_types' id='mime_types' cols='60' rows='5'>$mime_types</textarea></td>
//              </tr>";
//        echo "<tr valign='top'>
//                  <td class='head'>" . _AM_XHELP_MIME_ADMINF . "</td>
//                  <td class='even'>";
//
//        echo "<input type='radio' name='mime_admin' value='1' " . (1 == $mime_admin ? 'checked' : '') . '>' . _XHELP_TEXT_YES;
//        echo "<input type='radio' name='mime_admin' value='0' " . (0 == $mime_admin ? 'checked' : '') . '>' . _XHELP_TEXT_NO . '
//                  </td>
//              </tr>';
//        echo "<tr valign='top'>
//                  <td class='head'>" . _AM_XHELP_MIME_USERF . "</td>
//                  <td class='even'>";
//        echo "<input type='radio' name='mime_user' value='1'" . (1 == $mime_user ? 'checked' : '') . '>' . _XHELP_TEXT_YES;
//        echo "<input type='radio' name='mime_user' value='0'" . (0 == $mime_user ? 'checked' : '') . '>' . _XHELP_TEXT_NO . '
//                  </td>
//              </tr>';
//        echo "<tr valign='top'>
//                  <td class='head'></td>
//                  <td class='even'>
//                      <input type='submit' name='add_mime' id='add_mime' value='" . _AM_XHELP_BUTTON_SUBMIT . "' class='formButton'>
//                      <input type='button' name='cancel' value='" . _AM_XHELP_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton'>
//                  </td>
//              </tr>";
//        echo '</table></form>';
//        // end of add form
//
//        // Find new mimetypes table
//        echo "<form action='https://www.filext.com' method='post'>";
//        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
//        echo "<table width='100%' cellspacing='1' class='outer'>";
//        echo "<tr><th colspan='2'>" . _AM_XHELP_MIME_FINDMIMETYPE . '</th></tr>';
//
//        echo "<tr class='foot'>
//                  <td colspan='2'><input type='submit' name='find_mime' id='find_mime' value='" . _AM_XHELP_MIME_FINDIT . "' class='formButton'></td>
//              </tr>";
//
//        echo '</table></form>';
//
//        require_once __DIR__ . '/admin_footer.php';
//    } else {
//        $has_errors = false;
//        $error      = [];
//        $mime_ext   = $_POST['mime_ext'];
//        $mime_name  = $_POST['mime_name'];
//        $mime_types = $_POST['mime_types'];
//        $mime_admin = Request::getInt('mime_admin', 0, 'POST');
//        $mime_user  = Request::getInt('mime_user', 0, 'POST');
//
//        //Validate Mimetype entry
//        if ('' === trim($mime_ext)) {
//            $has_errors          = true;
//            $error['mime_ext'][] = _AM_XHELP_VALID_ERR_MIME_EXT;
//        }
//
//        if ('' === trim($mime_name)) {
//            $has_errors           = true;
//            $error['mime_name'][] = _AM_XHELP_VALID_ERR_MIME_NAME;
//        }
//
//        if ('' === trim($mime_types)) {
//            $has_errors            = true;
//            $error['mime_types'][] = _AM_XHELP_VALID_ERR_MIME_TYPES;
//        }
//
//        if ($has_errors) {
//            $session            = Xhelp\Session::getInstance();
//            $mime               = [];
//            $mime['mime_ext']   = $mime_ext;
//            $mime['mime_name']  = $mime_name;
//            $mime['mime_types'] = $mime_types;
//            $mime['mime_admin'] = $mime_admin;
//            $mime['mime_user']  = $mime_user;
//            $session->set('xhelp_addMime', $mime);
//            $session->set('xhelp_addMimeErr', $error);
//            redirect_header(Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'add'], false));
//        }
//
//        $mimetype = $mimetypeHandler->create();
//        $mimetype->setVar('mime_ext', $mime_ext);
//        $mimetype->setVar('mime_name', $mime_name);
//        $mimetype->setVar('mime_types', $mime_types);
//        $mimetype->setVar('mime_admin', $mime_admin);
//        $mimetype->setVar('mime_user', $mime_user);
//
//        if (!$mimetypeHandler->insert($mimetype)) {
//            $helper->redirect("mimetypes.php?op=manage&limit=$limit&start=$start", 3, _AM_XHELP_MESSAGE_ADD_MIME_ERROR);
//        } else {
//            clearAddSessionVars();
//            $helper->redirect("mimetypes.php?op=manage&limit=$limit&start=$start");
//        }
//    }
//}
//
//function delete()
//{
//    global $mimetypeHandler, $start, $limit;
//    $helper = Xhelp\Helper::getInstance();
//
//    if (!isset($_REQUEST['id'])) {
//        $helper->redirect('admin/mimetypes.php', 3, _AM_XHELP_MESSAGE_NO_ID);
//    } else {
//        $mime_id = Request::getInt('id', 0, 'REQUEST');
//    }
//    $mimetype = &$mimetypeHandler->get($mime_id);     // Retrieve mimetype object
//    if (!$mimetypeHandler->delete($mimetype, true)) {
//        $helper->redirect("mimetypes.php?op=manage&id=$mime_id&limit=$limit&start=$start", 3, _AM_XHELP_MESSAGE_DELETE_MIME_ERROR);
//    } else {
//        $helper->redirect("mimetypes.php?op=manage&limit=$limit&start=$start");
//    }
//}
//
//function edit()
//{
//    global $mimetypeHandler, $start, $limit;
//    $helper = Xhelp\Helper::getInstance();
//
//    if (!isset($_REQUEST['id'])) {
//        $helper->redirect('admin/mimetypes.php', 3, _AM_XHELP_MESSAGE_NO_ID);
//    } else {
//        $mime_id = Request::getInt('id', 0, 'REQUEST');
//    }
//    $mimetype = $mimetypeHandler->get($mime_id);     // Retrieve mimetype object
//
//    if (!isset($_POST['edit_mime'])) {
//        $session     = Xhelp\Session::getInstance();
//        $mime_type   = $session->get("xhelp_editMime_$mime_id");
//        $mime_errors = $session->get("xhelp_editMimeErr_$mime_id");
//
//        // Display header
//        xoops_cp_header();
//        //echo $oAdminButton->renderButtons('mimetypes');
//        $adminObject = Admin::getInstance();
//        $adminObject->displayNavigation(basename(__FILE__));
//
//        //Display any form errors
//        if (false === !$mime_errors) {
//            xhelpRenderErrors($mime_errors, Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'clearEditSession', 'id' => $mime_id]));
//        }
//
//        if (false === $mime_type) {
//            $mime_ext   = $mimetype->getVar('mime_ext');
//            $mime_name  = $mimetype->getVar('mime_name', 'e');
//            $mime_types = $mimetype->getVar('mime_types', 'e');
//            $mime_admin = $mimetype->getVar('mime_admin');
//            $mime_user  = $mimetype->getVar('mime_user');
//        } else {
//            $mime_ext   = $mime_type['mime_ext'];
//            $mime_name  = $mime_type['mime_name'];
//            $mime_types = $mime_type['mime_types'];
//            $mime_admin = $mime_type['mime_admin'];
//            $mime_user  = $mime_type['mime_user'];
//        }
//
//        // Display edit form
//        echo "<form action='mimetypes.php?op=edit&amp;id=" . $mime_id . "' method='post'>";
//        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
//        echo "<input type='hidden' name='limit' value='" . $limit . "'>";
//        echo "<input type='hidden' name='start' value='" . $start . "'>";
//        echo "<table width='100%' cellspacing='1' class='outer'>";
//        echo "<tr><th colspan='2'>" . _AM_XHELP_MIME_MODIFYF . '</th></tr>';
//        echo "<tr valign='top'>
//                  <td class='head'>" . _AM_XHELP_MIME_EXTF . "</td>
//                  <td class='even'><input type='text' name='mime_ext' id='mime_ext' value='$mime_ext' size='5'></td>
//              </tr>";
//        echo "<tr valign='top'>
//                  <td class='head'>" . _AM_XHELP_MIME_NAMEF . "</td>
//                  <td class='even'><input type='text' name='mime_name' id='mime_name' value='$mime_name'></td>
//              </tr>";
//        echo "<tr valign='top'>
//                  <td class='head'>" . _AM_XHELP_MIME_TYPEF . "</td>
//                  <td class='even'><textarea name='mime_types' id='mime_types' cols='60' rows='5'>$mime_types</textarea></td>
//              </tr>";
//        echo "<tr valign='top'>
//                  <td class='head'>" . _AM_XHELP_MIME_ADMINF . "</td>
//                  <td class='even'>
//                      <input type='radio' name='mime_admin' value='1' " . (1 == $mime_admin ? 'checked' : '') . '>' . _XHELP_TEXT_YES . "
//                      <input type='radio' name='mime_admin' value='0' " . (0 == $mime_admin ? 'checked' : '') . '>' . _XHELP_TEXT_NO . '
//                  </td>
//              </tr>';
//        echo "<tr valign='top'>
//                  <td class='head'>" . _AM_XHELP_MIME_USERF . "</td>
//                  <td class='even'>
//                      <input type='radio' name='mime_user' value='1' " . (1 == $mime_user ? 'checked' : '') . '>' . _XHELP_TEXT_YES . "
//                      <input type='radio' name='mime_user' value='0' " . (0 == $mime_user ? 'checked' : '') . '>' . _XHELP_TEXT_NO . '
//                  </td>
//              </tr>';
//        echo "<tr valign='top'>
//                  <td class='head'></td>
//                  <td class='even'>
//                      <input type='submit' name='edit_mime' id='edit_mime' value='" . _AM_XHELP_BUTTON_UPDATE . "' class='formButton'>
//                      <input type='button' name='cancel' value='" . _AM_XHELP_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton'>
//                  </td>
//              </tr>";
//        echo '</table></form>';
//        // end of edit form
//
//        require_once __DIR__ . '/admin_footer.php';
//    } else {
//        $mime_admin = 0;
//        $mime_user  = 0;
//        if (Request::hasVar('mime_admin', 'POST') && 1 === $_POST['mime_admin']) {
//            $mime_admin = 1;
//        }
//        if (Request::hasVar('mime_user', 'POST') && 1 === $_POST['mime_user']) {
//            $mime_user = 1;
//        }
//
//        //Validate Mimetype entry
//        if ('' === trim($_POST['mime_ext'])) {
//            $has_errors          = true;
//            $error['mime_ext'][] = _AM_XHELP_VALID_ERR_MIME_EXT;
//        }
//
//        if ('' === trim(\Xmf\Request::getString('mime_name', '', 'POST'))) {
//            $has_errors           = true;
//            $error['mime_name'][] = _AM_XHELP_VALID_ERR_MIME_NAME;
//        }
//
//        if ('' === trim($_POST['mime_types'])) {
//            $has_errors            = true;
//            $error['mime_types'][] = _AM_XHELP_VALID_ERR_MIME_TYPES;
//        }
//
//        if ($has_errors) {
//            $session            = Xhelp\Session::getInstance();
//            $mime               = [];
//            $mime['mime_ext']   = \Xmf\Request::getString('mime_ext', '', 'POST');
//            $mime['mime_name']  = \Xmf\Request::getString('mime_name', '', 'POST');
//            $mime['mime_types'] = $_POST['mime_types'];
//            $mime['mime_admin'] = $mime_admin;
//            $mime['mime_user']  = $mime_user;
//            $session->set('xhelp_editMime_' . $mime_id, $mime);
//            $session->set('xhelp_editMimeErr_' . $mime_id, $error);
//            redirect_header(Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'edit', 'id' => $mime_id], false));
//        }
//
//        $mimetype->setVar('mime_ext', \Xmf\Request::getString('mime_ext', '', 'POST'));
//        $mimetype->setVar('mime_name', \Xmf\Request::getString('mime_name', '', 'POST'));
//        $mimetype->setVar('mime_types', $_POST['mime_types']);
//        $mimetype->setVar('mime_admin', $mime_admin);
//        $mimetype->setVar('mime_user', $mime_user);
//
//        if (!$mimetypeHandler->insert($mimetype, true)) {
//            $helper->redirect("mimetypes.php?op=edit&id=$mime_id", 3, _AM_XHELP_MESSAGE_EDIT_MIME_ERROR);
//        } else {
//            clearEditSessionVars($mime_id);
//            $helper->redirect("mimetypes.php?op=manage&limit=$limit&start=$start");
//        }
//    }
//}
//
//function manage()
//{
//    global $mimetypeHandler, $icons, $start, $limit, $aSortBy, $aOrderBy, $aLimitBy, $aSearchBy;
//    $helper = Xhelp\Helper::getInstance();
//
//    if (Request::hasVar('deleteMimes', 'POST')) {
//        $aMimes = $_POST['mimes'];
//
//        $criteria = new \Criteria('mime_id', '(' . implode(',', $aMimes) . ')', 'IN');
//
//        if ($mimetypeHandler->deleteAll($criteria)) {
//            $helper->redirect("mimetypes.php?limit=$limit&start=$start");
//        } else {
//            $helper->redirect("mimetypes.php?limit=$limit&start=$start", 3, _AM_XHELP_MESSAGE_DELETE_MIME_ERROR);
//        }
//    }
//    if (Request::hasVar('add_mime', 'POST')) {
//        $helper->redirect("mimetypes.php?op=add&start=$start&limit=$limit");
//    }
//    if (Request::hasVar('mime_search', 'POST')) {
//        $helper->redirect('admin/mimetypes.php?op=search');
//    }
//
//    xoops_cp_header();
//    //echo $oAdminButton->renderButtons('mimetypes');
//    $adminObject = Admin::getInstance();
//    $adminObject->displayNavigation(basename(__FILE__));
//
//    $criteria = new \Criteria('', '');
//    if (Request::hasVar('order', 'REQUEST')) {
//        $order = $_REQUEST['order'];
//    } else {
//        $order = 'ASC';
//    }
//    if (Request::hasVar('sort', 'REQUEST')) {
//        $sort = $_REQUEST['sort'];
//    } else {
//        $sort = 'mime_ext';
//    }
//    $criteria->setOrder($order);
//    $criteria->setStart($start);
//    $criteria->setLimit($limit);
//    $criteria->setSort($sort);
//    $mimetypes  = $mimetypeHandler->getObjects($criteria);    // Retrieve a list of all mimetypes
//    $mime_count = $mimetypeHandler->getCount();
//    $nav        = new \XoopsPageNav($mime_count, $limit, $start, 'start', "op=manage&amp;limit=$limit");
//
//    echo '<script type="text/javascript" src="' . XHELP_BASE_URL . '/include/functions.js"></script>';
//    echo "<table width='100%' cellspacing='1' class='outer'>";
//    echo "<tr><td colspan='6' align='right'>";
//    echo "<form action='" . XHELP_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
//    echo $GLOBALS['xoopsSecurity']->getTokenHTML();
//    echo '<table>';
//    echo '<tr>';
//    echo "<td align='right'>" . _AM_XHELP_TEXT_SEARCH_BY . '</td>';
//    echo "<td align='left'><select name='search_by'>";
//    foreach ($aSearchBy as $value => $text) {
//        ($sort == $value) ? $selected = 'selected' : $selected = '';
//        echo "<option value='$value' $selected>$text</option>";
//    }
//    echo '</select></td>';
//    echo "<td align='right'>" . _AM_XHELP_TEXT_SEARCH_TEXT . '</td>';
//    echo "<td align='left'><input type='text' name='search_text' id='search_text' value=''></td>";
//    echo "<td><input type='submit' name='mime_search' id='mime_search' value='" . _AM_XHELP_BUTTON_SEARCH . "'></td>";
//    echo '</tr></table></form></td></tr>';
//
//    echo "<tr><td colspan='6'>";
//    echo "<form action='" . XHELP_ADMIN_URL . "/mimetypes.php?op=manage' style='margin:0; padding:0;' method='post'>";
//    echo $GLOBALS['xoopsSecurity']->getTokenHTML();
//    echo "<table width='100%'>";
//    echo "<tr><td align='right'>" . _AM_XHELP_TEXT_SORT_BY . "
//                  <select name='sort'>";
//    foreach ($aSortBy as $value => $text) {
//        ($sort == $value) ? $selected = 'selected' : $selected = '';
//        echo "<option value='$value' $selected>$text</option>";
//    }
//    echo '</select>
//                &nbsp;&nbsp;&nbsp;
//                  ' . _AM_XHELP_TEXT_ORDER_BY . "
//                  <select name='order'>";
//    foreach ($aOrderBy as $value => $text) {
//        ($order == $value) ? $selected = 'selected' : $selected = '';
//        echo "<option value='$value' $selected>$text</option>";
//    }
//    echo '</select>
//                  &nbsp;&nbsp;&nbsp;
//                  ' . _AM_XHELP_TEXT_NUMBER_PER_PAGE . "
//                  <select name='limit'>";
//    foreach ($aLimitBy as $value => $text) {
//        ($limit == $value) ? $selected = 'selected' : $selected = '';
//        echo "<option value='$value' $selected>$text</option>";
//    }
//    echo "</select>
//                  <input type='submit' name='mime_sort' id='mime_sort' value='" . _AM_XHELP_BUTTON_SUBMIT . "'>
//              </td>
//          </tr>";
//    echo '</table>';
//    echo '</td></tr>';
//    echo "<tr><th colspan='6'>" . _AM_XHELP_MENU_MIMETYPES . '</th></tr>';
//    echo "<tr class='head'>
//              <td>" . _AM_XHELP_MIME_ID . '</td>
//              <td>' . _AM_XHELP_MIME_NAME . '</td>
//              <td>' . _AM_XHELP_MIME_EXT . '</td>
//              <td>' . _AM_XHELP_MIME_ADMIN . '</td>
//              <td>' . _AM_XHELP_MIME_USER . '</td>
//              <td>' . _AM_XHELP_MINDEX_ACTION . '</td>
//          </tr>';
//    foreach ($mimetypes as $mime) {
//        echo "<tr class='even'>
//                  <td><input type='checkbox' name='mimes[]' value='" . $mime->getVar('mime_id') . "'>" . $mime->getVar('mime_id') . '</td>
//                  <td>' . $mime->getVar('mime_name') . '</td>
//                  <td>' . $mime->getVar('mime_ext') . "</td>
//                  <td>
//                      <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_admin=' . $mime->getVar('mime_admin') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
//                      " . (($mime->getVar('mime_admin') && isset($icons['online'])) ? $icons['online'] : $icons['offline'] ?? '') . "</a>
//                  </td>
//                  <td>
//                      <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_user=' . $mime->getVar('mime_user') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
//                      " . ($mime->getVar('mime_user') ? $icons['online'] ?? '' : $icons['offline'] ?? '') . "</a>
//                  </td>
//                  <td>
//                      <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=edit&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . ($icons['edit'] ?? '') . "</a>
//                      <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=delete&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . ($icons['delete'] ?? '') . '</a>
//                  </td>
//              </tr>';
//    }
//    echo "<tr class='foot'>
//              <td colspan='6' valign='top'>
//                  <a href='https://www.filext.com' style='float: right;' target='_blank'>" . _AM_XHELP_MIME_FINDMIMETYPE . "</a>
//                  <input type='checkbox' name='checkAllMimes' value='0' onclick='selectAll(this.form,\"mimes[]\",this.checked);'>
//                  <input type='submit' name='deleteMimes' id='deleteMimes' value='" . _AM_XHELP_BUTTON_DELETE . "'>
//                  <input type='submit' name='add_mime' id='add_mime' value='" . _AM_XHELP_MIME_CREATEF . "' class='formButton'>
//              </td>
//          </tr>";
//    echo '</table>';
//    echo "<div id='staff_nav'>" . $nav->renderNav() . '</div>';
//
//    require_once __DIR__ . '/admin_footer.php';
//}
//
//function search()
//{
//    global $mimetypeHandler, $limit, $start, $icons, $aSearchBy, $aOrderBy, $aLimitBy, $aSortBy;
//    $helper = Xhelp\Helper::getInstance();
//
//    if (Request::hasVar('deleteMimes', 'POST')) {
//        $aMimes = $_POST['mimes'];
//
//        $criteria = new \Criteria('mime_id', '(' . implode(',', $aMimes) . ')', 'IN');
//
//        if ($mimetypeHandler->deleteAll($criteria)) {
//            $helper->redirect("mimetypes.php?limit=$limit&start=$start");
//        } else {
//            $helper->redirect("mimetypes.php?limit=$limit&start=$start", 3, _AM_XHELP_MESSAGE_DELETE_MIME_ERROR);
//        }
//    }
//    if (Request::hasVar('add_mime', 'POST')) {
//        $helper->redirect("mimetypes.php?op=add&start=$start&limit=$limit");
//    }
//    if (Request::hasVar('order', 'REQUEST')) {
//        $order = $_REQUEST['order'];
//    } else {
//        $order = 'ASC';
//    }
//    if (Request::hasVar('sort', 'REQUEST')) {
//        $sort = $_REQUEST['sort'];
//    } else {
//        $sort = 'mime_name';
//    }
//
//    xoops_cp_header();
//    //echo $oAdminButton->renderButtons('mimetypes');
//    $adminObject = Admin::getInstance();
//    $adminObject->displayNavigation(basename(__FILE__));
//
//    if (!isset($_REQUEST['mime_search'])) {
//        echo "<form action='mimetypes.php?op=search' method='post'>";
//        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
//        echo "<table width='100%' cellspacing='1' class='outer'>";
//        echo "<tr><th colspan='2'>" . _AM_XHELP_TEXT_SEARCH_MIME . '</th></tr>';
//        echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_SEARCH_BY . "</td>
//                  <td class='even'>
//                      <select name='search_by'>";
//        foreach ($aSortBy as $value => $text) {
//            echo "<option value='$value'>$text</option>";
//        }
//        echo '</select>
//                  </td>
//              </tr>';
//        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_SEARCH_TEXT . "</td>
//                  <td class='even'>
//                      <input type='text' name='search_text' id='search_text' value=''>
//                  </td>
//              </tr>";
//        echo "<tr class='foot'>
//                  <td colspan='2'>
//                      <input type='submit' name='mime_search' id='mime_search' value='" . _AM_XHELP_BUTTON_SEARCH . "'>
//                  </td>
//              </tr>";
//        echo '</table></form>';
//    } else {
//        $search_field = $_REQUEST['search_by'];
//        $search_text  = $_REQUEST['search_text'];
//
//        $criteria = new \Criteria($search_field, "%$search_text%", 'LIKE');
//        $criteria->setSort($sort);
//        $criteria->setOrder($order);
//        $criteria->setLimit($limit);
//        $criteria->setStart($start);
//        $mime_count = $mimetypeHandler->getCount($criteria);
//        $mimetypes  = $mimetypeHandler->getObjects($criteria);
//        $nav        = new \XoopsPageNav($mime_count, $limit, $start, 'start', "op=search&amp;limit=$limit&amp;order=$order&amp;sort=$sort&amp;mime_search=1&amp;search_by=$search_field&amp;search_text=$search_text");
//        // Display results
//        echo '<script type="text/javascript" src="' . XHELP_BASE_URL . '/include/functions.js"></script>';
//
//        echo "<table width='100%' cellspacing='1' class='outer'>";
//        echo "<tr><td colspan='6' align='right'>";
//        echo "<form action='" . XHELP_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
//        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
//        echo '<table>';
//        echo '<tr>';
//        echo "<td align='right'>" . _AM_XHELP_TEXT_SEARCH_BY . '</td>';
//        echo "<td align='left'><select name='search_by'>";
//        foreach ($aSearchBy as $value => $text) {
//            ($search_field == $value) ? $selected = 'selected' : $selected = '';
//            echo "<option value='$value' $selected>$text</option>";
//        }
//        echo '</select></td>';
//        echo "<td align='right'>" . _AM_XHELP_TEXT_SEARCH_TEXT . '</td>';
//        echo "<td align='left'><input type='text' name='search_text' id='search_text' value='" . htmlentities($search_text, ENT_QUOTES) . "'></td>";
//        echo "<td><input type='submit' name='mime_search' id='mime_search' value='" . _AM_XHELP_BUTTON_SEARCH . "'></td>";
//        echo '</tr></table></form></td></tr>';
//
//        echo "<tr><td colspan='6'>";
//        echo "<form action='" . XHELP_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
//        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
//        echo "<table width='100%'>";
//        echo "<tr><td align='right'>" . _AM_XHELP_TEXT_SORT_BY . "
//                  <select name='sort'>";
//        foreach ($aSortBy as $value => $text) {
//            ($sort == $value) ? $selected = 'selected' : $selected = '';
//            echo "<option value='$value' $selected>$text</option>";
//        }
//        echo '</select>
//                &nbsp;&nbsp;&nbsp;
//                  ' . _AM_XHELP_TEXT_ORDER_BY . "
//                  <select name='order'>";
//        foreach ($aOrderBy as $value => $text) {
//            ($order == $value) ? $selected = 'selected' : $selected = '';
//            echo "<option value='$value' $selected>$text</option>";
//        }
//        echo '</select>
//                  &nbsp;&nbsp;&nbsp;
//                  ' . _AM_XHELP_TEXT_NUMBER_PER_PAGE . "
//                  <select name='limit'>";
//        foreach ($aLimitBy as $value => $text) {
//            ($limit == $value) ? $selected = 'selected' : $selected = '';
//            echo "<option value='$value' $selected>$text</option>";
//        }
//        echo "</select>
//                  <input type='submit' name='mime_sort' id='mime_sort' value='" . _AM_XHELP_BUTTON_SUBMIT . "'>
//                  <input type='hidden' name='mime_search' id='mime_search' value='1'>
//                  <input type='hidden' name='search_by' id='search_by' value='$search_field'>
//                  <input type='hidden' name='search_text' id='search_text' value='" . htmlentities($search_text, ENT_QUOTES) . "'>
//              </td>
//          </tr>";
//        echo '</table>';
//        echo '</td></tr>';
//        if (count($mimetypes) > 0) {
//            echo "<tr><th colspan='6'>" . _AM_XHELP_TEXT_SEARCH_MIME . '</th></tr>';
//            echo "<tr class='head'>
//                      <td>" . _AM_XHELP_MIME_ID . '</td>
//                      <td>' . _AM_XHELP_MIME_NAME . '</td>
//                      <td>' . _AM_XHELP_MIME_EXT . '</td>
//                      <td>' . _AM_XHELP_MIME_ADMIN . '</td>
//                      <td>' . _AM_XHELP_MIME_USER . '</td>
//                      <td>' . _AM_XHELP_MINDEX_ACTION . '</td>
//                  </tr>';
//            foreach ($mimetypes as $mime) {
//                echo "<tr class='even'>
//                          <td><input type='checkbox' name='mimes[]' value='" . $mime->getVar('mime_id') . "'>" . $mime->getVar('mime_id') . '</td>
//                          <td>' . $mime->getVar('mime_name') . '</td>
//                          <td>' . $mime->getVar('mime_ext') . "</td>
//                          <td>
//                              <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_admin=' . $mime->getVar('mime_admin') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
//                              " . ($mime->getVar('mime_admin') ? $icons['online'] : $icons['offline']) . "</a>
//                          </td>
//                          <td>
//                              <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_user=' . $mime->getVar('mime_user') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
//                              " . ($mime->getVar('mime_user') ? $icons['online'] : $icons['offline']) . "</a>
//                          </td>
//                          <td>
//                              <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=edit&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . $icons['edit'] . "</a>
//                              <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=delete&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . $icons['delete'] . '</a>
//                          </td>
//                      </tr>';
//            }
//            echo "<tr class='foot'>
//                      <td colspan='6' valign='top'>
//                          <a href='https://www.filext.com' style='float: right;' target='_blank'>" . _AM_XHELP_MIME_FINDMIMETYPE . "</a>
//                          <input type='checkbox' name='checkAllMimes' value='0' onclick='selectAll(this.form,\"mimes[]\",this.checked);'>
//                          <input type='submit' name='deleteMimes' id='deleteMimes' value='" . _AM_XHELP_BUTTON_DELETE . "'>
//                          <input type='submit' name='add_mime' id='add_mime' value='" . _AM_XHELP_MIME_CREATEF . "' class='formButton'>
//                      </td>
//                  </tr>";
//        } else {
//            echo '<tr><th>' . _AM_XHELP_TEXT_SEARCH_MIME . '</th></tr>';
//            echo "<tr class='even'>
//                      <td>" . _AM_XHELP_TEXT_NO_RECORDS . '</td>
//                  </tr>';
//        }
//        echo '</table>';
//        echo "<div id='pagenav'>" . $nav->renderNav() . '</div>';
//    }
//    require_once __DIR__ . '/admin_footer.php';
//}
//
//function updateMimeValue()
//{
//    global $mimetypeHandler;
//    $start  = $limit = 0;
//    $helper = Xhelp\Helper::getInstance();
//
//    if (Request::hasVar('limit', 'GET')) {
//        $limit = Request::getInt('limit', 0, 'GET');
//    }
//    if (Request::hasVar('start', 'GET')) {
//        $start = Request::getInt('start', 0, 'GET');
//    }
//
//    if (!isset($_REQUEST['id'])) {
//        $helper->redirect('admin/mimetypes.php', 3, _AM_XHELP_MESSAGE_NO_ID);
//    } else {
//        $mime_id = Request::getInt('id', 0, 'REQUEST');
//    }
//
//    $mimetype = &$mimetypeHandler->get($mime_id);
//
//    if (Request::hasVar('mime_admin', 'REQUEST')) {
//        $mime_admin = Request::getInt('mime_admin', 0, 'REQUEST');
//        $mime_admin = changeMimeValue($mime_admin);
//        $mimetype->setVar('mime_admin', $mime_admin);
//    }
//    if (Request::hasVar('mime_user', 'REQUEST')) {
//        $mime_user = Request::getInt('mime_user', 0, 'REQUEST');
//        $mime_user = changeMimeValue($mime_user);
//        $mimetype->setVar('mime_user', $mime_user);
//    }
//    if ($mimetypeHandler->insert($mimetype, true)) {
//        $helper->redirect("mimetypes.php?limit=$limit&start=$start");
//    } else {
//        $helper->redirect("mimetypes.php?limit=$limit&start=$start", 3);
//    }
//}
//
///**
// * @param int $mime_value
// * @return int
// */
//function changeMimeValue(int $mime_value): int
//{
//    if (1 == $mime_value) {
//        $mime_value = 0;
//    } else {
//        $mime_value = 1;
//    }
//
//    return $mime_value;
//}
//
//function clearAddSessionVars()
//{
//    $session = Xhelp\Session::getInstance();
//    $session->del('xhelp_addMime');
//    $session->del('xhelp_addMimeErr');
//}
//
//function clearAddSession()
//{
//    clearAddSessionVars();
//    redirect_header(Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'add'], false));
//}
//
///**
// * @param int|string $id
// */
//function clearEditSessionVars($id)
//{
//    $id      = (int)$id;
//    $session = Xhelp\Session::getInstance();
//    $session->del("xhelp_editMime_$id");
//    $session->del("xhelp_editMimeErr_$id");
//}
//
//function clearEditSession()
//{
//    $mimeid = $_REQUEST['id'];
//    clearEditSessionVars($mimeid);
//    redirect_header(Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'edit', 'id' => $mimeid], false));
//}
