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

    /** @var int */
    private static $level = 1;

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
        return \str_replace(PHP_EOL . PHP_EOL, PHP_EOL, $this->dump);
    }

    public static function newInstance($value)
    {
        return new self($value);
    }

    public function dump()
    {
        echo '<pre style="padding: 5px; border: 1px solid #ccc; background: #fafafa;">' . $this->__toString() . '</pre>';
    }

    private function iterableVarDumper($iterable, $type = self::ARRAY)
    {
        $size = \count($iterable);
        $res = '(' . $size . ') {';
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
                    . '[PROPERTIES] ' . $this->iterableVarDumper($properties, self::PROPERTIES) . PHP_EOL;
        }

        $methods = \get_class_methods($object);
        if(!empty($methods)){
            $res .= PHP_EOL . \str_repeat(' ', (4 * $this->reccess)) 
                    . '[METHODS] ' . $this->iterableVarDumper($methods, self::METHOD) . PHP_EOL;
        }
        $this->reccess--;
        $res .= \str_repeat(' ', ($this->reccess * 4)) . '}' . PHP_EOL;
        return $res;
    }

    private function typeDumper($value): string
    {
        switch (true) {
            case \is_string($value):
                $res = 'string(' . \mb_strlen($value) . ') "' . $value . '"';
                break;
            case \is_int($value):
                $res = 'int (' . $value . ')';
                break;
            case \is_resource($value):
                $res = 'resource';
                break;
            case \is_object($value):
                $res = 'object (' . \get_class($value) . '::class) ' . $this->objectVarDumper($value);
                break;
            case \is_null($value):
                $res = 'NULL';
                break;
            case \is_bool($value):
                $res = 'boolean (' 
                        . ($value === FALSE ? 'false' : 'true')
                        . ')';
                break;
            case \is_float($value):
                $res = 'float (' . $value . ')';
                break;
            case \is_array($value):
                $res = 'array ' . $this->iterableVarDumper($value);
                break;
            default:
                $res = 'unknown';
        }
        return $res;
    }

}
