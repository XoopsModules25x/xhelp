<?php

use XoopsModules\Xhelp;

//function xhelpAdminFooter()
//{
//    echo "<br><div class='center;'><a target='_BLANK' href='http://www.3dev.org'><img src='".XHELP_IMAGE_URL."/3Dev_xhelp.png'></a></div>";
//}

/**
 * Check the status of the supplied directory
 * Thanks to the NewBB2 Development Team and SmartFactory
 * @param      $path
 * @param bool $getStatus
 * @return int|string
 */
function &xhelp_admin_getPathStatus($path, $getStatus = false)
{
    if (empty($path)) {
        return false;
    }
    $url_path = urlencode($path);
    if (@is_writable($path)) {
        $pathCheckResult = 1;
        $path_status     = _AM_XHELP_PATH_AVAILABLE;
    } elseif (!@is_dir($path)) {
        $pathCheckResult = -1;
        $path_status     = _AM_XHELP_PATH_NOTAVAILABLE . " [<a href=main.php?op=createdir&amp;path=$url_path>" . _AM_XHELP_TEXT_CREATETHEDIR . '</a>]';
    } else {
        $pathCheckResult = -2;
        $path_status     = _AM_XHELP_PATH_NOTWRITABLE . " [<a href=main.php?op=setperm&amp;path=$url_path>" . _AM_XHELP_TEXT_SETPERM . '</a>]';
    }
    if (!$getStatus) {
        return $path_status;
    } else {
        return $pathCheckResult;
    }
}

/**
 * Thanks to the NewBB2 Development Team and SmartFactory
 * @param $target
 * @return bool
 */
function xhelp_admin_mkdir($target)
{
    // http://www.php.net/manual/en/function.mkdir.php
    // saint at corenova.com
    // bart at cdasites dot com
    if (is_dir($target) || empty($target)) {
        return true;
    } // best case check first
    if (file_exists($target) && !is_dir($target)) {
        return false;
    }
    if (xhelp_admin_mkdir(substr($target, 0, strrpos($target, '/')))) {
        if (!file_exists($target)) {
            return mkdir($target);
        }
    } // crawl back up & create dir tree

    return true;
}

/**
 * Thanks to the NewBB2 Development Team and SmartFactory
 * @param     $target
 * @param int $mode
 * @return bool
 */
function xhelp_admin_chmod($target, $mode = 0777)
{
    return @chmod($target, $mode);
}

/**
 * @param string $dirName
 * @param bool   $getResolved
 * @return string
 */
function xhelpDirsize($dirName = '.', $getResolved = false)
{
    $dir  = dir($dirName);
    $size = 0;

    if ($getResolved) {
        $hTicket = Xhelp\Helper::getInstance()->getHandler('Ticket');
        $hFile   = Xhelp\Helper::getInstance()->getHandler('File');

        $tickets = $hTicket->getObjectsByState(1);

        $aTickets = [];
        foreach ($tickets as $ticket) {
            $aTickets[$ticket->getVar('id')] = $ticket->getVar('id');
        }

        // Retrieve all unresolved ticket attachments
        $crit   = new \Criteria('ticketid', '(' . implode($aTickets, ',') . ')', 'IN');
        $files  = $hFile->getObjects($crit);
        $aFiles = [];
        foreach ($files as $f) {
            $aFiles[$f->getVar('id')] = $f->getVar('filename');
        }
    }

    while ($file = $dir->read()) {
        if ('.' !== $file && '..' !== $file) {
            if (is_dir($file)) {
                $size += dirsize($dirName . '/' . $file);
            } else {
                if ($getResolved) {
                    if (!in_array($file, $aFiles)) {     // Skip unresolved files
                        $size += filesize($dirName . '/' . $file);
                    }
                } else {
                    $size += filesize($dirName . '/' . $file);
                }
            }
        }
    }
    $dir->close();

    return Xhelp\Utility::prettyBytes($size);
}

/**
 * @param $control
 * @return mixed
 */
function xhelpGetControlLabel($control)
{
    if ($controlArr = xhelpGetControl($control)) {
        return $controlArr['label'];
    } else {
        return $control;
    }
}

/**
 * @param $control
 * @return bool|mixed
 */
function &xhelpGetControl($control)
{
    $controls = xhelpGetControlArray();
    if (isset($controls[$control])) {
        return $controls[$control];
    } else {
        return false;
    }
}

/**
 * @return array
 */
