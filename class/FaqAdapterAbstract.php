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
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/faq.php';

\define('XHELP_FAQ_CATEGORY_SING', 0);
\define('XHELP_FAQ_CATEGORY_MULTI', 1);
\define('XHELP_FAQ_CATEGORY_NONE', 2);

/**
 * class FaqAdapterAbstract
 */
abstract class FaqAdapterAbstract implements FaqAdapterInterface
{
    public $categoryType = \XHELP_FAQ_CATEGORY_SING;
    /**
     * Adapter Details
     * Required Values:
     * name - name of adapter
     * author - who wrote the plugin
     * author_email - contact email
     * version - version of this plugin
     * tested_versions - supported application versions
     * url - support url for plugin
     * module_dir - module directory name (not needed if class overloads the isActive() function from FaqAdapterAbstract)
     */
    public $meta    = [
        'name'            => '',
        'author'          => '',
        'author_email'    => '',
        'version'         => '',
        'tested_versions' => '',
        'url'             => '',
        'module_dir'      => '',
    ];
    public $modname;
    public $module;
    public $helper;
    public $dirname = '';

    /**
     * Perform any initilization needed
     */
    public function init()
    {
    }

    /**
     * Stub function (does nothing)
     */
    public function &getCategories()
    {
    }

    /**
     * @return bool true (success)/false (failure)
     */
    public function storeFaq(): bool
    {
        // Store a Faq
        return false;
    }

    /**
     * @return Faq object
     */
    public function createFaq(): Faq
    {
        // Create an faq
        $faq = new Faq();

        return $faq;
    }

    /**
     * @return bool true (success) / false (failure)
     */
    public function isActive(): bool
    {
        $module_dir  = $this->meta['module_dir'];
        $module_name = $this->meta['name'];

        if ('' === $module_dir || '' == $module_name) {      // Sanity check
            return false;
        }

        // Make sure that module is active
        /** @var \XoopsModuleHandler $moduleHandler */
        $moduleHandler = \xoops_getHandler('module');
        $mod           = $moduleHandler->getByDirname($module_dir);

        if (\is_object($mod)) {
            if ($mod->getVar('isactive')) {                       // Module active?
                $activeAdapter = Utility::getMeta('faq_adapter'); //TODO use this one or the one below?
                //                $activeAdapter = $module_name;
                if ($activeAdapter) {
                    return true;
                }

                return false;
            }

            return false;
        }

        return false;
    }

    /**
     * Create the url going to the Faq article
     *
     * @param \XoopsModules\Xhelp\Faq $faq
     * @return string
     */
    public function makeFaqUrl(\XoopsModules\Xhelp\Faq $faq): string
    {
    }
}
