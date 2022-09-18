# InitPHP VarDumper

## Installation

```
composer require initphp/var-dumper
```

or included `src/Init.php`.

## Usage

### `dump()`

```php
$obj = new stdClass;
$obj->pi = 3.14;

dump($obj);
```

### `dd()`

Dump and die

```php
$var = 'xml';

dd($var);
```

## TO-DO

- [ ] Coloring of codes will be made more understandable.

## License

Copyright &copy; 2022 [MIT License](./LICENSE)
