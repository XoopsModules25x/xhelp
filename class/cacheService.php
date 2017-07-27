<?php
//

/**
 * xhelp_cacheService class
 *
 * Part of the Messaging Subsystem.  Responsible for updating files in the XOOPS_ROOT_PATH/cache directory
 *
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 * @access  public
 * @package xhelp
 */
class XHelpCacheService extends xhelpService
{
    /**
     * Location of Xoops Cache Directory
     *
     * @var object
     * @access  private
     */
    public $_cacheDir;
    public $_cookies = [];

    /**
     * Class Constructor
     *
     * @access  public
     */
    public function __construct()
    {
        $this->_cacheDir = XHELP_CACHE_PATH;
        $this->init();
    }

    public function _attachEvents()
    {
        $this->_attachEvent('batch_status', $this);
        $this->_attachEvent('close_ticket', $this);
        $this->_attachEvent('delete_ticket', $this);
        $this->_attachEvent('new_ticket', $this);
        $this->_attachEvent('reopen_ticket', $this);
    }

    /**
     * Reset Performance Images on 'new_ticket' event
     * @param  xhelpTicket $ticket Ticket that was added
     * @return bool        True on success, false on error
     * @access  public
     */
    public function new_ticket($ticket)
    {
        return $this->_clearPerfImages();
    }

    /**
     * Reset Performance Images on 'close_ticket' event
     * @param  xhelpTicket $ticket Ticket that was closed
     * @return bool        True on success, false on error
     * @access public
     */
    public function close_ticket($ticket)
    {
        return $this->_clearPerfImages();
    }

    /**
     * Call Backback function for 'delete_ticket'
     * @param  xhelpTicket $ticket Ticket being deleted
     * @return bool        True on success, false on error
     * @access  public
     */
    public function delete_ticket($ticket)
    {
        $hStatus = xhelpGetHandler('status');
        $status  = $hStatus->get($ticket->getVar('status'));

        if ($status->getVar('state') == XHELP_STATE_UNRESOLVED) {
            return $this->_clearPerfImages();
        }
    }

    /**
     * Reset Performance Images on 'reopen_ticket' event
     * @param  xhelpTicket $ticket Ticket that was re-opened
     * @return bool        True on success, false on error
     * @access public
     */
    public function reopen_ticket($ticket)
    {
        return $this->_clearPerfImages();
    }

    /**
     * Callback function for the 'new_department' event
     * @param  array $args Array of arguments passed to EventService
     * @return bool  True on success, false on error
     * @access  public
     */
    public function new_department($args)
    {
        return $this->_clearPerfImages();
    }

    /**
     * Callback function for the 'delete_department' event
     * @param  array $args Array of arguments passed to EventService
     * @return bool  True on success, false on error
     * @access  public
     */
    public function delete_department($args)
    {
        return $this->_clearPerfImages();
    }

    /**
     * @param $args
     * @return bool
     */
    public function batch_status($args)
    {
        return $this->_clearPerfImages();
    }

    /**
     * Removes all cached images for the Department Performance block
     * @return bool True on success, false on error
     * @access  private
     */
    public function _clearPerfImages()
    {
        //Remove all cached department queue images
        $opendir = opendir($this->_cacheDir);

        while (($file = readdir($opendir)) != null) {
            if (strpos($file, 'xhelp_perf_') === false) {
                continue;
            }

            unlink($this->_cacheDir . '/' . $file);
        }

        return true;
    }

    /**
     * Only have 1 instance of class used
     * @return object {@link xhelp_cacheService}
     * @access  public
     */
    public static function getInstance()
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }
}
