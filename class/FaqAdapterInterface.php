<?php

namespace XoopsModules\Xhelp;

/**
 * class FaqAdapterAbstract
 */
interface FaqAdapterInterface
{
    /**
     * Perform any initilization needed
     */
    public function init();

    /**
     * Stub function (does nothing)
     */
    public function getCategories();

    /**
     * @return bool true (success)/false (failure)
     */
    public function storeFaq();

    /**
     * @return Faq object
     */
    public function createFaq(): Faq;

    /**
     * @return bool true (success) / false (failure)
     */
    public function isActive(): bool;
}
