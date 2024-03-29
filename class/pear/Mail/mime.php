<?php
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002  Richard Heyes                                     |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.|
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Richard Heyes <richard@phpguru.org>                           |
// |         Tomas V.V.Cox <cox@idecnet.com> (port to PEAR)                |
// +-----------------------------------------------------------------------+
//

require_once XHELP_PEAR_PATH . '/PEAR.php';
require_once XHELP_PEAR_PATH . '/Mail/mimePart.php';

/**
 * Mime mail composer class. Can handle: text and html bodies, embedded html
 * images and attachments.
 * Documentation and examples of this class are avaible here:
 * https://pear.php.net/manual/
 *
 * @notes   This class is based on HTML Mime Mail class from
 *   Richard Heyes <richard@phpguru.org> which was based also
 *   in the mime_mail.class by Tobias Ratschiller <tobias@dnet.it> and
 *   Sascha Schumann <sascha@schumann.cx>.
 *
 * @author  Richard Heyes <richard.heyes@heyes-computing.net>
 * @author  Tomas V.V.Cox <cox@idecnet.com>
 */
class Mail_mime
{
    /**
     * Contains the plain text part of the email
     * @var string
     */
    public $_txtbody;
    /**
     * Contains the html part of the email
     * @var string
     */
    public $_htmlbody;
    /**
     * contains the mime encoded text
     * @var string
     */
    public $_mime;
    /**
     * contains the multipart content
     * @var string
     */
    public $_multipart;
    /**
     * list of the attached images
     * @var array
     */
    public $_html_images = [];
    /**
     * list of the attachements
     * @var array
     */
    public $_parts = [];
    /**
     * Build parameters
     * @var array
     */
    public $_build_params = [];
    /**
     * Headers for the mail
     * @var array
     */
    public $_headers = [];
    /*
     * Constructor function
     *
     * @access public
     */

    /**
     * Mail_mime constructor.
     * @param string $crlf
     */
    public function __construct(string $crlf = "\r\n")
    {
        if (!defined('MAIL_MIME_CRLF')) {
            define('MAIL_MIME_CRLF', $crlf, true);
        }

        $this->_boundary = '=_' . md5(uniqid(time(), true));

        $this->_build_params = [
            'text_encoding' => '7bit',
            'html_encoding' => 'quoted-printable',
            '7bit_wrap'     => 998,
            'html_charset'  => 'ISO-8859-1',
            'text_charset'  => 'ISO-8859-1',
            'head_charset'  => 'ISO-8859-1',
        ];
    }

    /**
     * Accessor function to set the body text. Body text is used if
     * it's not an html mail being sent or else is used to fill the
     * text/plain part that emails clients who don't support
     * html should show.
     *
     * @param string $data   Either a string or the file name with the
     *                       contents
     * @param bool   $isfile If true the first param should be trated
     *                       as a file name, else as a string (default)
     * @param bool   $append If true the text or file is appended to the
     *                       existing body, else the old body is overwritten
     * @return bool|\myservices_PEAR_Error|object|\PEAR_Error|string true on success or PEAR_Error object
     * @access public
     */
    public function setTXTBody(string $data, bool $isfile = false, bool $append = false)
    {
        if ($isfile) {
            $cont = $this->_file2str($data);
            if (PEAR::isError($cont)) {
                return $cont;
            }
            if ($append) {
                $this->_txtbody .= $cont;
            } else {
                $this->_txtbody = $cont;
            }
        } else {
            if (!$append) {
                $this->_txtbody = $data;
            } else {
                $this->_txtbody .= $data;
            }
        }

        return true;
    }

    /**
     * Adds a html part to the mail
     *
     * @param string $data   Either a string or the file name with the
     *                       contents
     * @param bool   $isfile If true the first param should be trated
     *                       as a file name, else as a string (default)
     * @return bool|\myservices_PEAR_Error|object|\PEAR_Error|string true on success or PEAR_Error object
     */
    public function setHTMLBody(string $data, bool $isfile = false)
    {
        if ($isfile) {
            $cont = $this->_file2str($data);
            if (PEAR::isError($cont)) {
                return $cont;
            }
            $this->_htmlbody = $cont;
        } else {
            $this->_htmlbody = $data;
        }

        return true;
    }

