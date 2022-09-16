<?php
/**
 * Init.php
 * 
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Init.php © 2022-09-16T11:52:48.517Z
 * @version    0.1
 * @link       https://www.muhammetsafak.com.tr
 * @license    MIT
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'VarDumper.php';

if(!\function_exists('dump')){
    function dump(...$values)
    {
        foreach($values as $value){
            \InitPHP\VarDumper\VarDumper::newInstance($value)->dump();
        }
    }
}

if(!\function_exists('dd')){
    function dd(...$values)
    {
        foreach($values as $value) {
            \InitPHP\VarDumper\VarDumper::newInstance($value)->dump();
        }
        exit;
    }
}


