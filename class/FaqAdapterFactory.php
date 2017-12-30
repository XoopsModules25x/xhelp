<?php namespace Xoopsmodules\xhelp;

//

use Xoopsmodules\xhelp;

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

/**
 * class FaqAdapterFactory
 */
class FaqAdapterFactory
{
    /**
     * Retrieve an array of filenames for all installed adapters
     *
     * @return array xhelp\FaqAdapter filenames
     *
     */
    public static function &installedAdapters()
    {
        $aAdapters = [];

        // Step 1 - directory listing of all files in class/faq/ directory
        $adapters_dir = @ dir(XHELP_FAQ_ADAPTER_PATH);
        if ($adapters_dir) {
            while (false !== ($file = $adapters_dir->read())) {
                if (preg_match('|^\.+$|', $file)) {
                    continue;
                }
                if (preg_match('|\.php$|', $file)) {
                    $modname = basename($file, '.php'); // Get name without file extension

                    // Check that class exists in file
                    //                    $adapter_data = implode('', file(XHELP_FAQ_ADAPTER_PATH . '/' . $file));
                    $adapter_data = file_get_contents(XHELP_FAQ_ADAPTER_PATH . '/' . $file);
                    $classname    = 'xhelp' . ucfirst($modname) . 'Adapter';
                    if (preg_match("|class $classname(.*)|i", $adapter_data) > 0) {
                        require_once XHELP_FAQ_ADAPTER_PATH . "/$file";
                        $aAdapters[$modname] = new $classname();
                    }
                    unset($adapter_data);
                }
            }
        }

        // Step 3 - return array of accepted filenames
        return $aAdapters;
    }

    /**
     * Retrieve an FaqAdapter class
     * @param string $name
     * @return bool
     */
    public static function getFaqAdapter($name = '')
    {
        // Step 1 - Retrieve configured faq application
        $ret = false;
        if ('' == $name) {
            $name = xhelp\Utility::getMeta('faq_adapter');
            if ('' == $name) {
                return $ret;
            }
        }

        // Check adapterValid function
        $isValid = self::_adapterValid($name);

        if ($isValid) {
            // Step 2 - include script with faq adapter class
            require_once XHELP_FAQ_ADAPTER_PATH . '/' . $name . '.php';

            // Step 3 - create instance of adapter class
            $classname = 'xhelp' . $name . 'Adapter';

            // Step 4 - return adapter class
            $ret = new $classname();

            return $ret;
        } else {
            return $ret;
        }
    }

    /**
     * Set an FaqAdapter class
     *
     * @param $name
     * @return bool true (success) / false (failure)
     */
    public function setFaqAdapter($name)
    {
        // Step 1 - check that $name is a valid adapter
        $isValid = self::_adapterValid($name);

        // Step 2 - store in xhelp_meta table
        $ret = false;
        if ($isValid) {
            $ret = xhelp\Utility::setMeta('faq_adapter', $name);
        }

        // Step 3 - return true/false
        return $ret;
    }

    /**
     * Check if an adapter exists
     *
     * @param $name
     * @return bool true (success) / false (failure)
     */
    public function _adapterValid($name)
    {
        $ret = false;
        // Step 0 - Make sure this is a valid file
        if (is_file(XHELP_FAQ_ADAPTER_PATH . '/' . $name . '.php')) {
            // Step 1 - create instance of faq adapter class
            if (require_once XHELP_FAQ_ADAPTER_PATH . '/' . $name . '.php') {
                $classname = 'xhelp' . $name . 'Adapter';
                $oAdapter  = new $classname();

                // Step 2 - run isActive inside of adapter class
                $ret = $oAdapter->isActive($oAdapter->meta['module_dir']);
            }
        }

        // Step 3 - return value
        return $ret;
    }
}
