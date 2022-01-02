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
// +-----------------------------------------------------------------------+

require_once XHELP_PEAR_PATH . '/PEAR.php';

/**
 *  +----------------------------- IMPORTANT ------------------------------+
 *  | Usage of this class compared to native php extensions such as        |
 *  | mailparse or imap, is slow and may be feature deficient. If available|
 *  | you are STRONGLY recommended to use the php extensions.              |
 *  +----------------------------------------------------------------------+
 *
 * Mime Decoding class
 *
 * This class will parse a raw mime email and return
 * the structure. Returned structure is similar to
 * that returned by imap_fetchstructure().
 *
 * USAGE: (assume $input is your raw email)
 *
 * $decode = new Mail_mimeDecode($input, "\r\n");
 * $structure = $decode->decode();
 * print_r($structure);
 *
 * Or statically:
 *
 * $params['input'] = $input;
 * $structure = Mail_mimeDecode::decode($params);
 * print_r($structure);
 *
 * TODO:
 *  - Implement further content types, eg. multipart/parallel,
 *    perhaps even message/partial.
 *
 * @author  Richard Heyes <richard@phpguru.org>
 * @version $Revision: 1.2 $
 */
class Mail_mimeDecode extends PEAR
{
    /**
     * The raw email to decode
     * @var string
     */
    public $_input;
    /**
     * The header part of the input
     * @var string
     */
    public $_header;
    /**
     * The body part of the input
     * @var string
     */
    public $_body;
    /**
     * If an error occurs, this is used to store the message
     * @var string
     */
    public $_error;
    /**
     * Flag to determine whether to include bodies in the
     * returned object.
     * @var bool
     */
    public $_include_bodies;
    /**
     * Flag to determine whether to decode bodies
     * @var bool
     */
    public $_decode_bodies;
    /**
     * Flag to determine whether to decode headers
     * @var bool
     */
    public $_decode_headers;
    /**
     * If invoked from a class, $this will be set. This has problematic
     * connotations for calling decode() statically. Hence this variable
     * is used to determine if we are indeed being called statically or
     * via an object.
     */
    public $mailMimeDecode;

    /**
     * Constructor.
     *
     * Sets up the object, initialise the variables, and splits and
     * stores the header and body of the input.
     *
     * @param mixed $input
     */
    public function __construct($input)
    {
        [$header, $body] = $this->_splitBodyHeader($input);

        $this->_input          = $input;
        $this->_header         = $header;
        $this->_body           = $body;
        $this->_decode_bodies  = false;
        $this->_include_bodies = true;

        $this->mailMimeDecode = true;
    }

    /**
     * Begins the decoding process. If called statically
     * it will create an object and call the decode() method
     * of it.
     *
     * @param null|mixed $params
     * @return object Decoded results
     */
    public function decode($params = null)
    {
        // Have we been called statically? If so, create an object and pass details to that.
        if (!isset($this->mailMimeDecode) and isset($params['input'])) {
            $obj       = new self($params['input']);
            $structure = $obj->decode($params);
            // Called statically but no input
        } elseif (!isset($this->mailMimeDecode)) {
            return PEAR::raiseError('Called statically and no input given');
            // Called via an object
        } else {
            $this->_include_bodies = $params['include_bodies'] ?? false;
            $this->_decode_bodies  = $params['decode_bodies'] ?? false;
            $this->_decode_headers = $params['decode_headers'] ?? false;

            $structure = $this->_decode($this->_header, $this->_body);
            if (false === $structure) {
                $structure = $this->raiseError($this->_error);
            }
        }

        return $structure;
    }

