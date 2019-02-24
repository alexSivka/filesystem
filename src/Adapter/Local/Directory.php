<?php

namespace Sivka\Filesystem\Adapter\Local;

use Sivka\Filesystem\DirectoryInterface;

class Directory extends AbstractLocal implements DirectoryInterface {


    /**
     * @param string $name
     * @param int $mode
     * @return object
     */
    public function create($name, $mode = 0755){
        if(!file_exists($this->path . '/' . $name)) mkdir($this->path . '/' . $name, $mode);
        else chmod($this->path . '/' . $name, $mode);
        return $this->adapter->directory($this->orirginalPath . '/' . $name);
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
     * @param string $target
     * @param string $name
     * @return Link
     */
    public function createLink($target, $name){
        $link = new Link('', $this->adapter);
        return $link->create($target, $this->orirginalPath . '/' . $name);
    }

    /**
     * @return array
     */
    public function scan(){
        $res = [];
        $items = scandir($this->path);
        foreach($items as $item) if($item != '.' && $item != '..') $res[] = $item;
        return $res;
    }

    /**
     * @return array
     */
    public function read(){
        $files = [ 'dir' => [], 'link' => [], 'file' => [] ];
        $items = scandir($this->path);
        foreach($items as $item){
            if( ($item == '.' || $item == '..')) continue;

            $info = $this->adapter->get($this->orirginalPath . '/' . $item);

            $files[$info->getType()][] = $info;

        }
        return array_merge($files['dir'], $files['link'], $files['file']);
    }

    /**
     * @param string $directory
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
    function delete(){
        if(!$this->exists()) return $this;
        foreach ($this->read() as $item){
            $item->delete();
        }
        rmdir($this->path);
        return $this;
    }

    /**
     * @return string
     */
    public function getType(){
        return 'dir';
    }


}