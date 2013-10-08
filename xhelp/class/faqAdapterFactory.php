<?php
// $Id: faqAdapterFactory.php,v 1.4 2005/11/23 20:56:54 ackbarr Exp $

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

Class xhelpFaqAdapterFactory {
    /**
     * Retrieve an array of filenames for all installed adapters
     *
     * @return array xhelpFaqAdapter filenames
     *
     */
    function &installedAdapters()
    {
        $aAdapters = array();

        // Step 1 - directory listing of all files in class/faq/ directory
        $adapters_dir = @ dir(XHELP_FAQ_ADAPTER_PATH);
        if ($adapters_dir) {
            while(($file = $adapters_dir->read()) !== false) {
                if (preg_match('|^\.+$|', $file)){
                    continue;
                }
                if (preg_match('|\.php$|', $file)){
                    $modname = basename($file, ".php"); // Get name without file extension

                    // Check that class exists in file
                    $adapter_data = implode('', file(XHELP_FAQ_ADAPTER_PATH.'/'.$file));
                    $classname = 'xhelp'.ucfirst($modname).'Adapter';
                    if(preg_match("|class $classname(.*)|i", $adapter_data) > 0){
                        include_once(XHELP_FAQ_ADAPTER_PATH . "/$file");
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
     */
    function &getFaqAdapter($name = '')
    {
        // Step 1 - Retrieve configured faq application
        $ret = false;
        if($name == ''){
            $name = xhelpGetMeta('faq_adapter');
            if($name == ''){
                return $ret;
            }
        }

        // Check adapterValid function
        $isValid = xhelpFaqAdapterFactory::_adapterValid($name);

        if($isValid){
            // Step 2 - include script with faq adapter class
            require_once(XHELP_FAQ_ADAPTER_PATH .'/'.$name.'.php');

            // Step 3 - create instance of adapter class
            $classname = 'xhelp'.$name.'Adapter';

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
     * @return BOOL true (success) / false (failure)
     */
    function setFaqAdapter($name)
    {
        // Step 1 - check that $name is a valid adapter
        $isValid = xhelpFaqAdapterFactory::_adapterValid($name);


        // Step 2 - store in xhelp_meta table
        $ret = false;
        if($isValid){
            $ret = xhelpSetMeta('faq_adapter', $name);
        }

        // Step 3 - return true/false
        return $ret;
    }

    /**
     * Check if an adapter exists
     *
     * @return BOOL true (success) / false (failure)
     */
    function _adapterValid($name)
    {
        $ret = false;
        // Step 0 - Make sure this is a valid file
        if (is_file(XHELP_FAQ_ADAPTER_PATH . '/'. $name. '.php')) {
            // Step 1 - create instance of faq adapter class
            if(include_once(XHELP_FAQ_ADAPTER_PATH .'/'.$name.'.php')){
                $classname = 'xhelp'.$name.'Adapter';
                $oAdapter = new $classname();

                // Step 2 - run isActive inside of adapter class
                $ret = $oAdapter->isActive($oAdapter->meta['module_dir']);
            }
        }
        // Step 3 - return value
        return $ret;
    }
}
?>