<?php
/**
 * VarDumper.php
 * 
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  VarDumper.php © 2022-09-17T11:40:45.910Z
 * @version    0.1
 * @link       https://www.muhammetsafak.com.tr
 * @license    MIT
 */

declare(strict_types=1);

namespace InitPHP\VarDumper;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use ReflectionUnionType;

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
        'pre'           => 'color: #F1F1F1; background: #222; border: 1px solid #111; padding: 8px; margin: 8px; border-radius: 2px; overflow: auto;',
        'type'          => 'color: #FF4A4A;',
        'length'        => 'color: #A6E3E9;',
        'class_name'    => 'color: #A460ED;',
        'null'          => 'color: #E3FDFD;',
        'bool'          => 'color: #DAF7A6;',
    ];

    protected $customCss = '
    pre.vardumper::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    
    pre.vardumper::-webkit-scrollbar-track {
      background: #FD841F; 
    }
     
    pre.vardumper::-webkit-scrollbar-thumb {
      background: #E14D2A; 
    }
    
    pre.vardumper::-webkit-scrollbar-thumb:hover {
      background: #9C2C77; 
    }';

    protected static $isCustomCSSImport = false;

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
        $res = '';
        if(self::$isCustomCSSImport === FALSE){
            self::$isCustomCSSImport = true;
            $res .= '<style>' . $this->customCss . '</style>';
        }
        return $res . '<pre class="vardumper" style="' . $this->theme['pre'] . '">' . \str_replace(PHP_EOL . PHP_EOL, PHP_EOL, $this->dump) . '</pre>';
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
                    $res .= $value . PHP_EOL;
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

        $dump = $this->reflectionObjectAndMethodPropertiesDump($object);

        $properties = $dump['properties'];
        if(!empty($properties)){
            $res .= PHP_EOL . \str_repeat(' ', (4 * $this->reccess)) 
                    . '[<b>PROPERTIES</b>] ' . $this->iterableVarDumper($properties, self::PROPERTIES) . PHP_EOL;
        }

        $methods = $dump['methods'];
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

    private function reflectionObjectAndMethodPropertiesDump($object): array
    {
        $res = [
            'methods'       => [],
            'properties'    => \get_object_vars($object),
        ];
        $reflection = new ReflectionClass($object);
        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            $res['methods'][$method->getName()] = $this->reflectionMethod2String($method);
        }
        return $res;
    }

    private function reflectionMethod2String(ReflectionMethod $method): string
    {
        if($method->isPublic()){
            $syntax = 'public ';
        }elseif($method->isProtected()){
            $syntax = 'protected ';
        }else{
            $syntax = 'private ';
        }
        if($method->isFinal()){
            $syntax .= 'final ';
        }
        if($method->isAbstract()){
            $syntax .= 'abstract ';
        }
        if($method->isStatic()){
            $syntax .= 'static ';
        }
        $syntax .= 'function ' . $method->getName() . '(';
        
        $parameters = $method->getParameters();
        $method_parameters = [];
        foreach ($parameters as $parameter) {
            $param = '';
            if($parameter->hasType()){
                $param .= $this->reflectionType2String($parameter->getType()) . ' ';
            }
            if($parameter->isVariadic()){
                $param .= '...';
            }
            $param .= '$' . $parameter->getName();
            if($parameter->isOptional() && !$parameter->isVariadic()){
                $param .= ' = ' . $this->typeDumper($parameter->getDefaultValue());
            }
            $method_parameters[] = $param;
        }
        $syntax .= \implode(', ', $method_parameters);
        $syntax .= ')';
        if($method->hasReturnType()){
            $syntax .= ': ' . $this->reflectionType2String($method->getReturnType());
        }
        return $syntax . ';';
    }

    /**
     * Undocumented function
     *
     * @param ReflectionNamedType|ReflectionUnionType|null $types
     * @return string
     */
    private function reflectionType2String($types): string
    {
        if($types === null){
            return '';
        }
        $syntax = '<span style="' . $this->theme['type'] . '">';
        if($types instanceof ReflectionUnionType){
            $res_types = [];
            foreach($types->getTypes() as $type){
                $res_types[] = $type->getName();
            }
            $syntax .= \implode('|', $res_types);
        }else{
            $syntax .= $types->getName();
        }
        $syntax .= '</span>';
        return $syntax;
    }

}
