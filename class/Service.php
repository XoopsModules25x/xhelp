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
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       XOOPS Development Team
 */

/**
 * Service class
 *
 * Part of the Messaging Subsystem. Provides the base interface for subscribing, unsubcribing from events
 *
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 */
class Service
{
    public  $_cookies = [];
    private $eventService;
    private $helper;

    /**
     * @param string  $eventName
     * @param Service $callback
     */
    public function attachEvent(string $eventName, Service $callback): void
    {
        $this->addCookie($eventName, (string)$this->eventService->advise($eventName, $callback));
    }

    public function init(): void
    {
        $this->eventService = EventService::getInstance();
        $this->attachEvents();
    }

    public function attachEvents(): void
    {
        //Do nothing (must implement this function in subclasses)
    }

    public function detachEvents(): void
    {
        foreach ($this->_cookies as $event => $cookie) {
            if (\is_array($cookie)) {
                foreach ($cookie as $ele) {
                    $this->eventService->unadvise($event, $ele);
                }
            } else {
                $this->eventService->unadvise($event, $cookie);
            }
        }
        $this->_cookies = [];
    }

    /**
     * @param string $eventName
     */
    public function detachFromEvent(string $eventName): void
    {
        if (isset($this->_cookies[$eventName])) {
            $cookie = $this->_cookies[$eventName];
            if (\is_array($cookie)) {
                foreach ($cookie as $ele) {
                    $this->eventService->unadvise($eventName, $ele);
                }
            } else {
                $this->eventService->unadvise($eventName, $cookie);
            }
            unset($this->_cookies[$eventName]);
        }
    }

    /**
     * @param string $eventName
     * @param string $cookie
     */
    private function addCookie(string $eventName, string $cookie): void
    {
        //Check if the cookie already exist
        if (!isset($this->_cookies[$eventName])) {
            //Cookie doesn't exist
            $this->_cookies[$eventName] = $cookie;
        } elseif (\is_array($this->_cookies[$eventName])) {
            //Already an array, just add new cookie to array
            $this->_cookies[$eventName][] = $cookie;
        } else {
            //A single value, take value and replace it with an array
            $oldCookie                  = $this->_cookies[$eventName];
            $this->_cookies[$eventName] = [$oldCookie, $cookie];
        }
    }
}
