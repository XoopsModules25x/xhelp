<?php declare(strict_types=1);

//-----------------------------------------------------------------------------

/**
 * Javascript is written via an instance of this class
 */
class JPSpan_CodeWriter
{
    /**
     * Serialized Javascript
     * @var string
     */
    public $code = '';
    /**
     * Disables further writing of code
     * Used when errors are generated
     * @var bool
     */
    public $enabled = true;

    /**
     * Write some code - overwrites the existing code
     * @param mixed $code
     */
    public function write($code): void
    {
        if ($this->enabled) {
            $this->code = $code;
        }
    }

    /**
     * Append some code to the existing code
     * @param mixed $code
     */
    public function append($code): void
    {
        if ($this->enabled) {
            $this->code .= $code;
        }
    }

    /**
     * Return all the written code
     * @return string Javascript
     */
    public function toString(): string
    {
        return $this->code;
    }
}