function &xhelpGetControlArray()
{
    $ret = [
        XHELP_CONTROL_TXTBOX   => [
            'label'        => _XHELP_CONTROL_DESC_TXTBOX,
            'needs_length' => true,
            'needs_values' => false
        ],
        XHELP_CONTROL_TXTAREA  => [
            'label'        => _XHELP_CONTROL_DESC_TXTAREA,
            'needs_length' => true,
            'needs_values' => false
        ],
        XHELP_CONTROL_SELECT   => [
            'label'        => _XHELP_CONTROL_DESC_SELECT,
            'needs_length' => true,
            'needs_values' => true
        ],
        //Search issues?
        //XHELP_CONTROL_MULTISELECT => _XHELP_CONTROL_DESC_MULTISELECT,
        XHELP_CONTROL_YESNO    => [
            'label'        => _XHELP_CONTROL_DESC_YESNO,
            'needs_length' => false,
            'needs_values' => false
        ],
        //Search issues?
        //XHELP_CONTROL_CHECKBOX => _XHELP_CONTROL_DESC_CHECKBOX,
        XHELP_CONTROL_RADIOBOX => [
            'label'        => _XHELP_CONTROL_DESC_RADIOBOX,
            'needs_length' => true,
            'needs_values' => true
        ],
        XHELP_CONTROL_DATETIME => [
            'label'        => _XHELP_CONTROL_DESC_DATETIME,
            'needs_length' => false,
            'needs_values' => false
        ],
        XHELP_CONTROL_FILE     => [
            'label'        => _XHELP_CONTROL_DESC_FILE,
            'needs_length' => false,
            'needs_values' => false
        ]
    ];

    return $ret;
}

/**
 * @param        $err_arr
 * @param string $reseturl
 */
function xhelpRenderErrors(&$err_arr, $reseturl = '')
{
    if (is_array($err_arr) && count($err_arr) > 0) {
        echo '<div id="readOnly" class="errorMsg" style="border:1px solid #D24D00; background:#FEFECC url(' . XHELP_IMAGE_URL . '/important-32.png) no-repeat 7px 50%;color:#333;padding-left:45px;">';

        echo '<h4 style="text-align:left;margin:0; padding-top:0;">' . _AM_XHELP_MSG_SUBMISSION_ERR;

        if ($reseturl) {
            echo ' <a href="' . $reseturl . '">[' . _AM_XHELP_TEXT_SESSION_RESET . ']</a>';
        }

        echo '</h4><ul>';

        foreach ($err_arr as $key => $error) {
            if (is_array($error)) {
                foreach ($error as $err) {
                    echo '<li><a href="#' . $key . '" onclick="var e = xoopsGetElementById(\'' . $key . '\'); e.focus();">' . htmlspecialchars($err) . '</a></li>';
                }
            } else {
                echo '<li><a href="#' . $key . '" onclick="var e = xoopsGetElementById(\'' . $key . '\'); e.focus();">' . htmlspecialchars($error) . '</a></li>';
            }
        }
        echo '</ul></div><br>';
    }
}

/**
 * @param $string
 * @return mixed|string
 */
function removeAccents($string)
{
    $chars['in']  = chr(128)
                    . chr(131)
                    . chr(138)
                    . chr(142)
                    . chr(154)
                    . chr(158)
                    . chr(159)
                    . chr(162)
                    . chr(165)
                    . chr(181)
                    . chr(192)
                    . chr(193)
                    . chr(194)
                    . chr(195)
                    . chr(196)
                    . chr(197)
                    . chr(199)
                    . chr(200)
                    . chr(201)
                    . chr(202)
                    . chr(203)
                    . chr(204)
                    . chr(205)
                    . chr(206)
                    . chr(207)
                    . chr(209)
                    . chr(210)
                    . chr(211)
                    . chr(212)
                    . chr(213)
                    . chr(214)
                    . chr(216)
                    . chr(217)
                    . chr(218)
                    . chr(219)
                    . chr(220)
                    . chr(221)
                    . chr(224)
                    . chr(225)
                    . chr(226)
                    . chr(227)
                    . chr(228)
                    . chr(229)
                    . chr(231)
                    . chr(232)
                    . chr(233)
                    . chr(234)
                    . chr(235)
                    . chr(236)
                    . chr(237)
                    . chr(238)
                    . chr(239)
                    . chr(241)
                    . chr(242)
                    . chr(243)
                    . chr(244)
                    . chr(245)
                    . chr(246)
                    . chr(248)
                    . chr(249)
                    . chr(250)
                    . chr(251)
                    . chr(252)
                    . chr(253)
                    . chr(255);
    $chars['out'] = 'EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy';
    if (seemsUtf8($string)) {
        $invalid_latin_chars = [
            chr(197) . chr(146)            => 'OE',
            chr(197) . chr(147)            => 'oe',
            chr(197) . chr(160)            => 'S',
            chr(197) . chr(189)            => 'Z',
            chr(197) . chr(161)            => 's',
            chr(197) . chr(190)            => 'z',
            chr(226) . chr(130) . chr(172) => 'E'
        ];
        $string              = utf8_decode(strtr($string, $invalid_latin_chars));
    }
    $string              = strtr($string, $chars['in'], $chars['out']);
    $double_chars['in']  = [
        chr(140),
        chr(156),
        chr(198),
        chr(208),
        chr(222),
        chr(223),
        chr(230),
        chr(240),
        chr(254)
    ];
    $double_chars['out'] = ['OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th'];
    $string              = str_replace($double_chars['in'], $double_chars['out'], $string);

    return $string;
}

