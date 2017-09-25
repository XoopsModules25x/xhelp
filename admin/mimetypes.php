<?php
require_once __DIR__ . '/../../../include/cp_header.php';
require_once __DIR__ . '/admin_header.php';
require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

global $xoopsModule;
$module_id = $xoopsModule->getVar('mid');
$start     = $limit = 0;
if (isset($_REQUEST['limit'])) {
    $limit = (int)$_REQUEST['limit'];
} else {
    $limit = 15;
}
if (isset($_REQUEST['start'])) {
    $start = (int)$_REQUEST['start'];
}

$aSortBy   = [
    'mime_id'    => _AM_XHELP_MIME_ID,
    'mime_name'  => _AM_XHELP_MIME_NAME,
    'mime_ext'   => _AM_XHELP_MIME_EXT,
    'mime_admin' => _AM_XHELP_MIME_ADMIN,
    'mime_user'  => _AM_XHELP_MIME_USER
];
$aOrderBy  = ['ASC' => _AM_XHELP_TEXT_ASCENDING, 'DESC' => _AM_XHELP_TEXT_DESCENDING];
$aLimitBy  = ['10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100];
$aSearchBy = [
    'mime_id'   => _AM_XHELP_MIME_ID,
    'mime_name' => _AM_XHELP_MIME_NAME,
    'mime_ext'  => _AM_XHELP_MIME_EXT
];

$hMime = xhelpGetHandler('mimetype');
$op    = 'default';

if (isset($_REQUEST['op'])) {
    $op = $_REQUEST['op'];
}

switch ($op) {
    case 'add':
        add();
        break;

    case 'delete':
        delete();
        break;

    case 'edit':
        edit();
        break;

    case 'search':
        search();
        break;

    case 'updateMimeValue':
        updateMimeValue();
        break;

    case 'clearAddSession':
        clearAddSession();
        break;

    case 'clearEditSession':
        clearEditSession();
        break;

    case 'manage':
    default:
        manage();
        break;

}

function add()
{
    global $hMime, $limit, $start;

    if (!isset($_POST['add_mime'])) {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('mimetypes');
        $adminObject = \Xmf\Module\Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));

        $session     = Session::getInstance();
        $mime_type   = $session->get('xhelp_addMime');
        $mime_errors = $session->get('xhelp_addMimeErr');

        //Display any form errors
        if (false === !$mime_errors) {
            xhelpRenderErrors($mime_errors, xhelpMakeURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'clearAddSession']));
        }

        if (false === $mime_type) {
            $mime_ext   = '';
            $mime_name  = '';
            $mime_types = '';
            $mime_admin = 1;
            $mime_user  = 1;
        } else {
            $mime_ext   = $mime_type['mime_ext'];
            $mime_name  = $mime_type['mime_name'];
            $mime_types = $mime_type['mime_types'];
            $mime_admin = $mime_type['mime_admin'];
            $mime_user  = $mime_type['mime_user'];
        }

        // Display add form
        echo "<form action='mimetypes.php?op=add' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<table width='100%' cellspacing='1' class='outer'>";
        echo "<tr><th colspan='2'>" . _AM_XHELP_MIME_CREATEF . '</th></tr>';
        echo "<tr valign='top'>
                  <td class='head'>" . _AM_XHELP_MIME_EXTF . "</td>
                  <td class='even'><input type='text' name='mime_ext' id='mime_ext' value='$mime_ext' size='5'></td>
              </tr>";
        echo "<tr valign='top'>
                  <td class='head'>" . _AM_XHELP_MIME_NAMEF . "</td>
                  <td class='even'><input type='text' name='mime_name' id='mime_name' value='$mime_name'></td>
              </tr>";
        echo "<tr valign='top'>
                  <td class='head'>" . _AM_XHELP_MIME_TYPEF . "</td>
                  <td class='even'><textarea name='mime_types' id='mime_types' cols='60' rows='5'>$mime_types</textarea></td>
              </tr>";
        echo "<tr valign='top'>
                  <td class='head'>" . _AM_XHELP_MIME_ADMINF . "</td>
                  <td class='even'>";

        echo "<input type='radio' name='mime_admin' value='1' " . (1 == $mime_admin ? 'checked' : '') . '>' . _XHELP_TEXT_YES;
        echo "<input type='radio' name='mime_admin' value='0' " . (0 == $mime_admin ? 'checked' : '') . '>' . _XHELP_TEXT_NO . '
                  </td>
              </tr>';
        echo "<tr valign='top'>
                  <td class='head'>" . _AM_XHELP_MIME_USERF . "</td>
                  <td class='even'>";
        echo "<input type='radio' name='mime_user' value='1'" . (1 == $mime_user ? 'checked' : '') . '>' . _XHELP_TEXT_YES;
        echo "<input type='radio' name='mime_user' value='0'" . (0 == $mime_user ? 'checked' : '') . '>' . _XHELP_TEXT_NO . '
                  </td>
              </tr>';
        echo "<tr valign='top'>
                  <td class='head'></td>
                  <td class='even'>
                      <input type='submit' name='add_mime' id='add_mime' value='" . _AM_XHELP_BUTTON_SUBMIT . "' class='formButton'>
                      <input type='button' name='cancel' value='" . _AM_XHELP_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton'>
                  </td>
              </tr>";
        echo '</table></form>';
        // end of add form

        // Find new mimetypes table
        echo "<form action='http://www.filext.com' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<table width='100%' cellspacing='1' class='outer'>";
        echo "<tr><th colspan='2'>" . _AM_XHELP_MIME_FINDMIMETYPE . '</th></tr>';

        echo "<tr class='foot'>
                  <td colspan='2'><input type='submit' name='find_mime' id='find_mime' value='" . _AM_XHELP_MIME_FINDIT . "' class='formButton'></td>
              </tr>";

        echo '</table></form>';

        require_once __DIR__ . '/admin_footer.php';
    } else {
        $has_errors = false;
        $error      = [];
        $mime_ext   = $_POST['mime_ext'];
        $mime_name  = $_POST['mime_name'];
        $mime_types = $_POST['mime_types'];
        $mime_admin = (int)$_POST['mime_admin'];
        $mime_user  = (int)$_POST['mime_user'];

        //Validate Mimetype entry
        if ('' == trim($mime_ext)) {
            $has_errors          = true;
            $error['mime_ext'][] = _AM_XHELP_VALID_ERR_MIME_EXT;
        }

        if ('' == trim($mime_name)) {
            $has_errors           = true;
            $error['mime_name'][] = _AM_XHELP_VALID_ERR_MIME_NAME;
        }

        if ('' == trim($mime_types)) {
            $has_errors            = true;
            $error['mime_types'][] = _AM_XHELP_VALID_ERR_MIME_TYPES;
        }

        if ($has_errors) {
            $session            = Session::getInstance();
            $mime               = [];
            $mime['mime_ext']   = $mime_ext;
            $mime['mime_name']  = $mime_name;
            $mime['mime_types'] = $mime_types;
            $mime['mime_admin'] = $mime_admin;
            $mime['mime_user']  = $mime_user;
            $session->set('xhelp_addMime', $mime);
            $session->set('xhelp_addMimeErr', $error);
            header('Location: ' . xhelpMakeURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'add'], false));
        }

        $mimetype = $hMime->create();
        $mimetype->setVar('mime_ext', $mime_ext);
        $mimetype->setVar('mime_name', $mime_name);
        $mimetype->setVar('mime_types', $mime_types);
        $mimetype->setVar('mime_admin', $mime_admin);
        $mimetype->setVar('mime_user', $mime_user);

        if (!$hMime->insert($mimetype)) {
            redirect_header(XHELP_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start", 3, _AM_XHELP_MESSAGE_ADD_MIME_ERROR);
        } else {
            _clearAddSessionVars();
            header('Location: ' . XHELP_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start");
        }
    }
}

