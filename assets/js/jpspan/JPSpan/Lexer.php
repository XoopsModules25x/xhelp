<?php declare(strict_types=1);
/**
 * Author Markus Baker: https://www.lastcraft.com
 * Version adapted from Simple Test: https://sourceforge.net/projects/simpletest/
 * @author     Marcus Baker
 */

/**#@+
 * lexer mode constant
 */
define('JPSPAN_LEXER_ENTER', 1);
define('JPSPAN_LEXER_MATCHED', 2);
define('JPSPAN_LEXER_UNMATCHED', 3);
define('JPSPAN_LEXER_EXIT', 4);
define('JPSPAN_LEXER_SPECIAL', 5);
/**#@-*/

/**
 *    Compounded regular expression. Any of
 *    the contained patterns could match and
 *    when one does it's label is returned.
 */
class JPSpan_LexerParallelRegex
{
    public $_patterns;
    public $_labels;
    public $_regex;
    public $_case;

    /**
     *    Constructor. Starts with no patterns.
     * @param bool $case    True for case sensitive, false
     *                      for insensitive.
     */
    public function __construct($case)
    {
        $this->_case     = $case;
        $this->_patterns = [];
        $this->_labels   = [];
        $this->_regex    = null;
    }

    /**
     *    Adds a pattern with an optional label.
     * @param string      $pattern Perl style regex, but ( and )
     *                             lose the usual meaning.
     * @param bool|string $label   Label of regex to be returned
     *                             on a match.
     */
    public function addPattern($pattern, $label = true): void
    {
        $count                   = count($this->_patterns);
        $this->_patterns[$count] = $pattern;
        $this->_labels[$count]   = $label;
        $this->_regex            = null;
    }

    /**
     *    Attempts to match all patterns at once against
     *    a string.
     * @param string $subject   String to match against.
     * @param string $match     First matched portion of
     *                          subject.
     * @return bool|string True on success.
     */
    public function match($subject, &$match)
    {
        if (0 == count($this->_patterns)) {
            return false;
        }
        if (!preg_match($this->_getCompoundedRegex(), $subject, $matches)) {
            $match = '';

            return false;
        }
        $match = $matches[0];
        for ($i = 1, $iMax = count($matches); $i < $iMax; ++$i) {
            if ($matches[$i]) {
                return $this->_labels[$i - 1];
            }
        }

        return true;
    }

    /**
     *    Compounds the patterns into a single
     *    regular expression separated with the
     *    "or" operator. Caches the regex.
     *    Will automatically escape (, ) and / tokens.
     * @return null|string
     * @internal param array $patterns List of patterns in order.
     */
    public function _getCompoundedRegex(): ?string
    {
        if (null === $this->_regex) {
            for ($i = 0, $iMax = count($this->_patterns); $i < $iMax; ++$i) {
                $this->_patterns[$i] = '(' . str_replace(['/', '(', ')'], ['\/', '\(', '\)'], $this->_patterns[$i]) . ')';
            }
            $this->_regex = '/' . implode('|', $this->_patterns) . '/' . $this->_getPerlMatchingFlags();
        }

        return $this->_regex;
    }

    /**
     *    Accessor for perl regex mode flags to use.
     * @return string Perl regex flags.
     */
    public function _getPerlMatchingFlags(): string
    {
        return ($this->_case ? 'msS' : 'msSi');
    }
}

/**
 *    States for a stack machine.
 */
class JPSpan_LexerStateStack
{
    public $_stack;

    /**
     *    Constructor. Starts in named state.
     * @param string $start Starting state name.
     */
    public function __construct($start)
    {
        $this->_stack = [$start];
    }

    /**
     *    Accessor for current state.
     * @return string State.
     */
    public function getCurrent(): string
    {
        return $this->_stack[count($this->_stack) - 1];
    }

    /**
     *    Adds a state to the stack and sets it
     *    to be the current state.
     * @param string $state New state.
     */
    public function enter($state): void
    {
        $this->_stack[] = $state;
    }

    /**
     *    Leaves the current state and reverts
     *    to the previous one.
     * @return bool False if we drop off
     *                 the bottom of the list.
     */
    public function leave(): bool
    {
        if (1 == count($this->_stack)) {
            return false;
        }
        array_pop($this->_stack);

        return true;
    }
}

