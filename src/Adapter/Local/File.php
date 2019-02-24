<?php

namespace Sivka\Filesystem\Adapter\Local;

use Sivka\Filesystem\FileInterface;

class File extends AbstractLocal implements FileInterface {

    /**
     * @param string $path
     * @param string $content
     * @param int $mode
     * @return object
     */
    public function create($path, $content = '', $mode = 0755){
        return $this->adapter->file($path)->write($content)->chmod($mode);
    }

    /**
     * @return bool|string
     */
    public function read(){
        return file_get_contents($this->path);
    }

    /**
     * @param string $content
     * @return $this
     */
    public function write($content = ''){
        file_put_contents($this->path, $content);
        return $this;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function append($content){
        file_put_contents($this->path, $content, FILE_APPEND);
        return $this;
    }

    /**
     * @return $this
     */
    public function delete(){
        @unlink($this->path);
        return $this;
    }

    /**
     * @param string|object $directory
     * @param int $mode
     * @return object
     */
	public function copyTo($directory, $mode = 0775){
        if(is_string($directory)) $directory = $this->adapter->directory($directory);
	    return $directory->createFile($this->getName(), $this->read(), $this->getPerms());
    }

    /**
     * @return string
     */
    public function getMime(){
        $mime = mime_content_type($this->path);
        if (!$mime || $mime == 'application/octet-stream' || $mime == 'inode/x-empty') {
            $mime = \Sivka\Filesystem\Helper::getMimeByExt($this->getExtension());
        }
        return $mime;
    }

    /**
     * @return string
     */
    public function getExtension(){
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**
     * @return string
     */
    public function getType(){
        return 'file';
    }



}