function delete()
{
    global $hMime, $start, $limit;

    if (!isset($_REQUEST['id'])) {
        redirect_header(XHELP_ADMIN_URL . '/mimetypes.php', 3, _AM_XHELP_MESSAGE_NO_ID);
    } else {
        $mime_id = (int)$_REQUEST['id'];
    }
    $mimetype =& $hMime->get($mime_id);     // Retrieve mimetype object
    if (!$hMime->delete($mimetype, true)) {
        redirect_header(XHELP_ADMIN_URL . "/mimetypes.php?op=manage&id=$mime_id&limit=$limit&start=$start", 3, _AM_XHELP_MESSAGE_DELETE_MIME_ERROR);
    } else {
        header('Location: ' . XHELP_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start");
    }
}

function edit()
{
    global $hMime, $start, $limit;

    if (!isset($_REQUEST['id'])) {
        redirect_header(XHELP_ADMIN_URL . '/mimetypes.php', 3, _AM_XHELP_MESSAGE_NO_ID);
    } else {
        $mime_id = (int)$_REQUEST['id'];
    }
    $mimetype =& $hMime->get($mime_id);     // Retrieve mimetype object

    if (!isset($_POST['edit_mime'])) {
        $session     = Session::getInstance();
        $mime_type   = $session->get("xhelp_editMime_$mime_id");
        $mime_errors = $session->get("xhelp_editMimeErr_$mime_id");

        // Display header
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('mimetypes');
        $adminObject = \Xmf\Module\Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));

        //Display any form errors
        if (false === !$mime_errors) {
            xhelpRenderErrors($mime_errors, xhelpMakeURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'clearEditSession', 'id' => $mime_id]));
        }

        if (false === $mime_type) {
            $mime_ext   = $mimetype->getVar('mime_ext');
            $mime_name  = $mimetype->getVar('mime_name', 'e');
            $mime_types = $mimetype->getVar('mime_types', 'e');
            $mime_admin = $mimetype->getVar('mime_admin');
            $mime_user  = $mimetype->getVar('mime_user');
        } else {
            $mime_ext   = $mime_type['mime_ext'];
            $mime_name  = $mime_type['mime_name'];
            $mime_types = $mime_type['mime_types'];
            $mime_admin = $mime_type['mime_admin'];
            $mime_user  = $mime_type['mime_user'];
        }

        // Display edit form
        echo "<form action='mimetypes.php?op=edit&amp;id=" . $mime_id . "' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<input type='hidden' name='limit' value='" . $limit . "'>";
        echo "<input type='hidden' name='start' value='" . $start . "'>";
        echo "<table width='100%' cellspacing='1' class='outer'>";
        echo "<tr><th colspan='2'>" . _AM_XHELP_MIME_MODIFYF . '</th></tr>';
        echo "<tr valign='top'>
                  <td class='head'>" . _AM_XHELP_MIME_EXTF . "</td>
                  <td class='even'><input type='text' name='mime_ext' id='mime_ext' value='$mime_ext' size='5'></td>
              </tr>";
        echo "<tr valign='top'>
                  <td class='head'>" . _AM_XHELP_MIME_NAMEF . "</td>
                  <td class='even'><input type='text' name='mime_name' id='mime_name' value='$mime_name'></td>
              </tr>";
        echo "<tr valign='top'>
                  <td class='head'>" . _AM_XHELP_MIME_TYPEF . "</td>
                  <td class='even'><textarea name='mime_types' id='mime_types' cols='60' rows='5'>$mime_types</textarea></td>
              </tr>";
        echo "<tr valign='top'>
                  <td class='head'>" . _AM_XHELP_MIME_ADMINF . "</td>
                  <td class='even'>
                      <input type='radio' name='mime_admin' value='1' " . (1 == $mime_admin ? 'checked' : '') . '>' . _XHELP_TEXT_YES . "
                      <input type='radio' name='mime_admin' value='0' " . (0 == $mime_admin ? 'checked' : '') . '>' . _XHELP_TEXT_NO . '
                  </td>
              </tr>';
        echo "<tr valign='top'>
                  <td class='head'>" . _AM_XHELP_MIME_USERF . "</td>
                  <td class='even'>
                      <input type='radio' name='mime_user' value='1' " . (1 == $mime_user ? 'checked' : '') . '>' . _XHELP_TEXT_YES . "
                      <input type='radio' name='mime_user' value='0' " . (0 == $mime_user ? 'checked' : '') . '>' . _XHELP_TEXT_NO . '
                  </td>
              </tr>';
        echo "<tr valign='top'>
                  <td class='head'></td>
                  <td class='even'>
                      <input type='submit' name='edit_mime' id='edit_mime' value='" . _AM_XHELP_BUTTON_UPDATE . "' class='formButton'>
                      <input type='button' name='cancel' value='" . _AM_XHELP_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton'>
                  </td>
              </tr>";
        echo '</table></form>';
        // end of edit form

        require_once __DIR__ . '/admin_footer.php';
    } else {
        $mime_admin = 0;
        $mime_user  = 0;
        if (isset($_POST['mime_admin']) && 1 == $_POST['mime_admin']) {
            $mime_admin = 1;
        }
        if (isset($_POST['mime_user']) && 1 == $_POST['mime_user']) {
            $mime_user = 1;
        }

        //Validate Mimetype entry
        if ('' == trim($_POST['mime_ext'])) {
            $has_errors          = true;
            $error['mime_ext'][] = _AM_XHELP_VALID_ERR_MIME_EXT;
        }

        if ('' == trim($_POST['mime_name'])) {
            $has_errors           = true;
            $error['mime_name'][] = _AM_XHELP_VALID_ERR_MIME_NAME;
        }

        if ('' == trim($_POST['mime_types'])) {
            $has_errors            = true;
            $error['mime_types'][] = _AM_XHELP_VALID_ERR_MIME_TYPES;
        }

        if ($has_errors) {
            $session            = Session::getInstance();
            $mime               = [];
            $mime['mime_ext']   = $_POST['mime_ext'];
            $mime['mime_name']  = $_POST['mime_name'];
            $mime['mime_types'] = $_POST['mime_types'];
            $mime['mime_admin'] = $mime_admin;
            $mime['mime_user']  = $mime_user;
            $session->set('xhelp_editMime_' . $mime_id, $mime);
            $session->set('xhelp_editMimeErr_' . $mime_id, $error);
            header('Location: ' . xhelpMakeURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'edit', 'id' => $mime_id], false));
        }

        $mimetype->setVar('mime_ext', $_POST['mime_ext']);
        $mimetype->setVar('mime_name', $_POST['mime_name']);
        $mimetype->setVar('mime_types', $_POST['mime_types']);
        $mimetype->setVar('mime_admin', $mime_admin);
        $mimetype->setVar('mime_user', $mime_user);

        if (!$hMime->insert($mimetype, true)) {
            redirect_header(XHELP_ADMIN_URL . "/mimetypes.php?op=edit&id=$mime_id", 3, _AM_XHELP_MESSAGE_EDIT_MIME_ERROR);
        } else {
            _clearEditSessionVars($mime_id);
            header('Location: ' . XHELP_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start");
        }
    }
}

