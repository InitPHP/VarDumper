<?php
/**
 * VarDumper.php
 * 
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  VarDumper.php © 2022-09-16T11:40:45.910Z
 * @version    0.1
 * @link       https://www.muhammetsafak.com.tr
 * @license    MIT
 */

declare(strict_types=1);

namespace InitPHP\VarDumper;

use const PHP_EOL;

class VarDumper
{

    /** @var mixed */
    private $value;

    /** @var int */
    private $reccess = 0;

    /** @var string */
    private $dump;

    protected $theme = [
        'pre'           => 'color: #F1F1F1; background: #222; border: 1px solid #111; padding: 5px; margin: 8px;',
        'type'          => 'color: #FF4A4A;',
        'length'        => 'color: #A6E3E9;',
        'class_name'    => 'color: #A460ED;',
        'null'          => 'color: #E3FDFD;',
        'bool'          => 'color: #DAF7A6;',
    ];

    protected const ARRAY = 0;
    protected const METHOD = 1;
    protected const PROPERTIES = 2;

    public function __construct($value)
    {
        $this->value = $value;
        $this->dump = $this->typeDumper($this->value);
    }

    public function __toString()
    {
        return '<pre style="' . $this->theme['pre'] . '">' . \str_replace(PHP_EOL . PHP_EOL, PHP_EOL, $this->dump) . '</pre>';
    }

    public static function newInstance($value)
    {
        return new self($value);
    }

    public function dump()
    {
        echo  $this->__toString();
    }

    private function iterableVarDumper($iterable, $type = self::ARRAY)
    {
        $size = \count($iterable);
        $res = '(<span style="' . $this->theme['length'] . '">' . $size . '</span>) {';
        if($size > 0){
            
            ++$this->reccess;
            $res .= PHP_EOL;
            foreach ($iterable as $key => $value) {
                $res .= \str_repeat(' ', ($this->reccess * 4));
                if($type === self::PROPERTIES){
                    $res .= '["' . $key . '"] => ' . $this->typeDumper($value) . PHP_EOL;
                }elseif($type === self::METHOD){
                    $res .= $value . '()' . PHP_EOL;
                }else{
                    $res .= '[' . (\is_string($key) ? '"' . $key . '"' : $key) . '] => '
                    . $this->typeDumper($value) . PHP_EOL;
                }
            }
            --$this->reccess;
            $res .= \str_repeat(' ', ($this->reccess * 4));
            
        }
        $res .= '}';
        return $res;
    }

    private function objectVarDumper($object)
    {
        $res = '{';
        $this->reccess++;
        $properties = \get_object_vars($object);
        if(!empty($properties)){
            $res .= PHP_EOL . \str_repeat(' ', (4 * $this->reccess)) 
                    . '[<b>PROPERTIES</b>] ' . $this->iterableVarDumper($properties, self::PROPERTIES) . PHP_EOL;
        }

        $methods = \get_class_methods($object);
        if(!empty($methods)){
            $res .= PHP_EOL . \str_repeat(' ', (4 * $this->reccess)) 
                    . '[<b>METHODS</b>] ' . $this->iterableVarDumper($methods, self::METHOD) . PHP_EOL;
        }
        $this->reccess--;
        $res .= \str_repeat(' ', ($this->reccess * 4)) . '}' . PHP_EOL;
        return $res;
    }

    private function typeDumper($value): string
    {
        switch (true) {
            case \is_string($value):
                $res = '<span style="' . $this->theme['type'] . '">string</span>'
                        . '(<span style="' . $this->theme['length'] . '">' 
                        . (\function_exists('mb_strlen') ? \mb_strlen($value) : \strlen($value))
                        . '</span>) "' . $value . '"';
                break;
            case \is_int($value):
                $res = '<span style="' . $this->theme['type'] . '">int</span>'
                    . ' (' . $value . ')';
                break;
            case \is_resource($value):
                $res = '<span style="' . $this->theme['type'] . '">resource</span>';
                break;
            case \is_object($value):
                $res = '<span style="' . $this->theme['type'] . '">object</span>';
                $res .= ' (<span style="'.$this->theme['class_name'].'">' . \get_class($value) . '::class</span>) ' . $this->objectVarDumper($value);
                break;
            case \is_null($value):
                $res = '<span style="' . $this->theme['null'] . '">NULL</span>';
                break;
            case \is_bool($value):
                $res = '<span style="' . $this->theme['type'] . '">boolean</span> (' 
                        . '<span style="' . $this->theme['bool'] . '">'
                        . ($value === FALSE ? 'FALSE' : 'TRUE')
                        . '</span>)';
                break;
            case \is_float($value):
                $res = '<span style="' . $this->theme['type'] . '">float</span> (' . $value . ')';
                break;
            case \is_array($value):
                $res = '<span style="' . $this->theme['type'] . '">array</span> ' . $this->iterableVarDumper($value);
                break;
            default:
                $res = '<span style="' . $this->theme['type'] . '">unknown</span>';
        }
        return $res;
    }

}