/**
 * @param $Str
 * @return bool
 */
function seemsUtf8($Str)
{ # by bmorel at ssi dot fr
    for ($i = 0, $iMax = strlen($Str); $i < $iMax; ++$i) {
        if (ord($Str[$i]) < 0x80) {
            continue;
        } # 0bbbbbbb
        elseif (0xC0 == (ord($Str[$i]) & 0xE0)) {
            $n = 1;
        } # 110bbbbb
        elseif (0xE0 == (ord($Str[$i]) & 0xF0)) {
            $n = 2;
        } # 1110bbbb
        elseif (0xF0 == (ord($Str[$i]) & 0xF8)) {
            $n = 3;
        } # 11110bbb
        elseif (0xF8 == (ord($Str[$i]) & 0xFC)) {
            $n = 4;
        } # 111110bb
        elseif (0xFC == (ord($Str[$i]) & 0xFE)) {
            $n = 5;
        } # 1111110b
        else {
            return false;
        } # Does not match any model
        for ($j = 0; $j < $n; ++$j) { # n bytes matching 10bbbbbb follow ?
            if ((++$i == strlen($Str)) || (0x80 != (ord($Str[$i]) & 0xC0))) {
                return false;
            }
        }
    }

    return true;
}

/**
 * @param $field
 * @return mixed|string
 */
function sanitizeFieldName($field)
{
    $field = removeAccents($field);
    $field = strtolower($field);
    $field = preg_replace('/&.+?;/', '', $field); // kill entities
    $field = preg_replace('/[^a-z0-9 _-]/', '', $field);
    $field = preg_replace('/\s+/', ' ', $field);
    $field = str_replace(' ', '-', $field);
    $field = preg_replace('|-+|', '-', $field);
    $field = trim($field, '-');

    return $field;
}

/**
 * @return bool
 */
function xhelpCreateDepartmentVisibility()
{
    $hDepartments = Xhelp\Helper::getInstance()->getHandler('Department');
    $hGroups      = xoops_getHandler('group');
    $hGroupPerm   = xoops_getHandler('groupperm');
    $xoopsModule  = Xhelp\Utility::getModule();

    $module_id = $xoopsModule->getVar('mid');

    // Get array of all departments
    $departments = $hDepartments->getObjects(null, true);

    // Get array of groups
    $groups  = $hGroups->getObjects(null, true);
    $aGroups = [];
    foreach ($groups as $group_id => $group) {
        $aGroups[$group_id] = $group->getVar('name');
    }

    foreach ($departments as $dept) {
        $deptID = $dept->getVar('id');

        // Remove old group permissions
        $crit = new \CriteriaCompo(new \Criteria('gperm_modid', $module_id));
        $crit->add(new \Criteria('gperm_itemid', $deptID));
        $crit->add(new \Criteria('gperm_name', _XHELP_GROUP_PERM_DEPT));
        $hGroupPerm->deleteAll($crit);

        foreach ($aGroups as $group => $group_name) {     // Add new group permissions
            $hGroupPerm->addRight(_XHELP_GROUP_PERM_DEPT, $deptID, $group, $module_id);
        }

        // Todo: Possibly add text saying, "Visibility for Department x set"
    }

    return true;
}
