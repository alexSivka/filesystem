<?php

namespace Sivka\Filesystem\Adapter\Zip;

use Sivka\Filesystem\DirectoryInterface;

class Directory extends AbstractZip implements DirectoryInterface {

    /**
     * @param string $name
     * @param int $mode
     * @return object
     */
    public function create($name, $mode = 0755){
        if(!$this->_exists($this->path . '/' . $name)) ftp_mkdir($this->getConnection(), $this->path . '/' . $name);
        return $this->adapter->directory($this->orirginalPath . '/' . $name)->chmod($mode);
    }

    /**
     * @param string $name
     * @param string $content
     * @param int $mode
     * @return object
     */
    public function createFile($name, $content = '', $mode = 0755){
        $file = new File('', $this->adapter);
        return $file->create($this->orirginalPath . '/' . $name, $content, $mode);
    }

    /**
     * @return array
     */
    public function scan(){
        return parent::_scan($this->path);
    }

    /**
     * @return array
     */
    public function read(){
        $list = [ 'dir' => [], 'link' => [], 'file' => [] ];
        $files = $this->adapter->rawList($this->path);
        foreach($files as $item){
            if(strpos($item[0], 'd') === 0) $list['dir'][] = $this->adapter->directory($this->orirginalPath . '/' . $item[8]);
            elseif(strpos($item[0], 'l') === 0) $list['link'][] = $this->adapter->link($this->orirginalPath . '/' . $item[8]);
            else $list['file'][] = $this->adapter->file($this->orirginalPath . '/' . $item[8]);
        }
        return array_merge($list['dir'], $list['link'], $list['file']);
    }

    /**
     * @param string|object $directory
     * @param int $mode
     * @return object
     * @throws \Exception
     */
    public function copyTo($directory, $mode = 0775){
        if(!$this->exists()) throw new \Exception('source path not exists');
        if(!is_object($directory)) $directory = $this->adapter->directory($directory);

        $dest = $directory->create($this->getName(), $mode);


        foreach($this->read() as $item){
            if($item->isDir() && $item->getPath() == $directory->getPath()) continue; // not allow copying into self child directory
            $item->copyTo($dest);
        }

        return $dest;
    }

    /**
     * @return $this
     */
    public function delete(){
        if(!$this->exists()) return $this;
        foreach ($this->read() as $item){
            $item->delete();
        }
        ftp_rmdir($this->getConnection(), $this->path);
        return $this;
    }

    /**
     * @return string
     */
    public function getType(){
        return 'dir';
    }
}