/**
 *    Accepts text and breaks it into tokens.
 *    Some optimisation to make the sure the
 *    content is only scanned by the PHP regex
 *    parser once. Lexer modes must not start
 *    with leading underscores.
 */
class JPSpan_Lexer
{
    public $_regexes;
    public $_parser;
    public $_mode;
    public $_modeHandlers;
    public $_case;

    /**
     *    Sets up the lexer in case insensitive matching
     *    by default.
     * @param JPSpan_Parser $parser Handling strategy by
     *                              reference.
     * @param string        $start  Starting handler.
     * @param bool          $case   True for case sensitive.
     */
    public function __construct(&$parser, $start = 'accept', $case = false)
    {
        $this->_case         = $case;
        $this->_regexes      = [];
        $this->_parser       = &$parser;
        $this->_mode         = new JPSpan_LexerStateStack($start);
        $this->_modeHandlers = [];
    }

    /**
     *    Adds a token search pattern for a particular
     *    parsing mode. The pattern does not change the
     *    current mode.
     * @param string $pattern Perl style regex, but ( and )
     *                        lose the usual meaning.
     * @param string $mode    Should only apply this
     *                        pattern when dealing with
     *                        this type of input.
     */
    public function addPattern($pattern, $mode = 'accept'): void
    {
        if (!isset($this->_regexes[$mode])) {
            $this->_regexes[$mode] = new JPSpan_LexerParallelRegex($this->_case);
        }
        $this->_regexes[$mode]->addPattern($pattern);
    }

    /**
     *    Adds a pattern that will enter a new parsing
     *    mode. Useful for entering parenthesis, strings,
     *    tags, etc.
     * @param string $pattern  Perl style regex, but ( and )
     *                         lose the usual meaning.
     * @param string $mode     Should only apply this
     *                         pattern when dealing with
     *                         this type of input.
     * @param string $new_mode Change parsing to this new
     *                         nested mode.
     */
    public function addEntryPattern($pattern, $mode, $new_mode): void
    {
        if (!isset($this->_regexes[$mode])) {
            $this->_regexes[$mode] = new JPSpan_LexerParallelRegex($this->_case);
        }
        $this->_regexes[$mode]->addPattern($pattern, $new_mode);
    }

    /**
     *    Adds a pattern that will exit the current mode
     *    and re-enter the previous one.
     * @param string $pattern Perl style regex, but ( and )
     *                        lose the usual meaning.
     * @param string $mode    Mode to leave.
     */
    public function addExitPattern($pattern, $mode): void
    {
        if (!isset($this->_regexes[$mode])) {
            $this->_regexes[$mode] = new JPSpan_LexerParallelRegex($this->_case);
        }
        $this->_regexes[$mode]->addPattern($pattern, '__exit');
    }

    /**
     *    Adds a pattern that has a special mode. Acts as an entry
     *    and exit pattern in one go, effectively calling a special
     *    parser handler for this token only.
     * @param string $pattern Perl style regex, but ( and )
     *                        lose the usual meaning.
     * @param string $mode    Should only apply this
     *                        pattern when dealing with
     *                        this type of input.
     * @param string $special Use this mode for this one token.
     */
    public function addSpecialPattern($pattern, $mode, $special): void
    {
        if (!isset($this->_regexes[$mode])) {
            $this->_regexes[$mode] = new JPSpan_LexerParallelRegex($this->_case);
        }
        $this->_regexes[$mode]->addPattern($pattern, "_$special");
    }

    /**
     *    Adds a mapping from a mode to another handler.
     * @param string $mode    Mode to be remapped.
     * @param string $handler New target handler.
     */
    public function mapHandler($mode, $handler): void
    {
        $this->_modeHandlers[$mode] = $handler;
    }

    /**
     *    Splits the page text into tokens. Will fail
     *    if the handlers report an error or if no
     *    content is consumed. If successful then each
     *    unparsed and parsed token invokes a call to the
     *    held listener.
     * @param string $raw Raw HTML text.
     * @return bool True on success, else false.
     */
    public function parse($raw): bool
    {
        if (!isset($this->_parser)) {
            return false;
        }
        $length = mb_strlen($raw);
        while (is_array($parsed = $this->_reduce($raw))) {
            [$unmatched, $matched, $mode] = $parsed;
            if (!$this->_dispatchTokens($unmatched, $matched, $mode)) {
                return false;
            }
            if (mb_strlen($raw) == $length) {
                return false;
            }
            $length = mb_strlen($raw);
        }
        if (!$parsed) {
            return false;
        }

        return $this->_invokeParser($raw, JPSPAN_LEXER_UNMATCHED);
    }