function manage()
{
    global $hMime, $imagearray, $start, $limit, $aSortBy, $aOrderBy, $aLimitBy, $aSearchBy;

    if (isset($_POST['deleteMimes'])) {
        $aMimes = $_POST['mimes'];

        $crit = new Criteria('mime_id', '(' . implode($aMimes, ',') . ')', 'IN');

        if ($hMime->deleteAll($crit)) {
            header('Location: ' . XHELP_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start");
        } else {
            redirect_header(XHELP_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start", 3, _AM_XHELP_MESSAGE_DELETE_MIME_ERROR);
        }
    }
    if (isset($_POST['add_mime'])) {
        header('Location: ' . XHELP_ADMIN_URL . "/mimetypes.php?op=add&start=$start&limit=$limit");
        exit();
    }
    if (isset($_POST['mime_search'])) {
        header('Location: ' . XHELP_ADMIN_URL . '/mimetypes.php?op=search');
        exit();
    }

    xoops_cp_header();
    //echo $oAdminButton->renderButtons('mimetypes');
    $adminObject = \Xmf\Module\Admin::getInstance();
    $adminObject->displayNavigation(basename(__FILE__));

    $crit = new Criteria('', '');
    if (isset($_REQUEST['order'])) {
        $order = $_REQUEST['order'];
    } else {
        $order = 'ASC';
    }
    if (isset($_REQUEST['sort'])) {
        $sort = $_REQUEST['sort'];
    } else {
        $sort = 'mime_ext';
    }
    $crit->setOrder($order);
    $crit->setStart($start);
    $crit->setLimit($limit);
    $crit->setSort($sort);
    $mimetypes  = $hMime->getObjects($crit);    // Retrieve a list of all mimetypes
    $mime_count = $hMime->getCount();
    $nav        = new XoopsPageNav($mime_count, $limit, $start, 'start', "op=manage&amp;limit=$limit");

    echo '<script type="text/javascript" src="' . XHELP_BASE_URL . '/include/functions.js"></script>';
    echo "<table width='100%' cellspacing='1' class='outer'>";
    echo "<tr><td colspan='6' align='right'>";
    echo "<form action='" . XHELP_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
    echo $GLOBALS['xoopsSecurity']->getTokenHTML();
    echo '<table>';
    echo '<tr>';
    echo "<td align='right'>" . _AM_XHELP_TEXT_SEARCH_BY . '</td>';
    echo "<td align='left'><select name='search_by'>";
    foreach ($aSearchBy as $value => $text) {
        ($sort == $value) ? $selected = 'selected' : $selected = '';
        echo "<option value='$value' $selected>$text</option>";
    }
    echo '</select></td>';
    echo "<td align='right'>" . _AM_XHELP_TEXT_SEARCH_TEXT . '</td>';
    echo "<td align='left'><input type='text' name='search_text' id='search_text' value=''></td>";
    echo "<td><input type='submit' name='mime_search' id='mime_search' value='" . _AM_XHELP_BUTTON_SEARCH . "'></td>";
    echo '</tr></table></form></td></tr>';

    echo "<tr><td colspan='6'>";
    echo "<form action='" . XHELP_ADMIN_URL . "/mimetypes.php?op=manage' style='margin:0; padding:0;' method='post'>";
    echo $GLOBALS['xoopsSecurity']->getTokenHTML();
    echo "<table width='100%'>";
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
                  <input type='submit' name='mime_sort' id='mime_sort' value='" . _AM_XHELP_BUTTON_SUBMIT . "'>
              </td>
          </tr>";
    echo '</table>';
    echo '</td></tr>';
    echo "<tr><th colspan='6'>" . _AM_XHELP_MENU_MIMETYPES . '</th></tr>';
    echo "<tr class='head'>
              <td>" . _AM_XHELP_MIME_ID . '</td>
              <td>' . _AM_XHELP_MIME_NAME . '</td>
              <td>' . _AM_XHELP_MIME_EXT . '</td>
              <td>' . _AM_XHELP_MIME_ADMIN . '</td>
              <td>' . _AM_XHELP_MIME_USER . '</td>
              <td>' . _AM_XHELP_MINDEX_ACTION . '</td>
          </tr>';
    foreach ($mimetypes as $mime) {
        echo "<tr class='even'>
                  <td><input type='checkbox' name='mimes[]' value='" . $mime->getVar('mime_id') . "'>" . $mime->getVar('mime_id') . '</td>
                  <td>' . $mime->getVar('mime_name') . '</td>
                  <td>' . $mime->getVar('mime_ext') . "</td>
                  <td>
                      <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_admin=' . $mime->getVar('mime_admin') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
                      " . ($mime->getVar('mime_admin') ? $imagearray['online'] : $imagearray['offline']) . "</a>
                  </td>
                  <td>
                      <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_user=' . $mime->getVar('mime_user') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
                      " . ($mime->getVar('mime_user') ? $imagearray['online'] : $imagearray['offline']) . "</a>
                  </td>
                  <td>
                      <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=edit&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . $imagearray['editimg'] . "</a>
                      <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=delete&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . $imagearray['deleteimg'] . '</a>
                  </td>
              </tr>';
    }
    echo "<tr class='foot'>
              <td colspan='6' valign='top'>
                  <a href='http://www.filext.com' style='float: right;' target='_blank'>" . _AM_XHELP_MIME_FINDMIMETYPE . "</a>
                  <input type='checkbox' name='checkAllMimes' value='0' onclick='selectAll(this.form,\"mimes[]\",this.checked);'>
                  <input type='submit' name='deleteMimes' id='deleteMimes' value='" . _AM_XHELP_BUTTON_DELETE . "'>
                  <input type='submit' name='add_mime' id='add_mime' value='" . _AM_XHELP_MIME_CREATEF . "' class='formButton'>
              </td>
          </tr>";
    echo '</table>';
    echo "<div id='staff_nav'>" . $nav->renderNav() . '</div>';

    require_once __DIR__ . '/admin_footer.php';
}

function search()
{
    global $hMime, $limit, $start, $imagearray, $aSearchBy, $aOrderBy, $aLimitBy, $aSortBy;

    if (isset($_POST['deleteMimes'])) {
        $aMimes = $_POST['mimes'];

        $crit = new Criteria('mime_id', '(' . implode($aMimes, ',') . ')', 'IN');

        if ($hMime->deleteAll($crit)) {
            header('Location: ' . XHELP_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start");
        } else {
            redirect_header(XHELP_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start", 3, _AM_XHELP_MESSAGE_DELETE_MIME_ERROR);
        }
    }
    if (isset($_POST['add_mime'])) {
        header('Location: ' . XHELP_ADMIN_URL . "/mimetypes.php?op=add&start=$start&limit=$limit");
        exit();
    }
    if (isset($_REQUEST['order'])) {
        $order = $_REQUEST['order'];
    } else {
        $order = 'ASC';
    }
    if (isset($_REQUEST['sort'])) {
        $sort = $_REQUEST['sort'];
    } else {
        $sort = 'mime_name';
    }

    xoops_cp_header();
    //echo $oAdminButton->renderButtons('mimetypes');
    $adminObject = \Xmf\Module\Admin::getInstance();
    $adminObject->displayNavigation(basename(__FILE__));

    if (!isset($_REQUEST['mime_search'])) {
        echo "<form action='mimetypes.php?op=search' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<table width='100%' cellspacing='1' class='outer'>";
        echo "<tr><th colspan='2'>" . _AM_XHELP_TEXT_SEARCH_MIME . '</th></tr>';
        echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_SEARCH_BY . "</td>
                  <td class='even'>
                      <select name='search_by'>";
        foreach ($aSortBy as $value => $text) {
            echo "<option value='$value'>$text</option>";
        }
        echo '</select>
                  </td>
              </tr>';
        echo "<tr><td class='head'>" . _AM_XHELP_TEXT_SEARCH_TEXT . "</td>
                  <td class='even'>
                      <input type='text' name='search_text' id='search_text' value=''>
                  </td>
              </tr>";
        echo "<tr class='foot'>
                  <td colspan='2'>
                      <input type='submit' name='mime_search' id='mime_search' value='" . _AM_XHELP_BUTTON_SEARCH . "'>
                  </td>
              </tr>";
        echo '</table></form>';
    } else {
        $search_field = $_REQUEST['search_by'];
        $search_text  = $_REQUEST['search_text'];

        $crit = new Criteria($search_field, "%$search_text%", 'LIKE');
        $crit->setSort($sort);
        $crit->setOrder($order);
        $crit->setLimit($limit);
        $crit->setStart($start);
        $mime_count = $hMime->getCount($crit);
        $mimetypes  = $hMime->getObjects($crit);
        $nav        = new XoopsPageNav($mime_count, $limit, $start, 'start', "op=search&amp;limit=$limit&amp;order=$order&amp;sort=$sort&amp;mime_search=1&amp;search_by=$search_field&amp;search_text=$search_text");
        // Display results
        echo '<script type="text/javascript" src="' . XHELP_BASE_URL . '/include/functions.js"></script>';

        echo "<table width='100%' cellspacing='1' class='outer'>";
        echo "<tr><td colspan='6' align='right'>";
        echo "<form action='" . XHELP_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo '<table>';
        echo '<tr>';
        echo "<td align='right'>" . _AM_XHELP_TEXT_SEARCH_BY . '</td>';
        echo "<td align='left'><select name='search_by'>";
        foreach ($aSearchBy as $value => $text) {
            ($search_field == $value) ? $selected = 'selected' : $selected = '';
            echo "<option value='$value' $selected>$text</option>";
        }
        echo '</select></td>';
        echo "<td align='right'>" . _AM_XHELP_TEXT_SEARCH_TEXT . '</td>';
        echo "<td align='left'><input type='text' name='search_text' id='search_text' value='" . htmlentities($search_text, ENT_QUOTES) . "'></td>";
        echo "<td><input type='submit' name='mime_search' id='mime_search' value='" . _AM_XHELP_BUTTON_SEARCH . "'></td>";
        echo '</tr></table></form></td></tr>';

        echo "<tr><td colspan='6'>";
        echo "<form action='" . XHELP_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<table width='100%'>";
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
                  <input type='submit' name='mime_sort' id='mime_sort' value='" . _AM_XHELP_BUTTON_SUBMIT . "'>
                  <input type='hidden' name='mime_search' id='mime_search' value='1'>
                  <input type='hidden' name='search_by' id='search_by' value='$search_field'>
                  <input type='hidden' name='search_text' id='search_text' value='" . htmlentities($search_text, ENT_QUOTES) . "'>
              </td>
          </tr>";
        echo '</table>';
        echo '</td></tr>';
        if (count($mimetypes) > 0) {
            echo "<tr><th colspan='6'>" . _AM_XHELP_TEXT_SEARCH_MIME . '</th></tr>';
            echo "<tr class='head'>
                      <td>" . _AM_XHELP_MIME_ID . '</td>
                      <td>' . _AM_XHELP_MIME_NAME . '</td>
                      <td>' . _AM_XHELP_MIME_EXT . '</td>
                      <td>' . _AM_XHELP_MIME_ADMIN . '</td>
                      <td>' . _AM_XHELP_MIME_USER . '</td>
                      <td>' . _AM_XHELP_MINDEX_ACTION . '</td>
                  </tr>';
            foreach ($mimetypes as $mime) {
                echo "<tr class='even'>
                          <td><input type='checkbox' name='mimes[]' value='" . $mime->getVar('mime_id') . "'>" . $mime->getVar('mime_id') . '</td>
                          <td>' . $mime->getVar('mime_name') . '</td>
                          <td>' . $mime->getVar('mime_ext') . "</td>
                          <td>
                              <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_admin=' . $mime->getVar('mime_admin') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
                              " . ($mime->getVar('mime_admin') ? $imagearray['online'] : $imagearray['offline']) . "</a>
                          </td>
                          <td>
                              <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_user=' . $mime->getVar('mime_user') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
                              " . ($mime->getVar('mime_user') ? $imagearray['online'] : $imagearray['offline']) . "</a>
                          </td>
                          <td>
                              <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=edit&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . $imagearray['editimg'] . "</a>
                              <a href='" . XHELP_ADMIN_URL . '/mimetypes.php?op=delete&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . $imagearray['deleteimg'] . '</a>
                          </td>
                      </tr>';
            }
            echo "<tr class='foot'>
                      <td colspan='6' valign='top'>
                          <a href='http://www.filext.com' style='float: right;' target='_blank'>" . _AM_XHELP_MIME_FINDMIMETYPE . "</a>
                          <input type='checkbox' name='checkAllMimes' value='0' onclick='selectAll(this.form,\"mimes[]\",this.checked);'>
                          <input type='submit' name='deleteMimes' id='deleteMimes' value='" . _AM_XHELP_BUTTON_DELETE . "'>
                          <input type='submit' name='add_mime' id='add_mime' value='" . _AM_XHELP_MIME_CREATEF . "' class='formButton'>
                      </td>
                  </tr>";
        } else {
            echo '<tr><th>' . _AM_XHELP_TEXT_SEARCH_MIME . '</th></tr>';
            echo "<tr class='even'>
                      <td>" . _AM_XHELP_TEXT_NO_RECORDS . '</td>
                  </tr>';
        }
        echo '</table>';
        echo "<div id='pagenav'>" . $nav->renderNav() . '</div>';
    }
    require_once __DIR__ . '/admin_footer.php';
}

function updateMimeValue()
{
    global $hMime;
    $start = $limit = 0;

    if (isset($_GET['limit'])) {
        $limit = (int)$_GET['limit'];
    }
    if (isset($_GET['start'])) {
        $start = (int)$_GET['start'];
    }

    if (!isset($_REQUEST['id'])) {
        redirect_header(XHELP_ADMIN_URL . '/mimetypes.php', 3, _AM_XHELP_MESSAGE_NO_ID);
    } else {
        $mime_id = (int)$_REQUEST['id'];
    }

    $mimetype =& $hMime->get($mime_id);

    if (isset($_REQUEST['mime_admin'])) {
        $mime_admin = (int)$_REQUEST['mime_admin'];
        $mime_admin = _changeMimeValue($mime_admin);
        $mimetype->setVar('mime_admin', $mime_admin);
    }
    if (isset($_REQUEST['mime_user'])) {
        $mime_user = (int)$_REQUEST['mime_user'];
        $mime_user = _changeMimeValue($mime_user);
        $mimetype->setVar('mime_user', $mime_user);
    }
    if ($hMime->insert($mimetype, true)) {
        header('Location: ' . XHELP_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start");
    } else {
        redirect_header(XHELP_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start", 3);
    }
}

/**
 * @param $mime_value
 * @return int
 */
function _changeMimeValue($mime_value)
{
    if (1 == $mime_value) {
        $mime_value = 0;
    } else {
        $mime_value = 1;
    }

    return $mime_value;
}

function _clearAddSessionVars()
{
    $session = Session::getInstance();
    $session->del('xhelp_addMime');
    $session->del('xhelp_addMimeErr');
}

function clearAddSession()
{
    _clearAddSessionVars();
    header('Location: ' . xhelpMakeURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'add'], false));
}

/**
 * @param $id
 */
function _clearEditSessionVars($id)
{
    $id      = (int)$id;
    $session = Session::getInstance();
    $session->del("xhelp_editMime_$id");
    $session->del("xhelp_editMimeErr_$id");
}

function clearEditSession()
{
    $mimeid = $_REQUEST['id'];
    _clearEditSessionVars($mimeid);
    header('Location: ' . xhelpMakeURI(XHELP_ADMIN_URL . '/mimetypes.php', ['op' => 'edit', 'id' => $mimeid], false));
}