    /**
     * Adds an image to the list of embedded images.
     *
     * @param string $file       The image file name OR image data itself
     * @param string $c_type     The content type
     * @param string $name       The filename of the image. Only use if $file is the image data
     * @param bool   $isfilename Whether $file is a filename or not. Defaults to true
     * @return bool|\myservices_PEAR_Error|object|\PEAR_Error|string true on success or PEAR_Error object
     * @access public
     */
    public function addHTMLImage(string $file, string $c_type = 'application/octet-stream', string $name = '', bool $isfilename = true)
    {
        $filedata = ($isfilename) ? $this->_file2str($file) : $file;
        $filename = ($isfilename) ? basename($file) : basename($name);
        if (PEAR::isError($filedata)) {
            return $filedata;
        }
        $this->_html_images[] = [
            'body'   => $filedata,
            'name'   => $filename,
            'c_type' => $c_type,
            'cid'    => md5(uniqid(time(), true)),
        ];

        return true;
    }

    /**
     * Adds a file to the list of attachments.
     *
     * @param string $file       The file name of the file to attach OR the file data itself
     * @param string $c_type     The content type
     * @param string $name       The filename of the attachment. Only use if $file is the file data
     * @param bool   $isfilename Whether $file is a filename or not. Defaults to true
     * @return bool|\myservices_PEAR_Error|object|\PEAR_Error|string true on success or PEAR_Error object
     * @access public
     */
    public function addAttachment(string $file, string $c_type = 'application/octet-stream', string $name = '', bool $isfilename = true, string $encoding = 'base64')
    {
        $filedata = ($isfilename) ? $this->_file2str($file) : $file;
        if ($isfilename) {
            // Force the name the user supplied, otherwise use $file
            $filename = !empty($name) ? $name : $file;
        } else {
            $filename = $name;
        }
        if (empty($filename)) {
            return PEAR::raiseError('The supplied filename for the attachment can\'t be empty');
        }
        $filename = basename($filename);
        if (PEAR::isError($filedata)) {
            return $filedata;
        }

        $this->_parts[] = [
            'body'     => $filedata,
            'name'     => $filename,
            'c_type'   => $c_type,
            'encoding' => $encoding,
        ];

        return true;
    }

    /**
     * Returns the contents of the given file name as string
     * @param string $file_name
     * @return false|\myservices_PEAR_Error|object|\PEAR_Error|string
     * @acces private
     */
    private function &_file2str(string $file_name)
    {
        if (!is_readable($file_name)) {
            return PEAR::raiseError('File is not readable ' . $file_name);
        }
        if (!$fd = fopen($file_name, 'rb')) {
            return PEAR::raiseError('Could not open ' . $file_name);
        }
        $cont = fread($fd, filesize($file_name));
        fclose($fd);

        return $cont;
    }

    /**
     * Adds a text subpart to the mimePart object and
     * returns it during the build process.
     *
     * @param mixed  $obj  The object to add the part to, or
     *                     null if a new object is to be created.
     * @param string $text The text to add.
     * @return \Mail_mimePart  The text mimePart object
     */

    private function &_addTextPart($obj, string $text): Mail_mimePart
    {
        $params['content_type'] = 'text/plain';
        $params['encoding']     = $this->_build_params['text_encoding'];
        $params['charset']      = $this->_build_params['text_charset'];
        if (is_object($obj)) {
            return $obj->addSubPart($text, $params);
        }

        return new Mail_mimePart($text, $params);
    }

    /**
     * Adds a html subpart to the mimePart object and
     * returns it during the build process.
     *
     * @param mixed $obj The object to add the part to, or
     *                   null if a new object is to be created.
     * @return \Mail_mimePart  The html mimePart object
     */
    private function &_addHtmlPart($obj): Mail_mimePart
    {
        $params['content_type'] = 'text/html';
        $params['encoding']     = $this->_build_params['html_encoding'];
        $params['charset']      = $this->_build_params['html_charset'];
        if (is_object($obj)) {
            return $obj->addSubPart($this->_htmlbody, $params);
        }

        return new Mail_mimePart($this->_htmlbody, $params);
    }

    /**
     * Creates a new mimePart object, using multipart/mixed as
     * the initial content-type and returns it during the
     * build process.
     *
     * @return \Mail_mimePart  The multipart/mixed mimePart object
     */
    private function &_addMixedPart(): Mail_mimePart
    {
        $params['content_type'] = 'multipart/mixed';

        return new Mail_mimePart('', $params);
    }

    /**
     * Adds a multipart/alternative part to a mimePart
     * object, (or creates one), and returns it  during
     * the build process.
     *
     * @param mixed $obj The object to add the part to, or
     *                   null if a new object is to be created.
     * @return \Mail_mimePart  The multipart/mixed mimePart object
     */
    private function &_addAlternativePart($obj): Mail_mimePart
    {
        $params['content_type'] = 'multipart/alternative';
        if (is_object($obj)) {
            return $obj->addSubPart('', $params);
        }

        return new Mail_mimePart('', $params);
    }

