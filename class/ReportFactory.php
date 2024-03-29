<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

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

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

/**
 * class ReportFactory
 */
class ReportFactory
{
    /**
     * @param string $name
     * @return bool
     */
    public function getReport(string $name): bool
    {
        $report = false;
        if ('' != $name) {
            //            $classname = 'xhelp' . \ucfirst($name) . 'Report';
            //            require_once \XHELP_REPORT_PATH . "/$name.php";
            $classname = __NAMESPACE__ . '\Reports\\' . \ucfirst($name);
            if (!\class_exists($classname)) {
                throw new \RuntimeException("Class '$classname' not found");
            }

            $report = new $classname();
        }

        return $report;
    }

    /**
     * @return array
     */
    public static function getReports(): array
    {
        $aReports = [];

        // Step 1 - directory listing of all files in /reports directory
        $report_dir = @\dir(\XHELP_REPORT_PATH);
        if ($report_dir) {
            while (false !== ($file = $report_dir->read())) {
                $meta = [];
                if (\preg_match('|^\.+$|', $file)) {
                    continue;
                }
                if (\preg_match('|\.php$|', $file)) {
                    $filename = \basename($file, '.php'); // Get name without file extension

                    // Check that class exists in file
                    //                    $report_data = implode('', file(XHELP_REPORT_PATH . '/' . $file));
                    //                    $report_data = file_get_contents(\XHELP_REPORT_PATH . '/' . $file);
                    //                    $classname   = 'xhelp' . \ucfirst($filename) . 'Report';
                    //                    if (\preg_match("|class $classname(.*)|i", $report_data) > 0) {
                    //                        require_once \XHELP_REPORT_PATH . "/$file";
                    //                        $aReports[$filename] = new $classname();
                    //                    }
                    //                    unset($report_data);

                    if (false !== \strpos($filename, 'Report')) {
                        $classname = __NAMESPACE__ . '\Reports\\' . \ucfirst($filename);
                        if (!\class_exists($classname)) {
                            throw new \RuntimeException("Class '$classname' not found");
                        }
                        $aReports[$filename] = new $classname();
                    }
                }
            }
        }

        return $aReports;
    }
}
