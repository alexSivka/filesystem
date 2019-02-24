<?php

namespace Sivka;


use Sivka\Filesystem\AdapterInterface;
use Sivka\Filesystem\Adapter\Local;

class Filesystem {

    /**
     * @var object \Sivka\Filesystem\AdapterInterface
     */
    protected $adapter;

    public function __construct($adapter = null){
        if(!$adapter) $adapter = new Local();
        $this->setAdapter($adapter);
    }

    /**
     * @param AdapterInterface $adapter
     */
    public function setAdapter(AdapterInterface $adapter){
        $this->adapter = $adapter;
    }

    /**
     * @return object \Sivka\Filesystem\AdapterInterface
     */
    public function getAdapter(){
        return $this->adapter;
    }

    /**
     * @param string $path
     * @return \Sivka\Filesystem\FileInterface
     */
    public function file($path = ''){
        return $this->adapter->file($path);
    }

    /**
     * @param string $path
     * @return \Sivka\Filesystem\DirectoryInterface
     */
    public function directory($path = ''){
        return $this->adapter->directory($path);
    }

    public function __call($name, $args){
        if($name == 'get') return $this->adapter->get(isset($args[0]) ? $args[0] : '');
    }

    public static function __callstatic($name, $args){
        if($name == 'get') return (new Local())->get(isset($args[0]) ? $args[0] : '');
    }

}