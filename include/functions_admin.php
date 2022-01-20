<?php declare(strict_types=1);

use XoopsModules\Xhelp;

//function xhelpAdminFooter()
//{
//    echo "<br><div class='center;'><a target='_BLANK' href='https://www.3dev.org'><img src='".XHELP_IMAGE_URL."/3Dev_xhelp.png'></a></div>";
//}

/**
 * Check the status of the supplied directory
 * Thanks to the NewBB2 Development Team and SmartFactory
 * @param string $path
 * @param bool   $getStatus
 * @return int|string
 */
function xhelp_admin_getPathStatus(string $path, bool $getStatus = false)
{
    if (empty($path)) {
        return false;
    }
    $url_path = urlencode($path);
    if (@is_writable($path)) {
        $pathCheckResult = 1;
        $path_status     = _AM_XHELP_PATH_AVAILABLE;
    } elseif (@is_dir($path)) {
        $pathCheckResult = -2;
        $path_status     = _AM_XHELP_PATH_NOTWRITABLE . " [<a href=main.php?op=setperm&amp;path=$url_path>" . _AM_XHELP_TEXT_SETPERM . '</a>]';
    } else {
        $pathCheckResult = -1;
        $path_status     = _AM_XHELP_PATH_NOTAVAILABLE . " [<a href=main.php?op=createdir&amp;path=$url_path>" . _AM_XHELP_TEXT_CREATETHEDIR . '</a>]';
    }
    if (!$getStatus) {
        return $path_status;
    }

    return $pathCheckResult;
}

/**
 * Thanks to the NewBB2 Development Team and SmartFactory
 * @param string $target
 * @return bool
 */
function xhelp_admin_mkdir(string $target): bool
{
    // https://www.php.net/manual/en/function.mkdir.php
    // saint at corenova.com
    // bart at cdasites dot com
    if (empty($target) || is_dir($target)) {
        return true;
    } // best case check first
    if (is_dir($target) && !is_dir($target)) {
        return false;
    }
    if (xhelp_admin_mkdir(mb_substr($target, 0, mb_strrpos($target, '/')))) {
        if (!is_dir($target)) {
            return mkdir($target);
        }
    } // crawl back up & create dir tree

    return true;
}

/**
 * Thanks to the NewBB2 Development Team and SmartFactory
 * @param string $target
 * @param int    $mode
 * @return bool
 */
function xhelp_admin_chmod(string $target, int $mode = 0777): bool
{
    return @chmod($target, $mode);
}

/**
 * @param string $dirName
 * @param bool   $getResolved
 * @return string
 */
