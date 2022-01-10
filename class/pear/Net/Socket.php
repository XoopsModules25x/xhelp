<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | https://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Stig Bakken <ssb@fast.no>                                   |
// |          Chuck Hagenbuch <chuck@horde.org>                           |
// +----------------------------------------------------------------------+
//
// $Id: Socket.php,v 1.1 2005/02/08 20:13:02 ackbarr Exp $
//

require_once XHELP_PEAR_PATH . '/PEAR.php';

/**
 * Generalized Socket class. More docs to be written.
 *
 * @version 1.0
 * @author  Stig Bakken <ssb@fast.no>
 * @author  Chuck Hagenbuch <chuck@horde.org>
 */
class Net_Socket extends PEAR
{
    // {{{ properties
    /** Socket file pointer. */
    public $fp = null;
    /** Whether the socket is blocking. */
    public $blocking = true;
    /** Whether the socket is persistent. */
    public $persistent = false;
    /** The IP address to connect to. */
    public $addr = '';
    /** The port number to connect to. */
    public $port = 0;
    /** Number of seconds to wait on socket connections before
     * assuming there's no more data. */
    public $timeout = false;
    /** Number of bytes to read at a time in readLine() and
     * readAll(). */
    public $lineLength = 2048;
    // }}}

    // {{{ constructor

    /**
     * Constructs a new Net_Socket object.
     */
    public function __construct()
    {
        parent::__construct();
    }

    // }}}

    // {{{ connect()

    /**
     * Connect to the specified port. If called when the socket is
     * already connected, it disconnects and connects again.
     *
     * @param string    $addr       IP address or host name
     * @param int       $port       TCP port number
     * @param bool|null $persistent (optional) whether the connection is
     *                              persistent (kept open between requests by the web server)
     * @param int|null  $timeout    (optional) how long to wait for data
     * @return bool|object|\PEAR_Error true on success or error object
     */
    public function connect(string $addr, int $port, $persistent = null, $timeout = null)
    {
        if (is_resource($this->fp)) {
            @fclose($this->fp);
            $this->fp = null;
        }

        if (strspn($addr, '.0123456789') == mb_strlen($addr)) {
            $this->addr = $addr;
        } else {
            $this->addr = gethostbyname($addr);
        }
        $this->port = $port % 65536;
        if (null !== $persistent) {
            $this->persistent = $persistent;
        }
        if (null !== $timeout) {
            $this->timeout = $timeout;
        }
        $openfunc = $this->persistent ? 'pfsockopen' : 'fsockopen';
        $errno    = 0;
        $errstr   = '';
        if ($this->timeout) {
            $fp = $openfunc($this->addr, $this->port, $errno, $errstr, $this->timeout);
        } else {
            $fp = $openfunc($this->addr, $this->port, $errno, $errstr);
        }

        if (!$fp) {
            return $this->raiseError($errstr, $errno);
        }

        $this->fp = $fp;

        return $this->setBlocking($this->blocking);
    }

    // }}}

    // {{{ disconnect()

    /**
     * Disconnects from the peer, closes the socket.
     *
     * @return bool|object|\PEAR_Error true on success or an error object otherwise
     */
    public function disconnect()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
            $this->fp = null;

            return true;
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ isBlocking()

    /**
     * Find out if the socket is in blocking mode.
     *
     * @return bool the current blocking mode.
     */
    public function isBlocking(): bool
    {
        return $this->blocking;
    }

    // }}}

    // {{{ setBlocking()

