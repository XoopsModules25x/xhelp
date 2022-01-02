<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

if (!\defined('XHELP_CLASS_PATH')) {
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
     * @return array FaqAdapterAbstract filenames
     */
    public static function &installedAdapters(): array
    {
        $aAdapters = [];

        // Step 1 - directory listing of all files in class/faq/ directory
        $adapters_dir = @\dir(\XHELP_FAQ_ADAPTER_PATH);
        if ($adapters_dir) {
            while (false !== ($file = $adapters_dir->read())) {
                if (\preg_match('|^\.+$|', $file)) {
                    continue;
                }
                if (\preg_match('|\.php$|', $file)) {
                    $modname = \basename($file, '.php'); // Get name without file extension

                    // Step 2 - Check that class exists
                    //                    $adapter_data = implode('', file(XHELP_FAQ_ADAPTER_PATH . '/' . $file));
                    //                    $adapter_data = file_get_contents(\XHELP_FAQ_ADAPTER_PATH . '/' . $file);
                    //                     $classname    = 'xhelp' . \ucfirst($modname) . 'Adapter';

                    $class = __NAMESPACE__ . '\Faq\\' . \ucfirst($modname);
                    if (\class_exists($class)) {
                        $adapter = new $class();
                        if ($adapter instanceof FaqAdapterAbstract) {
                            //                            $dirname = $adapter->dirname;
                            $aAdapters[$modname] = $adapter;
                            //                            if (!empty($dirname) && \is_dir(XOOPS_ROOT_PATH . '/modules/' . $dirname)) {
                            //                                if ($adapter->loadModule()) {
                            //                                    $ret = $adapter;
                            //                                } else {
                            //                                    $object->setErrors(\_AM_RSSFIT_PLUGIN_MODNOTFOUND);
                            //                                }
                            //                            } else {
                            //                                $object->setErrors(\_AM_RSSFIT_PLUGIN_MODNOTFOUND);
                            //                            }
                        }
                        //                    } else {
                        //                        $object->setErrors(\_AM_RSSFIT_PLUGIN_CLASSNOTFOUND . ' ' . $class);
                        //                    }

                        //                    if (\preg_match("|class $classname(.*)|i", $adapter_data) > 0) {
                        //                        require_once \XHELP_FAQ_ADAPTER_PATH . "/$file";
                        //                        $aAdapters[$modname] = new $classname();
                        //                    }
                        //                    unset($adapter_data);
                    }
                }
            }
        }

        // Step 3 - return array of accepted filenames
        return $aAdapters;
    }

    /**
     * Retrieve an FaqAdapterAbstract class
     * @param string $name
     * @return FaqAdapterAbstract|null
     */
    public static function getFaqAdapter(string $name = ''): ?FaqAdapterAbstract
    {
        // Step 1 - Retrieve configured faq application
        $ret = null;
        if ('' == $name) {
            $name = Utility::getMeta('faq_adapter');
            if ('' == $name) {
                return $ret;
            }
        }

        // Check adapterValid function
        $isValid = self::isAdapterValid($name);

        if ($isValid) {
            // Step 2 - include script with faq adapter class
//            require_once \XHELP_FAQ_ADAPTER_PATH . '/' . $name . '.php';

            // Step 3 - create instance of adapter class
//            $classname = 'xhelp' . $name . 'Adapter';
            $classname = __NAMESPACE__ . '\Faq\\' . \ucfirst($name);
            if (!\class_exists($classname)) {
                throw new \RuntimeException("Class '$classname' not found");
            }
            $ret = new $classname();

            // Step 4 - return adapter class

            return $ret;
        }

        return $ret;
    }

    /**
     * Set an FaqAdapterAbstract class
     *
     * @param $name
     * @return bool true (success) / false (failure)
     */
    public static function setFaqAdapter($name): bool
    {
        // Step 1 - check that $name is a valid adapter
        $isValid = self::isAdapterValid($name);

        // Step 2 - store in xhelp_meta table
        $ret = false;
        if ($isValid) {
            $ret = Utility::setMeta('faq_adapter', $name);
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
    public static function isAdapterValid($name): bool
    {
        $ret = false;
        // Step 0 - Make sure this is a valid file
        if (\is_file(\XHELP_FAQ_ADAPTER_PATH . '/' . $name . '.php')) {
            // Step 1 - create instance of faq adapter class
//            if (require_once \XHELP_FAQ_ADAPTER_PATH . '/' . $name . '.php') {
//                $classname = 'xhelp' . $name . 'Adapter';
//                $oAdapter  = new $classname();
//
//                // Step 2 - run isActive inside of adapter class
//                $ret = $oAdapter->isActive($oAdapter->meta['module_dir']);
//            }



            $class = __NAMESPACE__ . '\Faq\\' . \ucfirst($name);
            if (\class_exists($class)) {
                $oAdapter = new $class();
                if ($oAdapter instanceof FaqAdapterAbstract) {
                    // Step 2 - run isActive inside of adapter class //TODO MB: are we checking if it's Valid or if it's Active?
                    $ret = $oAdapter->isActive($oAdapter->meta['module_dir']);
                }
            }





        }

        // Step 3 - return value
        return $ret;
    }
}
