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

namespace XoopsModules\Xhelp\Faq;

use XoopsModules\Xhelp;
use XoopsModules\Smartsection\Helper as AdapterHelper;
use XoopsModules\Smartsection\Constants as AdapterConstants;

//Sanity Check: make sure that file is not being accessed directly
if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// ** Define any site specific variables here **
//\define('XHELP_SSECTION_PATH', XOOPS_ROOT_PATH . '/modules/smartsection');
//\define('XHELP_SSECTION_URL', \XHELP_SITE_URL . '/modules/smartsection');
// ** End site specific variables **

// What features should be enabled for new smartsection items
//\define('XHELP_SSECTION_DOHTML', 0);
//\define('XHELP_SSECTION_DOSMILEY', 1);
//\define('XHELP_SSECTION_DOBBCODE', 1);
//\define('XHELP_SSECTION_DOIMAGE', 0);
//\define('XHELP_SSECTION_DOBR', 1);
//\define('XHELP_SSECTION_NOTIFYPUB', 1);
//\define('XHELP_SSECTION_FORCEAPPROVAL', 0); //Should articles be reviewed prior to submission (0 = Always No, 1 = Always Yes, 2 = Follow Module Config

// @todo - can this declaration be moved into the initialization sequence so
// that each class does not need to include its interface?
//Include the base faqAdapter interface (required)
// require_once XHELP_CLASS_PATH . '/faqAdapter.php';

//These functions are required to work with the smartsection application directly
//@require \XHELP_SSECTION_PATH . '/include/functions.php';

/**
 * class Smartsection
 */
class Smartsection extends Xhelp\FaqAdapterAbstract
{
    /**
     * Does application support categories?
     * Possible Values:
     * XHELP_FAQ_CATEGORY_SING - entries can be in 1 category
     * XHELP_FAQ_CATEGORY_MULTI - entries can be in more than 1 category
     * XHELP_FAQ_CATEGORY_NONE - No category support
     */
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
     * module_dir - module directory name (not needed if class overloads the isActive() function from Xhelp\FaqAdapterAbstract)
     */
    public $meta = [
        'name'            => 'SmartSection',
        'author'          => 'Brian Wahoff',
        'author_email'    => 'ackbarr@xoops.org',
        'description'     => 'Create SmartSection pages from xHelp helpdesk tickets',
        'version'         => '1.0',
        'tested_versions' => '1.05 Beta 1',
        'url'             => 'https://www.smartfactory.ca/',
        'module_dir'      => 'smartsection',
    ];

    /**
     * Class Constructor (Required)
     */
    public function __construct()
    {
        if (\class_exists(AdapterHelper::class)) {
            $this->helper  = AdapterHelper::getInstance();
            $this->dirname = $this->helper->dirname();
        }
        // Every class should call parent::init() to ensure that all class level
        // variables are initialized properly.
        parent::init();
    }

    /**
     * getCategories: retrieve the categories for the module
     * @return array|bool Array of Xhelp\FaqCategory
     */
    public function &getCategories()
    {
        $ret = false;
        //        if (!\class_exists('XoopsModules\Smartsection\Helper')) {
        //            return false;
        //        }
        if (null === $this->helper) {
            return $ret;
        }

        $ret    = [];
        $helper = Xhelp\Helper::getInstance();
        // Create an instance of the Xhelp\FaqCategoryHandler
        $faqCategoryHandler = $helper->getHandler('FaqCategory');

        // Get all the categories for the application
        $smartsectionCategoryHandler = $this->helper->getHandler('Category');
        $categories                  = $smartsectionCategoryHandler->getCategories(0, 0, -1);

        //Convert the module specific category to the
        //Xhelp\FaqCategory object for standarization
        foreach ($categories as $category) {
            $faqcat = $faqCategoryHandler->create();
            $faqcat->setVar('id', $category->getVar('categoryid'));
            $faqcat->setVar('parent', $category->getVar('parentid'));
            $faqcat->setVar('name', $category->getVar('name'));
            $ret[] = $faqcat;
        }
        unset($categories);
        \ksort($ret);

        return $ret;
    }

