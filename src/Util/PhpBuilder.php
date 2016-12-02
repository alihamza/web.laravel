<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Util;

/**
 * The php code builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PhpBuilder
{
    /**
     * @var string
     */
    protected $code = '';

    /**
     * The current indentation level of the code
     *
     * @var int
     */
    public $indent = 0;

    /**
     * @return string
     */
    public function getCode() : string
    {
        return $this->code;
    }

    /**
     * Appends the supplied code to the builder.
     *
     * @param string $code
     *
     * @return void
     */
    public function append(string $code)
    {
        if ($code && (empty($this->code) || substr($this->code, -strlen(PHP_EOL)) === PHP_EOL)) {
            $this->code .= $this->indent($code);
        } else {
            $this->code .= $code;
        }
    }

    /**
     * Appends the supplied code and a new line to the builder.
     *
     * @param string $code
     *
     * @return void
     */
    public function appendLine(string $code = '')
    {
        $this->append($code);
        $this->code .= PHP_EOL;
    }

    protected function indent($code)
    {
        $indent = str_repeat(' ', 4 * $this->indent);

        return $indent . str_replace(PHP_EOL, PHP_EOL . $indent, $code);
    }
}