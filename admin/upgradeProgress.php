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

require_once __DIR__ . '/admin_header.php';

echo '<html>
      <head>
          <title>' . _AM_XHELP_UPDATE_DB . '</title>
      </head>';
echo "<table width='95%' border='0'>";
echo '<tr><th>' . _AM_XHELP_UPDATE_DB . '</th></tr>';
echo "<tr><td><img src='" . XHELP_IMAGE_URL . "/progress.gif'></td></tr>";
echo '</table>';
echo '</html>';