    /**
     * storeFaq: store the FAQ in the application's specific database (required)
     * @param Xhelp\Faq|null $faq The faq to add
     * @return bool     true (success) / false (failure)
     */
    public function storeFaq(Xhelp\Faq $faq = null): bool
    {
        global $xoopsUser, $smartsection_itemHandler;

        $uid = $xoopsUser->getVar('uid');

        if (!\class_exists('XoopsModules\Smartsection\Helper')) {
            return false;
        }

        //fix for smartsectionItem::store assuming that smartsection handlers are globalized
        $GLOBALS['smartsection_itemHandler']     = AdapterHelper::getInstance()
            ->getHandler('Item');
        $GLOBALS['smartsection_categoryHandler'] = AdapterHelper::getInstance()
            ->getHandler('Category');

        //        $ssConfig = XoopsModules\Smartsection\Utility::getModuleConfig();

        // Create page in smartsection from Xhelp\Faq object
        $smartsectionItemHandler = AdapterHelper::getInstance()
            ->getHandler('Item');
        $itemObj                 = $smartsectionItemHandler->create();

        //$faq->getVar('categories') is an array. If your application
        //only supports single categories use the first element
        //in the array
        $categories = $faq->getVar('categories');
        $categories = (int)$categories[0];       // Change array of categories to 1 category

        // Putting the values about the ITEM in the ITEM object
        $itemObj->setVar('categoryid', $categories);
        $itemObj->setVar('title', $faq->getVar('subject', 'e'));
        $itemObj->setVar('summary', '[b]' . \ucfirst(\_XHELP_TEXT_PROBLEM) . "[/b]\r\n" . $faq->getVar('problem', 'e'));
        $itemObj->setVar('body', '[b]' . \ucfirst(\_XHELP_TEXT_SOLUTION) . "[/b]\r\n" . $faq->getVar('solution', 'e'));

        $itemObj->setVar('dohtml', \XHELP_SSECTION_DOHTML);
        $itemObj->setVar('dosmiley', \XHELP_SSECTION_DOSMILEY);
        $itemObj->setVar('doxcode', \XHELP_SSECTION_DOBBCODE);
        $itemObj->setVar('doimage', \XHELP_SSECTION_DOIMAGE);
        $itemObj->setVar('dobr', \XHELP_SSECTION_DOBR);
        $itemObj->setVar('notifypub', \XHELP_SSECTION_NOTIFYPUB);
        $itemObj->setVar('uid', $uid);
        $itemObj->setVar('datesub', \time());

        // Setting the status of the item
        if ($this->articleNeedsApproval()) {
            $itemObj->setVar('status', AdapterConstants::SMARTSECTION_STATUS_SUBMITTED);
        } else {
            $itemObj->setVar('status', AdapterConstants::SMARTSECTION_STATUS_PUBLISHED);
        }

        // Storing the item object in the database
        $ret = $itemObj->store();
        if ($ret && null !== $faq) {
            $faq->setVar('id', $itemObj->getVar('itemid'));
            $faq->setVar('url', $this->makeFaqUrl($faq));

            if ($this->articleNeedsApproval()) {
                if (\XHELP_SSECTION_NOTIFYPUB) {
                    require_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
                    /** @var \XoopsNotificationHandler $notificationHandler */
                    $notificationHandler = \xoops_getHandler('notification');
                    $notificationHandler->subscribe('item', $itemObj->itemid(), 'approved', \XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE);
                }
                // Send notifications
                $itemObj->sendNotifications([AdapterConstants::SMARTSECTION_NOT_ITEM_SUBMITTED]);
            } else {
                // Send notifications
                $itemObj->sendNotifications([AdapterConstants::SMARTSECTION_NOT_ITEM_PUBLISHED]);
            }
        }

        return $ret;
    }

    /**
     * Create the url going to the faq article
     *
     * @param Xhelp\Faq $faq object
     * @return string
     */
    public function makeFaqUrl(Xhelp\Faq $faq): string
    {
        return \XHELP_SSECTION_URL . '/item.php?itemid=' . $faq->getVar('id');
    }

    /**
     * @return bool
     */
    private function articleNeedsApproval(): bool
    {
        $smartsectionHelper = AdapterHelper::getInstance();
        return (\XHELP_SSECTION_FORCEAPPROVAL == 2 && 0 === $smartsectionHelper->getConfig('perm_autoapprove'))
               || \XHELP_SSECTION_FORCEAPPROVAL == 1;
    }
}
