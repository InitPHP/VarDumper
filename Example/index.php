<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '../src/Init.php';

$obj = new stdClass;
$obj->hi = "Hello";
$obj->status = false;
$obj->time = time();
$obj->age = null;
$obj->price = 16.7;
$obj->data = [
    'name'      => 'Cat',
    'status'    => false,
    'informations'  => [
        'birtday'   => date("Y-m-d"),
    ]
];


dd($obj);