    /**
     *    Sends the matched token and any leading unmatched
     *    text to the parser changing the lexer to a new
     *    mode if one is listed.
     * @param string      $unmatched Unmatched leading portion.
     * @param string      $matched   Actual token match.
     * @param bool|string $mode      Mode after match. A boolean
     *                               false mode causes no change.
     * @return bool False if there was any error
     *                               from the parser.
     */
    public function _dispatchTokens($unmatched, $matched, $mode = false): bool
    {
        if (!$this->_invokeParser($unmatched, JPSPAN_LEXER_UNMATCHED)) {
            return false;
        }
        if ($this->_isModeEnd($mode)) {
            if (!$this->_invokeParser($matched, JPSPAN_LEXER_EXIT)) {
                return false;
            }

            return $this->_mode->leave();
        }
        if ($this->_isSpecialMode($mode)) {
            $this->_mode->enter($this->_decodeSpecial($mode));
            if (!$this->_invokeParser($matched, JPSPAN_LEXER_SPECIAL)) {
                return false;
            }

            return $this->_mode->leave();
        }
        if (is_string($mode)) {
            $this->_mode->enter($mode);

            return $this->_invokeParser($matched, JPSPAN_LEXER_ENTER);
        }

        return $this->_invokeParser($matched, JPSPAN_LEXER_MATCHED);
    }

    /**
     *    Tests to see if the new mode is actually to leave
     *    the current mode and pop an item from the matching
     *    mode stack.
     * @param string $mode Mode to test.
     * @return bool True if this is the exit mode.
     */
    public function _isModeEnd($mode): bool
    {
        return ('__exit' === $mode);
    }

    /**
     *    Test to see if the mode is one where this mode
     *    is entered for this token only and automatically
     *    leaves immediately afterwoods.
     * @param string $mode Mode to test.
     * @return bool True if this is the exit mode.
     */
    public function _isSpecialMode($mode): bool
    {
        return (0 == strncmp($mode, '_', 1));
    }

    /**
     *    Strips the magic underscore marking single token
     *    modes.
     * @param string $mode Mode to decode.
     * @return string Underlying mode name.
     */
    public function _decodeSpecial($mode): string
    {
        return mb_substr($mode, 1);
    }

    /**
     *    Calls the parser method named after the current
     *    mode. Empty content will be ignored. The lexer
     *    has a parser handler for each mode in the lexer.
     * @param string $content   Text parsed.
     * @param bool   $is_match  Token is recognised rather
     *                          than unparsed data.
     * @return bool
     */
    public function _invokeParser($content, $is_match): bool
    {
        if (('' === $content) || (false === $content)) {
            return true;
        }
        $handler = $this->_modeHandlers[$handler] ?? $this->_mode->getCurrent();

        return $this->_parser->$handler($content, $is_match);
    }

    /**
     *    Tries to match a chunk of text and if successful
     *    removes the recognised chunk and any leading
     *    unparsed data. Empty strings will not be matched.
     * @param string $raw  The subject to parse. This is the
     *                     content that will be eaten.
     * @return array  Three item list of unparsed
     *                     content followed by the
     *                     recognised token and finally the
     *                     action the parser is to take.
     *                     True if no match, false if there
     *                     is a parsing error.
     */
    public function _reduce(&$raw)
    {
        if (!isset($this->_regexes[$this->_mode->getCurrent()])) {
            return false;
        }
        if ('' === $raw) {
            return true;
        }
        $action = $this->_regexes[$this->_mode->getCurrent()]->match($raw, $match);
        if ($action) {
            $unparsed_character_count = mb_strpos($raw, $match);
            $unparsed                 = mb_substr($raw, 0, $unparsed_character_count);
            $raw                      = mb_substr($raw, $unparsed_character_count + mb_strlen($match));

            return [$unparsed, $match, $action];
        }

        return true;
    }
}
