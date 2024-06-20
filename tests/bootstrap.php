<?php

// custom autoloader...simple really :)
spl_autoload_register(function($className): void {
    $classPath = __DIR__.'/../src/'.str_replace('\\', '/', $className).'.php';
    if (is_file($classPath)) {
        require_once $classPath;
    }
});
