<?php

namespace Sivka\Filesystem\Adapter;
  

use Sivka\Filesystem\AdapterInterface;
use Sivka\Filesystem\Adapter\Local\File;
use Sivka\Filesystem\Adapter\Local\Directory;
use Sivka\Filesystem\Adapter\Local\Link;

class Local implements AdapterInterface {

    /** @var string */
    protected $root;

    /** @var string */
    protected $osName;

    /**
     * Local constructor.
     * @param null|string $root
     */
    function __construct($root = null){
        if(!$root) $root = $_SERVER['DOCUMENT_ROOT'];
        $this->root = rtrim($root, '/') . '/';
        $this->osName = strtoupper(substr(PHP_OS, 0, 3));
    }

    /**
     * @param string $path
     * @return null|Directory|File|Link
     */
    public function get($path){
        $absPath = $this->getAbsolutePath($path);
        if($this->osName == 'WIN') {
            if(str_replace('\\', '/', realpath($absPath)) != $absPath) return $this->link($path);
        }

        if(is_file($absPath)) return $this->file($path);
        else if(is_dir($absPath)) return $this->directory($path);
        elseif(is_link($absPath)) return $this->link($path);
        return null;
    }

    /**
     * @param string $path
     * @return \Sivka\Filesystem\Adapter\Local\File
     */
    public function file($path = ''){
        return new File($path, $this);
    }

    /**
     * @param string $path
     * @return \Sivka\Filesystem\Adapter\Local\Directory
     */
    public function directory($path = ''){
        return new Directory($path, $this);
    }

    /**
     * @param string $path
     * @return \Sivka\Filesystem\Adapter\Local\Link
     */
    public function link($path = ''){
        return new Link($path, $this);
    }

    /**
     * @param string $path
     * @return string
     */
    public function getAbsolutePath($path){
        if(strpos($path, $this->root) !== 0) $path = $this->root . ltrim($path, '/');
        return $path;
    }


    /**
     * @return string
     */
    public function getRoot(){
        return $this->root;
    }

    /**
     * @return string
     */
    public function getOsName(){
        return $this->osName;
    }
}
