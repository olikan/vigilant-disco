<?php

require dirname(__FILE__) . '/vendor/autoload.php';

if (php_sapi_name() !== 'cli') {
    exit('Oops!');
}

if (!isset($argv[1])) {
    exit('The file name is empty!');
}

$path = $argv[1];

if (!file_exists($path)) {
    exit('The file does not exist!');
}

$string_to_check = file_get_contents($path);

try {
    $regex = \src\Parenthesis::hasMatched($string_to_check);
    echo 'The Expression ' . ($regex === true ? 'is' : 'isn\'t') . ' correct' . PHP_EOL;
} catch (InvalidArgumentException $exception) {
    echo $exception;
}

