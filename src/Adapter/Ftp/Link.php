<?php

namespace Sivka\Filesystem\Adapter\Ftp;

use Sivka\Filesystem\LinkInterface;

class Link extends AbstractFtp implements LinkInterface {

    /**
     * does nothing, because there is no ftp command for create links
     * @param $target
     * @param $link
     * @return $this
     */
    public function create($target, $link){
        return $this;
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
        if(filetype($path) == 'dir') rmdir($path);
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