    /**
     * Adds a multipart/related part to a mimePart
     * object, (or creates one), and returns it  during
     * the build process.
     *
     * @param mixed $obj The object to add the part to, or
     *                   null if a new object is to be created.
     * @return \Mail_mimePart  The multipart/mixed mimePart object
     */
    private function &_addRelatedPart($obj): Mail_mimePart
    {
        $params['content_type'] = 'multipart/related';
        if (is_object($obj)) {
            return $obj->addSubPart('', $params);
        }

        return new Mail_mimePart('', $params);
    }

    /**
     * Adds an html image subpart to a mimePart object
     * and returns it during the build process.
     *
     * @param Mail_mimePart $obj   The mimePart to add the image to
     * @param array         $value The image information
     * @return void The image mimePart object
     */
    private function &_addHtmlImagePart(Mail_mimePart $obj, array $value): void
    {
        $params['content_type'] = $value['c_type'];
        $params['encoding']     = 'base64';
        $params['disposition']  = 'inline';
        $params['dfilename']    = $value['name'];
        $params['cid']          = $value['cid'];
        $obj->addSubPart($value['body'], $params);
    }

    /**
     * Adds an attachment subpart to a mimePart object
     * and returns it during the build process.
     *
     * @param Mail_mimePart $obj   The mimePart to add the image to
     * @param array         $value The attachment information
     * @return void The image mimePart object
     * @access private
     */
    private function &_addAttachmentPart(Mail_mimePart $obj, array $value): void
    {
        $params['content_type'] = $value['c_type'];
        $params['encoding']     = $value['encoding'];
        $params['disposition']  = 'attachment';
        $params['dfilename']    = $value['name'];
        $obj->addSubPart($value['body'], $params);
    }

    /**
     * Builds the multipart message from the list ($this->_parts) and
     * returns the mime content.
     *
     * @param array|null $build_params  Build parameters that change the way the email
     *                                  is built. Should be associative. Can contain:
     *                                  text_encoding  -  What encoding to use for plain text
     *                                  Default is 7bit
     *                                  html_encoding  -  What encoding to use for html
     *                                  Default is quoted-printable
     *                                  7bit_wrap      -  Number of characters before text is
     *                                  wrapped in 7bit encoding
     *                                  Default is 998
     *                                  html_charset   -  The character set to use for html.
     *                                  Default is iso-8859-1
     *                                  text_charset   -  The character set to use for text.
     *                                  Default is iso-8859-1
     *                                  head_charset   -  The character set to use for headers.
     *                                  Default is iso-8859-1
     * @return false|mixed The mime content
     */
    public function get(array $build_params = null)
    {
        if (isset($build_params)) {
            foreach ($build_params as $key => $value) {
                $this->_build_params[$key] = $value;
            }
        }

        if (!empty($this->_html_images) && isset($this->_htmlbody)) {
            foreach ($this->_html_images as $value) {
                $this->_htmlbody = str_replace($value['name'], 'cid:' . $value['cid'], $this->_htmlbody);
            }
        }

        $null        = null;
        $attachments = !empty($this->_parts);
        $html_images = !empty($this->_html_images);
        $html        = !empty($this->_htmlbody);
        $text        = (!$html and !empty($this->_txtbody));

        switch (true) {
            case $text and !$attachments:
                $message = &$this->_addTextPart($null, $this->_txtbody);
                break;
            case !$text and !$html and $attachments:
                $message = &$this->_addMixedPart();

                for ($i = 0, $iMax = count($this->_parts); $i < $iMax; ++$i) {
                    $this->_addAttachmentPart($message, $this->_parts[$i]);
                }
                break;
            case $text and $attachments:
                $message = &$this->_addMixedPart();
                $this->_addTextPart($message, $this->_txtbody);

                for ($i = 0, $iMax = count($this->_parts); $i < $iMax; ++$i) {
                    $this->_addAttachmentPart($message, $this->_parts[$i]);
                }
                break;
            case $html and !$attachments and !$html_images:
                if (isset($this->_txtbody)) {
                    $message = &$this->_addAlternativePart($null);
                    $this->_addTextPart($message, $this->_txtbody);
                    $this->_addHtmlPart($message);
                } else {
                    $message = &$this->_addHtmlPart($null);
                }
                break;
            case $html and !$attachments and $html_images:
                if (isset($this->_txtbody)) {
                    $message = &$this->_addAlternativePart($null);
                    $this->_addTextPart($message, $this->_txtbody);
                    $related = &$this->_addRelatedPart($message);
                } else {
                    $message = &$this->_addRelatedPart($null);
                    $related = &$message;
                }
                $this->_addHtmlPart($related);
                for ($i = 0, $iMax = count($this->_html_images); $i < $iMax; ++$i) {
                    $this->_addHtmlImagePart($related, $this->_html_images[$i]);
                }
                break;
            case $html and $attachments and !$html_images:
                $message = &$this->_addMixedPart();
                if (isset($this->_txtbody)) {
                    $alt = &$this->_addAlternativePart($message);
                    $this->_addTextPart($alt, $this->_txtbody);
                    $this->_addHtmlPart($alt);
                } else {
                    $this->_addHtmlPart($message);
                }
                for ($i = 0, $iMax = count($this->_parts); $i < $iMax; ++$i) {
                    $this->_addAttachmentPart($message, $this->_parts[$i]);
                }
                break;
            case $html and $attachments and $html_images:
                $message = &$this->_addMixedPart();
                if (isset($this->_txtbody)) {
                    $alt = &$this->_addAlternativePart($message);
                    $this->_addTextPart($alt, $this->_txtbody);
                    $rel = &$this->_addRelatedPart($alt);
                } else {
                    $rel = &$this->_addRelatedPart($message);
                }
                $this->_addHtmlPart($rel);
                for ($i = 0, $iMax = count($this->_html_images); $i < $iMax; ++$i) {
                    $this->_addHtmlImagePart($rel, $this->_html_images[$i]);
                }
                for ($i = 0, $iMax = count($this->_parts); $i < $iMax; ++$i) {
                    $this->_addAttachmentPart($message, $this->_parts[$i]);
                }
                break;
        }

        if (isset($message)) {
            $output         = $message->encode();
            $this->_headers = array_merge($this->_headers, $output['headers']);

            return $output['body'];
        }

        return false;
    }

