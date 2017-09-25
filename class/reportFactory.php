<?php
//

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

/**
 * Class XHelpReportFactory
 */
class XHelpReportFactory
{
    /**
     * @param $name
     * @return bool
     */
    public function getReport($name)
    {
        $report = false;
        if ('' != $name) {
            $classname = 'xhelp' . ucfirst($name) . 'Report';
            require_once XHELP_REPORT_PATH . "/$name.php";
            $report = new $classname();
        }

        return $report;
    }

    /**
     * @return array
     */
    public static function getReports()
    {
        $aReports = [];

        // Step 1 - directory listing of all files in /reports directory
        $report_dir = @ dir(XHELP_REPORT_PATH);
        if ($report_dir) {
            while (false !== ($file = $report_dir->read())) {
                $meta = [];
                if (preg_match('|^\.+$|', $file)) {
                    continue;
                }
                if (preg_match('|\.php$|', $file)) {
                    $filename = basename($file, '.php'); // Get name without file extension

                    // Check that class exists in file
                    //                    $report_data = implode('', file(XHELP_REPORT_PATH . '/' . $file));
                    $report_data = file_get_contents(XHELP_REPORT_PATH . '/' . $file);
                    $classname   = 'xhelp' . ucfirst($filename) . 'Report';
                    if (preg_match("|class $classname(.*)|i", $report_data) > 0) {
                        require_once XHELP_REPORT_PATH . "/$file";
                        $aReports[$filename] = new $classname();
                    }
                    unset($report_data);
                }
            }
        }

        return $aReports;
    }
}