    /**
     * Sets whether the socket connection should be blocking or
     * not. A read call to a non-blocking socket will return immediately
     * if there is no data available, whereas it will block until there
     * is data for blocking sockets.
     *
     * @param bool $mode true for blocking sockets, false for nonblocking
     * @return bool|object|\PEAR_Error true on success or an error object otherwise
     */
    public function setBlocking(bool $mode)
    {
        if (is_resource($this->fp)) {
            $this->blocking = $mode;
            stream_set_blocking($this->fp, $this->blocking);

            return true;
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ setTimeout()

    /**
     * Sets the timeout value on socket descriptor,
     * expressed in the sum of seconds and microseconds
     *
     * @param int $seconds      seconds
     * @param int $microseconds microseconds
     * @return bool|object|\PEAR_Error true on success or an error object otherwise
     */
    public function setTimeout(int $seconds, int $microseconds)
    {
        if (is_resource($this->fp)) {
            stream_set_timeout($this->fp, $seconds, $microseconds);

            return true;
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ getStatus()

    /**
     * Returns information about an existing socket resource.
     * Currently returns four entries in the result array:
     *
     * <p>
     * timed_out (bool) - The socket timed out waiting for data<br>
     * blocked (bool) - The socket was blocked<br>
     * eof (bool) - Indicates EOF event<br>
     * unread_bytes (int) - Number of bytes left in the socket buffer<br>
     * </p>
     *
     * @return array|object|\PEAR_Error Array containing information about existing socket resource or an error object otherwise
     */
    public function getStatus()
    {
        if (is_resource($this->fp)) {
            return stream_get_meta_data($this->fp);
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ gets()

    /**
     * Get a specified line of data
     *
     * @param $size
     * @return bool|object|string bytes of data from the socket, or a PEAR_Error if
     *               not connected.
     */
    public function gets($size)
    {
        if (is_resource($this->fp)) {
            return fgets($this->fp, $size);
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ read()

    /**
     * Read a specified amount of data. This is guaranteed to return,
     * and has the added benefit of getting everything in one fread()
     * chunk; if you know the size of the data you're getting
     * beforehand, this is definitely the way to go.
     *
     * @param int $size number of bytes to read from the socket.
     * @return bool|object|string|PEAR_Error bytes of data from the socket, or a PEAR_Error if not connected.
     */
    public function read(int $size)
    {
        if (is_resource($this->fp)) {
            return fread($this->fp, $size);
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ write()

    /**
     * Write a specified amount of data.
     *
     * @param string $data
     * @return false|int|object|\PEAR_Error true on success or an error object otherwise
     */
    public function write(string $data)
    {
        if (is_resource($this->fp)) {
            return fwrite($this->fp, $data);
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ writeLine()

    /**
     * Write a line of data to the socket, followed by a trailing "\r\n".
     *
     * @param string $data
     * @return false|int|object|\PEAR_Error fputs result, or an error
     */
    public function writeLine(string $data)
    {
        if (is_resource($this->fp)) {
            return $this->write($data . "\r\n");
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ eof()

    /**
     * Tests for end-of-file on a socket descriptor
     *
     * @return bool
     */
    public function eof(): bool
    {
        return (is_resource($this->fp) && feof($this->fp));
    }

    // }}}

    // {{{ readByte()

    /**
     * Reads a byte of data
     *
     * @return int|object 1 byte of data from the socket, or a PEAR_Error if
     *           not connected.
     */
    public function readByte()
    {
        if (is_resource($this->fp)) {
            return ord($this->read(1));
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ readWord()

    /**
     * Reads a word of data
     *
     * @return int|object 1 word of data from the socket, or a PEAR_Error if
     *           not connected.
     */
    public function readWord()
    {
        if (is_resource($this->fp)) {
            $buf = $this->read(2);

            return (ord($buf[0]) + (ord($buf[1]) << 8));
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ readInt()

    /**
     * Reads an int of data
     *
     * @return int|object 1 int of data from the socket, or a PEAR_Error if
     *           not connected.
     */
    public function readInt()
    {
        if (is_resource($this->fp)) {
            $buf = $this->read(4);

            return (ord($buf[0]) + (ord($buf[1]) << 8) + (ord($buf[2]) << 16) + (ord($buf[3]) << 24));
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ readString()

    /**
     * Reads a zeroterminated string of data
     *
     * @return string, or a PEAR_Error if
     *                 not connected.
     */
    public function readString(): string
    {
        if (is_resource($this->fp)) {
            $string = '';
            while ("\x00" !== ($char = $this->read(1))) {
                $string .= $char;
            }

            return $string;
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ readIPAddress()

    /**
     * Reads an IP Address and returns it in a dot formated string
     *
     * @return string|\PEAR_Error Dot formated string, or a PEAR_Error if
     *             not connected.
     */
    public function readIPAddress()
    {
        if (is_resource($this->fp)) {
            $buf = $this->read(4);

            return sprintf('%s.%s.%s.%s', ord($buf[0]), ord($buf[1]), ord($buf[2]), ord($buf[3]));
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ readLine()

    /**
     * Read until either the end of the socket or a newline, whichever
     * comes first. Strips the trailing newline from the returned data.
     *
     * @return object|\PEAR_Error|string  All available data up to a newline, without that
     *             newline, or until the end of the socket, or a PEAR_Error if
     *             not connected.
     */
    public function readLine()
    {
        if (is_resource($this->fp)) {
            $line    = '';
            $timeout = time() + $this->timeout;
            while (!$this->eof() && (!$this->timeout || time() < $timeout)) {
                $line .= $this->gets($this->lineLength);
                if (mb_strlen($line) >= 2
                    && ("\r\n" === mb_substr($line, -2)
                        || "\n" === mb_substr($line, -1))) {
                    return rtrim($line);
                }
            }

            return $line;
        }

        return $this->raiseError('not connected');
    }

    // }}}

    // {{{ readAll()

    /**
     * Read until the socket closes. THIS FUNCTION WILL NOT EXIT if the
     * socket is in blocking mode until the socket closes.
     *
     * @return object|PEAR_Error|string All data until the socket closes, or a PEAR_Error if
     *             not connected.
     */
    public function readAll()
    {
        if (is_resource($this->fp)) {
            $data = '';
            while (!$this->eof()) {
                $data .= $this->read($this->lineLength);
            }

            return $data;
        }

        return $this->raiseError('not connected');
    }
    // }}}
}
