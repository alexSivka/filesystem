<?php

namespace Sivka\Filesystem\Adapter\Local;

abstract class AbstractLocal {

    /** @var object \Sivka\Filesystem\AdapterInterface */
    protected $adapter;

    /** @var string */
    protected $path;

    /** @var string */
    protected $orirginalPath;

    /**
     * AbstractModel constructor.
     * @param string $path
     * @param object \Sivka\Filesystem\AdapterInterface $adapter
     */
    function __construct($path, $adapter){
        $this->adapter = $adapter;
        $this->setPath($path);
    }

    /**
     * @return object \Sivka\Filesystem\AdapterInterface
     */
    public function getAdapter(){
        return $this->adapter;
    }

    /**
     * @param string $path
     * @return $this
     */
    protected function setPath($path){
        $this->orirginalPath = $path && $path != '/' ? trim($path, '/') : '/';
        $this->path = $this->adapter->getAbsolutePath($this->orirginalPath);
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(){
        return $this->path;
    }

    /**
     * @return string
     */
    public function getName(){
        return basename($this->path);
    }

    /**
     * @return string
     */
    public function getOrirginalPath(){
        return $this->orirginalPath;
    }

    /**
     * @return bool
     */
    public function isDir(){
        return $this->getType() == 'dir';
    }

    /**
     * @return bool
     */
    public function isFile(){
        return $this->getType() == 'file';
    }

    /**
     * @return bool
     */
    public function isLink(){
        return $this->getType() == 'link';
    }

    /**
     * @param string $newPath
     * @return $this
     */
    public function rename($newPath){
        $newPath = rtrim($newPath, '/');
        if(strpos($newPath, '/') === false) $newPath = dirname($this->orirginalPath) . '/' . $newPath;
        $newAbsPath = $this->adapter->getAbsolutePath($newPath);
        if($newAbsPath == $this->path) return $this;
        rename($this->path, $newAbsPath);
        return $this->setPath($newPath);
    }


    /**
     * @param int $mode
     * @return $this
     */
    public function chmod($mode){
        chmod($this->path, $mode);
        return $this;
    }

    /**
     * @return bool
     */
    public function exists(){
        return $this->_exists($this->path);
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function _exists($path){
        return file_exists($path);
    }

    /**
     * @return string
     */
    public function getPerms(){
        return substr(base_convert(fileperms($this->path), 10, 8), -4);
    }

    /**
     * @return int
     */
    public function getMTime(){
        return filemtime($this->path);
    }

    /**
     * @return int
     */
    public function getSize(){
        return filesize($this->path);
    }

    /**
     * @return string
     */
    public function getLocation(){
        return dirname($this->path);
    }

    /**
     * @param object $directory
     * @return object
     */
    public function moveTo($directory){
        $dest = $this->copyTo($directory);
        $dest->touch($this->getMTime());
        $this->delete();
        return $dest;
    }

    /**
     * @param int $time
     * @return $this
     */
    public function touch($time){
        touch($this->path, $time);
        return $this;
    }

}