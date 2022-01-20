<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

/**
 * class ReportRendererFactory
 */
class ReportRendererFactory
{
    /**
     * ReportRendererFactory constructor.
     */
    public function __construct()
    {
        // Constructor
    }

    /**
     * @param string $type
     * @param string $report
     * @return bool
     */
    public static function getRenderer(string $type, string $report): bool
    {
        $ret = false;
        if ('' === $type) {
            return $ret;
        }

        // Check rendererValid function
        $isValid = self::_rendererValid($type);

        if ($isValid) {
            // Step 2 - include script with faq adapter class
            //            require_once \XHELP_RPT_RENDERER_PATH . '/' . $type . 'ReportRenderer.php';

            // Step 3 - create instance of adapter class
            //            $classname = 'xhelp' . $type . 'ReportRenderer';

            $classname = __NAMESPACE__ . '\ReportRenderer\\' . \ucfirst($type . 'ReportRenderer');
            if (!\class_exists($classname)) {
                throw new \RuntimeException("Class '$classname' not found");
            }

            // Step 4 - return adapter class
            $ret = new $classname($report);

            return $ret;
        }

        return $ret;
        //XHELP_RPT_RENDERER_PATH
    }

    /**
     * @param string $type
     * @return bool
     */
    private static function _rendererValid(string $type): bool
    {
        // Make sure this is a valid file
        if (\is_file(\XHELP_RPT_RENDERER_PATH . '/' . $type . 'ReportRenderer.php')) {
            return true;
        }

        return false;
    }
}
