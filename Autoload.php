<?php

if (version_compare(PHP_VERSION, '5.6.0', '<')) throw new Exception('PHP version must be 5.6.0 or higher');

spl_autoload_register(
    function($className){

        if($className == 'Sivka\Filesystem'){

            require_once __DIR__ . '/src/Filesystem.php';
            return;

        }

        $name = str_replace('\\', '/', $className);

        $name = str_replace('Sivka/Filesystem/', '', $name);

        require_once __DIR__ . '/src/' . $name . '.php';

    }
);