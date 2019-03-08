<?php

namespace Sivka\Filesystem\Adapter\Zip;

use Sivka\Filesystem\FileInterface;

class File extends AbstractZip implements FileInterface {

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
     * @return string
     */
    public function read(){
        ob_start();
        ftp_get($this->getConnection(), 'php://output', $this->path, FTP_BINARY);
        return ob_get_clean();
    }

    /**
     * @param string $content
     * @return $this
     */
    public function write($content = ''){
        $fp = tmpfile();
        if($content) {
            fwrite($fp, $content);
            rewind($fp);
        }
        ftp_fput($this->getConnection(), $this->path, $fp, FTP_BINARY);
        fclose($fp);
        return $this;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function append($content){
        $content = $this->read() . $content;
        return $this->write($content);
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
     * @return $this
     */
    public function delete(){
        ftp_delete($this->getConnection(), $this->path);
        return $this;
    }

    /**
     * @return string
     */
    public function getMime(){
        return \Sivka\Filesystem\Helper::getMimeByExt($this->getExtension());
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