    /**
     * Returns an array with the headers needed to prepend to the email
     * (MIME-Version and Content-Type). Format of argument is:
     * $array['header-name'] = 'header-value';
     *
     * @param array|null $xtra_headers Assoc array with any extra headers. Optional.
     * @return array Assoc array with the mime headers
     */
    public function headers(array $xtra_headers = null): array
    {
        // Content-Type header should already be present,
        // So just add mime version header
        $headers['MIME-Version'] = '1.0';
        if (isset($xtra_headers)) {
            $headers = array_merge($headers, $xtra_headers);
        }
        $this->_headers = array_merge($headers, $this->_headers);

        return $this->_encodeHeaders($this->_headers);
    }

    /**
     * Get the text version of the headers
     * (usefull if you want to use the PHP mail() function)
     *
     * @param array|null $xtra_headers Assoc array with any extra headers. Optional.
     * @return string Plain text headers
     */
    public function txtHeaders(array $xtra_headers = null): string
    {
        $headers = $this->headers($xtra_headers);
        $ret     = '';
        foreach ($headers as $key => $val) {
            $ret .= "$key: $val" . MAIL_MIME_CRLF;
        }

        return $ret;
    }

    /**
     * Sets the Subject header
     *
     * @param string $subject String to set the subject to
     *                        access  public
     */
    public function setSubject(string $subject): void
    {
        $this->_headers['Subject'] = $subject;
    }

    /**
     * Set an email to the From (the sender) header
     *
     * @param string $email The email direction to add
     */
    public function setFrom(string $email): void
    {
        $this->_headers['From'] = $email;
    }

    /**
     * Add an email to the Cc (carbon copy) header
     * (multiple calls to this method is allowed)
     *
     * @param string $email The email direction to add
     */
    public function addCc(string $email): void
    {
        if (isset($this->_headers['Cc'])) {
            $this->_headers['Cc'] .= ", $email";
        } else {
            $this->_headers['Cc'] = $email;
        }
    }

    /**
     * Add an email to the Bcc (blank carbon copy) header
     * (multiple calls to this method is allowed)
     *
     * @param string $email The email direction to add
     */
    public function addBcc(string $email): void
    {
        if (isset($this->_headers['Bcc'])) {
            $this->_headers['Bcc'] .= ", $email";
        } else {
            $this->_headers['Bcc'] = $email;
        }
    }

    /**
     * Encodes a header as per RFC2047
     *
     * @param array $input The header data to encode
     * @return array Encoded data
     */
    public function _encodeHeaders(array $input): array
    {
        foreach ($input as $hdr_name => $hdr_value) {
            preg_match_all('/(\w*[\x80-\xFF]+\w*)/', $hdr_value, $matches);
            foreach ($matches[1] as $value) {
                $replacement = preg_replace('/([\x80-\xFF])/e', '"=" . strtoupper(dechex(ord("\1")))', $value);
                $hdr_value   = str_replace($value, '=?' . $this->_build_params['head_charset'] . '?Q?' . $replacement . '?=', $hdr_value);
            }
            $input[$hdr_name] = $hdr_value;
        }

        return $input;
    }
} // End of class