    /**
     * Performs the decoding. Decodes the body string passed to it
     * If it finds certain content-types it will call itself in a
     * recursive fashion
     *
     * @param        $headers
     * @param        $body
     * @param string $default_ctype
     * @return object Results of decoding process
     */
    public function _decode($headers, $body, $default_ctype = 'text/plain')
    {
        $return  = new stdClass();
        $headers = $this->_parseHeaders($headers);

        foreach ($headers as $value) {
            if (isset($return->headers[mb_strtolower($value['name'])]) and !is_array($return->headers[mb_strtolower($value['name'])])) {
                $return->headers[mb_strtolower($value['name'])]   = [$return->headers[mb_strtolower($value['name'])]];
                $return->headers[mb_strtolower($value['name'])][] = $value['value'];
            } elseif (isset($return->headers[mb_strtolower($value['name'])])) {
                $return->headers[mb_strtolower($value['name'])][] = $value['value'];
            } else {
                $return->headers[mb_strtolower($value['name'])] = $value['value'];
            }
        }

        reset($headers);
        //        while (list($key, $value) = each($headers)) {
        foreach ($headers as $key => $value) {
            $headers[$key]['name'] = \mb_strtolower($headers[$key]['name']);
            switch ($headers[$key]['name']) {
                case 'content-type':
                    $content_type = $this->_parseHeaderValue($headers[$key]['value']);

                    if (preg_match('/([0-9a-z+.-]+)\/([0-9a-z+.-]+)/i', $content_type['value'], $regs)) {
                        $return->ctype_primary   = $regs[1];
                        $return->ctype_secondary = $regs[2];
                    }

                    if (isset($content_type['other'])) {
                        while ([$p_name, $p_value] = each($content_type['other'])) {
                            $return->ctype_parameters[$p_name] = $p_value;
                        }
                    }
                    break;
                case 'content-disposition':
                    $content_disposition = $this->_parseHeaderValue($headers[$key]['value']);
                    $return->disposition = $content_disposition['value'];
                    if (isset($content_disposition['other'])) {
                        while ([$p_name, $p_value] = each($content_disposition['other'])) {
                            $return->d_parameters[$p_name] = $p_value;
                        }
                    }
                    break;
                case 'content-transfer-encoding':
                    $content_transfer_encoding = $this->_parseHeaderValue($headers[$key]['value']);
                    break;
            }
        }

        if (isset($content_type)) {
            switch (mb_strtolower($content_type['value'])) {
                case 'text/plain':
                    $encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
                    $this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $encoding) : $body) : null;
                    break;
                case 'text/html':
                    $encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
                    $this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $encoding) : $body) : null;
                    break;
                case 'multipart/parallel':
                case 'multipart/report': // RFC1892
                case 'multipart/signed': // PGP
                case 'multipart/digest':
                case 'multipart/alternative':
                case 'multipart/related':
                case 'multipart/mixed':
                    if (!isset($content_type['other']['boundary'])) {
                        $this->_error = 'No boundary found for ' . $content_type['value'] . ' part';

                        return false;
                    }

                    $default_ctype = ('multipart/digest' === \mb_strtolower($content_type['value'])) ? 'message/rfc822' : 'text/plain';

                    $parts = $this->_boundarySplit($body, $content_type['other']['boundary']);
                    for ($i = 0, $iMax = count($parts); $i < $iMax; ++$i) {
                        [$part_header, $part_body] = $this->_splitBodyHeader($parts[$i]);
                        $part = $this->_decode($part_header, $part_body, $default_ctype);
                        if (false === $part) {
                            $part = $this->raiseError($this->_error);
                        }
                        $return->parts[] = $part;
                    }
                    break;
                case 'message/rfc822':
                    $obj             = new self($body);
                    $return->parts[] = $obj->decode(['include_bodies' => $this->_include_bodies]);
                    unset($obj);
                    break;
                default:
                    if (!isset($content_transfer_encoding['value'])) {
                        $content_transfer_encoding['value'] = '7bit';
                    }
                    $this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $content_transfer_encoding['value']) : $body) : null;
                    break;
            }
        } else {
            $ctype                   = explode('/', $default_ctype);
            $return->ctype_primary   = $ctype[0];
            $return->ctype_secondary = $ctype[1];
            $this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body) : $body) : null;
        }

        return $return;
    }

    /**
     * Given the output of the above function, this will return an
     * array of references to the parts, indexed by mime number.
     *
     * @param object $structure   The structure to go through
     * @param bool   $no_refs
     * @param string $mime_number Internal use only.
     * @param string $prepend
     * @return array  Mime numbers
     */
    public function &getMimeNumbers(&$structure, $no_refs = false, $mime_number = '', $prepend = '')
    {
        $return = [];
        if (!empty($structure->parts)) {
            if ('' != $mime_number) {
                $structure->mime_id              = $prepend . $mime_number;
                $return[$prepend . $mime_number] = &$structure;
            }
            for ($i = 0, $iMax = count($structure->parts); $i < $iMax; ++$i) {
                if (!empty($structure->headers['content-type']) and 0 === mb_strpos(mb_strtolower($structure->headers['content-type']), 'message/')) {
                    $prepend      = $prepend . $mime_number . '.';
                    $_mime_number = '';
                } else {
                    $_mime_number = ('' == $mime_number ? $i + 1 : sprintf('%s.%s', $mime_number, $i + 1));
                }

                $arr = &self::getMimeNumbers($structure->parts[$i], $no_refs, $_mime_number, $prepend);
                foreach ($arr as $key => $val) {
                    $no_refs ? $return[$key] = '' : $return[$key] = &$arr[$key];
                }
            }
        } else {
            if ('' == $mime_number) {
                $mime_number = '1';
            }
            $structure->mime_id = $prepend . $mime_number;
            $no_refs ? $return[$prepend . $mime_number] = '' : $return[$prepend . $mime_number] = &$structure;
        }

        return $return;
    }

    /**
     * Given a string containing a header and body
     * section, this function will split them (at the first
     * blank line) and return them.
     *
     * @param mixed $input
     * @return array Contains header and body section
     */
    public function _splitBodyHeader($input)
    {
        if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $input, $match)) {
            return [$match[1], $match[2]];
        }
        $this->_error = 'Could not split header and body';

        return false;
    }

    /**
     * Parse headers given in $input and return
     * as assoc array.
     *
     * @param mixed $input
     * @return array Contains parsed headers
     */
    public function _parseHeaders($input)
    {
        if ('' !== $input) {
            // Unfold the input
            $input   = preg_replace("/\r?\n/", "\r\n", $input);
            $input   = preg_replace("/\r\n(\t| )+/", ' ', $input);
            $headers = explode("\r\n", trim($input));

            foreach ($headers as $value) {
                $hdr_name  = mb_substr($value, 0, $pos = mb_strpos($value, ':'));
                $hdr_value = mb_substr($value, $pos + 1);
                if (' ' === $hdr_value[0]) {
                    $hdr_value = mb_substr($hdr_value, 1);
                }

                $return[] = [
                    'name'  => $hdr_name,
                    'value' => $this->_decode_headers ? $this->_decodeHeader($hdr_value) : $hdr_value,
                ];
            }
        } else {
            $return = [];
        }

        return $return;
    }

    /**
     * Function to parse a header value,
     * extract first part, and any secondary
     * parts (after ;) This function is not as
     * robust as it could be. Eg. header comments
     * in the wrong place will probably break it.
     *
     * @param mixed $input
     * @return array Contains parsed result
     */
    public function _parseHeaderValue($input)
    {
        if (false !== ($pos = mb_strpos($input, ';'))) {
            $return['value'] = trim(mb_substr($input, 0, $pos));
            $input           = trim(mb_substr($input, $pos + 1));

            if (mb_strlen($input) > 0) {
                // This splits on a semi-colon, if there's no preceeding backslash
                // Can't handle if it's in double quotes however. (Of course anyone
                // sending that needs a good slap).
                $parameters = preg_split('/\s*(?<!\\\\);\s*/i', $input);

                for ($i = 0, $iMax = count($parameters); $i < $iMax; ++$i) {
                    $param_name  = mb_substr($parameters[$i], 0, $pos = mb_strpos($parameters[$i], '='));
                    $param_value = mb_substr($parameters[$i], $pos + 1);
                    if ('"' === $param_value[0]) {
                        $param_value = mb_substr($param_value, 1, -1);
                    }
                    $return['other'][$param_name]                = $param_value;
                    $return['other'][mb_strtolower($param_name)] = $param_value;
                }
            }
        } else {
            $return['value'] = trim($input);
        }

        return $return;
    }

    /**
     * This function splits the input based
     * on the given boundary
     *
     * @param $input
     * @param $boundary
     * @return array Contains array of resulting mime parts
     */
    public function _boundarySplit($input, $boundary)
    {
        $tmp = explode('--' . $boundary, $input);

        for ($i = 1; $i < count($tmp) - 1; ++$i) {
            $parts[] = $tmp[$i];
        }

        return $parts;
    }

    /**
     * Given a header, this function will decode it
     * according to RFC2047. Probably not *exactly*
     * conformant, but it does pass all the given
     * examples (in RFC2047).
     *
     * @param mixed $input
     * @return string Decoded header value
     */
    public function _decodeHeader($input)
    {
        // Remove white space between encoded-words
        $input = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $input);

        // For each encoded-word...
        while (preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $input, $matches)) {
            $encoded  = $matches[1];
            $charset  = $matches[2];
            $encoding = $matches[3];
            $text     = $matches[4];

            switch (mb_strtolower($encoding)) {
                case 'b':
                    $text = base64_decode($text, true);
                    break;
                case 'q':
                    $text = str_replace('_', ' ', $text);
                    preg_match_all('/=([a-f0-9]{2})/i', $text, $matches);
                    foreach ($matches[1] as $value) {
                        $text = str_replace('=' . $value, chr(hexdec($value)), $text);
                    }
                    break;
            }

            $input = str_replace($encoded, $text, $input);
        }

        return $input;
    }

    /**
     * Given a body string and an encoding type,
     * this function will decode and return it.
     *
     * @param mixed $input
     * @param mixed $encoding
     * @return string Decoded body
     */
    public function _decodeBody($input, $encoding = '7bit')
    {
        switch ($encoding) {
            case '7bit':
                return $input;
                break;
            case 'quoted-printable':
                return $this->_quotedPrintableDecode($input);
                break;
            case 'base64':
                return base64_decode($input, true);
                break;
            default:
                return $input;
        }
    }

    /**
     * Given a quoted-printable string, this
     * function will decode and return it.
     *
     * @param mixed $input
     * @return string Decoded body
     */
    public function _quotedPrintableDecode($input)
    {
        // Remove soft line breaks
        $input = preg_replace("/=\r?\n/", '', $input);

        // Replace encoded characters
        $input = preg_replace('/=([a-f0-9]{2})/ie', "chr(hexdec('\\1'))", $input);

        return $input;
    }

    /**
     * Checks the input for uuencoded files and returns
     * an array of them. Can be called statically, eg:
     *
     * $files =& Mail_mimeDecode::uudecode($some_text);
     *
     * It will check for the begin 666 ... end syntax
     * however and won't just blindly decode whatever you
     * pass it.
     *
     * @param mixed $input
     * @return array Decoded bodies, filenames and permissions
     * @author Unknown
     */
    public function &uudecode($input)
    {
        // Find all uuencoded sections
        preg_match_all("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", $input, $matches);

        for ($j = 0, $jMax = count($matches[3]); $j < $jMax; $j++) {
            $str      = $matches[3][$j];
            $filename = $matches[2][$j];
            $fileperm = $matches[1][$j];

            $file   = '';
            $str    = preg_split("/\r?\n/", trim($str));
            $strlen = count($str);

            for ($i = 0; $i < $strlen; ++$i) {
                $pos = 1;
                $d   = 0;
                $len = (((ord(mb_substr($str[$i], 0, 1)) - 32) - ' ') & 077);

                while (($d + 3 <= $len) and ($pos + 4 <= mb_strlen($str[$i]))) {
                    $c0   = (ord(mb_substr($str[$i], $pos, 1)) ^ 0x20);
                    $c1   = (ord(mb_substr($str[$i], $pos + 1, 1)) ^ 0x20);
                    $c2   = (ord(mb_substr($str[$i], $pos + 2, 1)) ^ 0x20);
                    $c3   = (ord(mb_substr($str[$i], $pos + 3, 1)) ^ 0x20);
                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

                    $file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

                    $file .= chr(((($c2 - ' ') & 077) << 6) | (($c3 - ' ') & 077));

                    $pos += 4;
                    $d   += 3;
                }

                if (($d + 2 <= $len) && ($pos + 3 <= mb_strlen($str[$i]))) {
                    $c0   = (ord(mb_substr($str[$i], $pos, 1)) ^ 0x20);
                    $c1   = (ord(mb_substr($str[$i], $pos + 1, 1)) ^ 0x20);
                    $c2   = (ord(mb_substr($str[$i], $pos + 2, 1)) ^ 0x20);
                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

                    $file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

                    $pos += 3;
                    $d   += 2;
                }

                if (($d + 1 <= $len) && ($pos + 2 <= mb_strlen($str[$i]))) {
                    $c0   = (ord(mb_substr($str[$i], $pos, 1)) ^ 0x20);
                    $c1   = (ord(mb_substr($str[$i], $pos + 1, 1)) ^ 0x20);
                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
                }
            }
            $files[] = ['filename' => $filename, 'fileperm' => $fileperm, 'filedata' => $file];
        }

        return $files;
    }

    /**
     * getSendArray() returns the arguments required for Mail::send()
     * used to build the arguments for a mail::send() call
     *
     * Usage:
     * $mailtext = Full email (for example generated by a template)
     * $decoder = new Mail_mimeDecode($mailtext);
     * $parts =  $decoder->getSendArray();
     * if (!PEAR::isError($parts) {
     *     list($recipents,$headers,$body) = $parts;
     *     $mail = Mail::factory('smtp');
     *     $mail->send($recipents,$headers,$body);
     * } else {
     *     echo $parts->message;
     * }
     * @return mixed array of recipeint, headers,body or Pear_Error
     * @author Alan Knowles <alan@akbkhome.com>
     */
    public function getSendArray()
    {
        // prevent warning if this is not set
        $this->_decode_headers = false;
        $headerlist            = $this->_parseHeaders($this->_header);
        $to                    = '';
        if (!$headerlist) {
            return $this->raiseError('Message did not contain headers');
        }
        foreach ($headerlist as $item) {
            $header[$item['name']] = $item['value'];
            switch (mb_strtolower($item['name'])) {
                case 'to':
                case 'cc':
                case 'bcc':
                    $to = ',' . $item['value'];
                // no break
                default:
                    break;
            }
        }
        if ('' == $to) {
            return $this->raiseError('Message did not contain any recipents');
        }
        $to = mb_substr($to, 1);

        return [$to, $header, $this->_body];
    }

    /**
     * Returns a xml copy of the output of
     * Mail_mimeDecode::decode. Pass the output in as the
     * argument. This function can be called statically. Eg:
     *
     * $output = $obj->decode();
     * $xml    = Mail_mimeDecode::getXML($output);
     *
     * The DTD used for this should have been in the package. Or
     * alternatively you can get it from cvs, or here:
     * https://www.phpguru.org/xmail/xmail.dtd.
     *
     * @param mixed $input
     * @return string XML version of input
     */
    public function getXML($input)
    {
        $crlf   = "\r\n";
        $output = '<?xml version=\'1.0\'?>' . $crlf . '<!DOCTYPE email SYSTEM "https://www.phpguru.org/xmail/xmail.dtd">' . $crlf . '<email>' . $crlf . self::_getXML($input) . '</email>';

        return $output;
    }

    /**
     * Function that does the actual conversion to xml. Does a single
     * mimepart at a time.
     *
     * @param mixed $input
     * @param mixed $indent
     * @return string XML version of input
     */
    public function _getXML($input, $indent = 1)
    {
        $htab    = "\t";
        $crlf    = "\r\n";
        $output  = '';
        $headers = @(array)$input->headers;

        foreach ($headers as $hdr_name => $hdr_value) {
            // Multiple headers with this name
            if (is_array($headers[$hdr_name])) {
                for ($i = 0, $iMax = count($hdr_value); $i < $iMax; ++$i) {
                    $output .= self::_getXML_helper($hdr_name, $hdr_value[$i], $indent);
                }
                // Only one header of this sort
            } else {
                $output .= self::_getXML_helper($hdr_name, $hdr_value, $indent);
            }
        }

        if (!empty($input->parts)) {
            for ($i = 0, $iMax = count($input->parts); $i < $iMax; ++$i) {
                $output .= $crlf . str_repeat($htab, $indent) . '<mimepart>' . $crlf . self::_getXML($input->parts[$i], $indent + 1) . str_repeat($htab, $indent) . '</mimepart>' . $crlf;
            }
        } elseif (isset($input->body)) {
            $output .= $crlf . str_repeat($htab, $indent) . '<body><![CDATA[' . $input->body . ']]></body>' . $crlf;
        }

        return $output;
    }

    /**
     * Helper function to _getXML(). Returns xml of a header.
     *
     * @param mixed $hdr_name
     * @param mixed $hdr_value
     * @param mixed $indent
     * @return string XML version of input
     */
    public function _getXML_helper($hdr_name, $hdr_value, $indent)
    {
        $htab   = "\t";
        $crlf   = "\r\n";
        $return = '';

        $new_hdr_value = ('received' !== $hdr_name) ? self::_parseHeaderValue($hdr_value) : ['value' => $hdr_value];
        $new_hdr_name  = str_replace(' ', '-', ucwords(str_replace('-', ' ', $hdr_name)));

        // Sort out any parameters
        if (!empty($new_hdr_value['other'])) {
            foreach ($new_hdr_value['other'] as $paramname => $paramvalue) {
                $params[] = str_repeat($htab, $indent)
                            . $htab
                            . '<parameter>'
                            . $crlf
                            . str_repeat($htab, $indent)
                            . $htab
                            . $htab
                            . '<paramname>'
                            . htmlspecialchars($paramname, ENT_QUOTES | ENT_HTML5)
                            . '</paramname>'
                            . $crlf
                            . str_repeat($htab, $indent)
                            . $htab
                            . $htab
                            . '<paramvalue>'
                            . htmlspecialchars($paramvalue, ENT_QUOTES | ENT_HTML5)
                            . '</paramvalue>'
                            . $crlf
                            . str_repeat($htab, $indent)
                            . $htab
                            . '</parameter>'
                            . $crlf;
            }

            $params = implode('', $params);
        } else {
            $params = '';
        }

        $return = str_repeat($htab, $indent) . '<header>' . $crlf . str_repeat($htab, $indent) . $htab . '<headername>' . htmlspecialchars($new_hdr_name, ENT_QUOTES | ENT_HTML5) . '</headername>' . $crlf . str_repeat($htab, $indent) . $htab . '<headervalue>' . htmlspecialchars(
                $new_hdr_value['value'],
                ENT_QUOTES | ENT_HTML5
            ) . '</headervalue>' . $crlf . $params . str_repeat($htab, $indent) . '</header>' . $crlf;

        return $return;
    }
} // End of class
