<?php

namespace Sivka\Filesystem\Adapter\Local;

use Sivka\Filesystem\LinkInterface;

class Link extends AbstractLocal implements LinkInterface {

    /**
     * @param string|object $target
     * @param string $link
     * @return Link
     */
    public function create($target, $link){

        $link = $this->adapter->getAbsolutePath($link);
        if($this->_exists($link)) $this->_delete($link);

        $target = is_object($target) ? $target->getPath() : $this->adapter->getAbsolutePath($target);

        symlink($target, $link);

        return new self($link, $this->adapter);
    }

    /**
     * @return string
     */
    public function read(){
        return str_replace('\\','/', @readlink($this->path));
    }

    /**
     * tries copy target of link
     * @param string $directory
     * @param int $mode
     * @return object
     */
    public function copyTo($directory, $mode = 0755){
        if(is_string($directory)) $directory = $this->adapter->directory($directory);
        $item = $this->adapter->get($this->read());
        if($item) return $item->copyTo($directory, $mode);
    }

    /**
     * @param object $directory
     * @return object
     */
    public function moveTo($directory){
        $newFile = $this->copyTo($directory);
        $this->delete();
        return $newFile;
    }

    /**
     * @return Link
     */
    public function delete(){
       return $this->_delete($this->path);
    }

    /**
     * @param string $path
     * @return $this
     */
    protected function _delete($path){
        $type = @filetype($path);
        if(!$type || $type == 'dir') rmdir($path);
        else @unlink($path);
        return $this;
    }

    /**
     * @return string
     */
    public function getType(){
        return 'link';
    }


}