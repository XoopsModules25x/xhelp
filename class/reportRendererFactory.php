<?php
//

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

/**
 * Class XHelpReportRendererFactory
 */
class XHelpReportRendererFactory
{
    /**
     * XHelpReportRendererFactory constructor.
     */
    public function __construct()
    {
        // Constructor
    }

    /**
     * @param $type
     * @param $report
     * @return bool
     */
    public static function getRenderer($type, &$report)
    {
        $ret = false;
        if ('' == $type) {
            return $ret;
        }

        // Check rendererValid function
        $isValid = XHelpReportRendererFactory::_rendererValid($type);

        if ($isValid) {
            // Step 2 - include script with faq adapter class
            require_once XHELP_RPT_RENDERER_PATH . '/' . $type . 'ReportRenderer.php';

            // Step 3 - create instance of adapter class
            $classname = 'xhelp' . $type . 'ReportRenderer';

            // Step 4 - return adapter class
            $ret = new $classname($report);

            return $ret;
        } else {
            return $ret;
        }
        //XHELP_RPT_RENDERER_PATH
    }

    /**
     * @param $type
     * @return bool
     */
    public static function _rendererValid($type)
    {
        // Make sure this is a valid file
        if (is_file(XHELP_RPT_RENDERER_PATH . '/' . $type . 'ReportRenderer.php')) {
            return true;
        } else {
            return false;
        }
    }
}
