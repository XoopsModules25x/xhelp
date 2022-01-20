<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

/**
 * CacheService class
 *
 * Part of the Messaging Subsystem.  Responsible for updating files in the XOOPS_ROOT_PATH/cache directory
 *
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 */
class CacheService extends Service
{
    /**
     * Location of Xoops Cache Directory
     *
     * @var string
     */
    private $cacheDir;
    //    public $_cookies = [];

    /**
     * Class Constructor
     */
    public function __construct()
    {
        $this->cacheDir = \XHELP_CACHE_PATH;
        $this->init();
    }

    /**
     *
     */
    public function attachEvents()
    {
        $this->attachEvent('batch_status', $this);
        $this->attachEvent('close_ticket', $this);
        $this->attachEvent('delete_ticket', $this);
        $this->attachEvent('new_ticket', $this);
        $this->attachEvent('reopen_ticket', $this);
    }

    /**
     * Reset Performance Images on 'new_ticket' event
     * @param Ticket $ticket Ticket that was added
     * @return bool        True on success, false on error
     */
    public function new_ticket(Ticket $ticket): bool
    {
        return $this->clearPerfImages();
    }

    /**
     * Reset Performance Images on 'close_ticket' event
     * @param Ticket $ticket Ticket that was closed
     * @return bool        True on success, false on error
     */
    public function close_ticket(Ticket $ticket): bool
    {
        return $this->clearPerfImages();
    }

    /**
     * Call Backback function for 'delete_ticket'
     * @param Ticket $ticket Ticket being deleted
     * @return bool        True on success, false on error
     */
    public function delete_ticket(Ticket $ticket): bool
    {
        $ret    = false;
        $helper = Helper::getInstance();
        /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
        $statusHandler = $helper->getHandler('Status');
        $status        = $statusHandler->get($ticket->getVar('status'));

        if (\XHELP_STATE_UNRESOLVED == $status->getVar('state')) {
            return $this->clearPerfImages();
        }
        return $ret;
    }

    /**
     * Reset Performance Images on 'reopen_ticket' event
     * @param Ticket $ticket Ticket that was re-opened
     * @return bool        True on success, false on error
     */
    public function reopen_ticket(Ticket $ticket): bool
    {
        return $this->clearPerfImages();
    }

    /**
     * Callback function for the 'new_department' event
     * @param array $args Array of arguments passed to EventService
     * @return bool  True on success, false on error
     */
    public function new_department(array $args): bool
    {
        return $this->clearPerfImages();
    }

    /**
     * Callback function for the 'delete_department' event
     * @param array $args Array of arguments passed to EventService
     * @return bool  True on success, false on error
     */
    public function delete_department(array $args): bool
    {
        return $this->clearPerfImages();
    }

    /**
     * @param array $args
     * @return bool
     */
    public function batch_status(array $args): bool
    {
        return $this->clearPerfImages();
    }

    /**
     * Removes all cached images for the Department Performance block
     * @return bool True on success, false on error
     */
    public function clearPerfImages(): bool
    {
        //Remove all cached department queue images
        $opendir = \opendir($this->cacheDir);

        while (false !== ($file = \readdir($opendir))) {
            if (false === mb_strpos((string)$file, 'xhelp_perf_')) {
                continue;
            }

            \unlink($this->cacheDir . '/' . $file);
        }

        return true;
    }

    /**
     * Only have 1 instance of class used
     * @return Service {@link Service}
     */
    public static function getInstance(): Service
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }
}