function xhelpDirsize(string $dirName = '.', bool $getResolved = false): string
{
    $helper = Xhelp\Helper::getInstance();
    $dir    = dir($dirName);
    $size   = 0;

    if ($getResolved) {
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = $helper->getHandler('Ticket');
        /** @var \XoopsModules\Xhelp\FileHandler $fileHandler */
        $fileHandler = $helper->getHandler('File');

        $tickets = $ticketHandler->getObjectsByState(1);

        $aTickets = [];
        foreach ($tickets as $ticket) {
            $aTickets[$ticket->getVar('id')] = $ticket->getVar('id');
        }

        // Retrieve all unresolved ticket attachments
        $criteria = new \Criteria('ticketid', '(' . implode(',', $aTickets) . ')', 'IN');
        $files    = $fileHandler->getObjects($criteria);
        $aFiles   = [];
        foreach ($files as $f) {
            $aFiles[$f->getVar('id')] = $f->getVar('filename');
        }
    }

    while ($file = $dir->read()) {
        if ('.' !== $file && '..' !== $file) {
            if (is_dir($file)) {
                //                $size += dirsize($dirName . '/' . $file);
                $size += disk_total_space($dirName . '/' . $file);
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
 * @param string $control
 * @return mixed
 */
function xhelpGetControlLabel(string $control)
{
    $controlArr = xhelpGetControl($control);
    if ($controlArr) {
        return $controlArr['label'];
    }

    return $control;
}

/**
 * @param string $control
 * @return bool|array
 */
function xhelpGetControl(string $control)
{
    $controls = xhelpGetControlArray();

    return $controls[$control] ?? false;
}

/**
 * @return array
 */
function &xhelpGetControlArray(): array
{
    $ret = [
        XHELP_CONTROL_TXTBOX   => [
            'label'        => _XHELP_CONTROL_DESC_TXTBOX,
            'needs_length' => true,
            'needs_values' => false,
        ],
        XHELP_CONTROL_TXTAREA  => [
            'label'        => _XHELP_CONTROL_DESC_TXTAREA,
            'needs_length' => true,
            'needs_values' => false,
        ],
        XHELP_CONTROL_SELECT   => [
            'label'        => _XHELP_CONTROL_DESC_SELECT,
            'needs_length' => true,
            'needs_values' => true,
        ],
        //Search issues?
        //XHELP_CONTROL_MULTISELECT => _XHELP_CONTROL_DESC_MULTISELECT,
        XHELP_CONTROL_YESNO    => [
            'label'        => _XHELP_CONTROL_DESC_YESNO,
            'needs_length' => false,
            'needs_values' => false,
        ],
        //Search issues?
        //XHELP_CONTROL_CHECKBOX => _XHELP_CONTROL_DESC_CHECKBOX,
        XHELP_CONTROL_RADIOBOX => [
            'label'        => _XHELP_CONTROL_DESC_RADIOBOX,
            'needs_length' => true,
            'needs_values' => true,
        ],
        XHELP_CONTROL_DATETIME => [
            'label'        => _XHELP_CONTROL_DESC_DATETIME,
            'needs_length' => false,
            'needs_values' => false,
        ],
        XHELP_CONTROL_FILE     => [
            'label'        => _XHELP_CONTROL_DESC_FILE,
            'needs_length' => false,
            'needs_values' => false,
        ],
    ];

    return $ret;
}

/**
 * @param array  $err_arr
 * @param string $reseturl
 */
function xhelpRenderErrors(array $err_arr, string $reseturl = '')
{
    if ($err_arr && is_array($err_arr)) {
        echo '<div id="readOnly" class="errorMsg" style="border:1px solid #D24D00; background:#FEFECC url(' . XHELP_IMAGE_URL . '/important-32.png) no-repeat 7px 50%;color:#333;padding-left:45px;">';

        echo '<h4 style="text-align:left;margin:0; padding-top:0;">' . _AM_XHELP_MSG_SUBMISSION_ERR;

        if ($reseturl) {
            echo ' <a href="' . $reseturl . '">[' . _AM_XHELP_TEXT_SESSION_RESET . ']</a>';
        }

        echo '</h4><ul>';

        foreach ($err_arr as $key => $error) {
            if (is_array($error)) {
                foreach ($error as $err) {
                    echo '<li><a href="#' . $key . '" onclick="var e = xoopsGetElementById(\'' . $key . '\'); e.focus();">' . htmlspecialchars($err, ENT_QUOTES | ENT_HTML5) . '</a></li>';
                }
            } else {
                echo '<li><a href="#' . $key . '" onclick="var e = xoopsGetElementById(\'' . $key . '\'); e.focus();">' . htmlspecialchars($error, ENT_QUOTES | ENT_HTML5) . '</a></li>';
            }
        }
        echo '</ul></div><br>';
    }
}

/**
 * @param string $string
 * @return string
 */
function removeAccents(string $string): string
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
                    . chr(
                        209
                    )
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
            chr(226) . chr(130) . chr(172) => 'E',
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
        chr(254),
    ];
    $double_chars['out'] = ['OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th'];
    $string              = str_replace($double_chars['in'], $double_chars['out'], $string);

    return $string;
}

/**
 * @param array $str
 * @return bool
 */
function seemsUtf8(array $str): bool
{ # by bmorel at ssi dot fr
    foreach ($str as $i => $iValue) {
        if (ord($iValue) < 0x80) {
            continue;
        } # 0bbbbbbb
        elseif (0xC0 == (ord($iValue) & 0xE0)) {
            $n = 1;
        } # 110bbbbb
        elseif (0xE0 == (ord($iValue) & 0xF0)) {
            $n = 2;
        } # 1110bbbb
        elseif (0xF0 == (ord($iValue) & 0xF8)) {
            $n = 3;
        } # 11110bbb
        elseif (0xF8 == (ord($iValue) & 0xFC)) {
            $n = 4;
        } # 111110bb
        elseif (0xFC == (ord($iValue) & 0xFE)) {
            $n = 5;
        } # 1111110b
        else {
            return false;
        } # Does not match any model
        for ($j = 0; $j < $n; ++$j) { # n bytes matching 10bbbbbb follow ?
            if ((++$i == mb_strlen($str)) || (0x80 != (ord($str[$i]) & 0xC0))) {
                return false;
            }
        }
    }

    return true;
}

/**
 * @param string $field
 * @return string
 */
function sanitizeFieldName(string $field): string
{
    $field = removeAccents($field);
    $field = \mb_strtolower($field);
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
function xhelpCreateDepartmentVisibility(): bool
{
    $helper = Xhelp\Helper::getInstance();

    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    /** @var \XoopsGroupHandler $groupHandler */
    $groupHandler = xoops_getHandler('group');
    /** @var \XoopsGroupPermHandler $grouppermHandler */
    $grouppermHandler = xoops_getHandler('groupperm');
    $xoopsModule      = Xhelp\Utility::getModule();

    $module_id = $xoopsModule->getVar('mid');

    // Get array of all departments
    $departments = $departmentHandler->getObjects(null, true);

    // Get array of groups
    $groups  = $groupHandler->getObjects(null, true);
    $aGroups = [];
    foreach ($groups as $group_id => $group) {
        $aGroups[$group_id] = $group->getVar('name');
    }

    foreach ($departments as $dept) {
        $deptID = $dept->getVar('id');

        // Remove old group permissions
        $criteria = new \CriteriaCompo(new \Criteria('gperm_modid', $module_id));
        $criteria->add(new \Criteria('gperm_itemid', $deptID));
        $criteria->add(new \Criteria('gperm_name', _XHELP_GROUP_PERM_DEPT));
        $grouppermHandler->deleteAll($criteria);

        foreach ($aGroups as $group => $group_name) {     // Add new group permissions
            $grouppermHandler->addRight(_XHELP_GROUP_PERM_DEPT, $deptID, $group, $module_id);
        }
        // Todo: Possibly add text saying, "Visibility for Department x set"
    }

    return true;
}
