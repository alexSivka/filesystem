<?php

namespace Sivka\Filesystem\Adapter\Ftp;

abstract class AbstractFtp {

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
        ftp_rename($this->getConnection(), $this->path, $newAbsPath);
        return $this->setPath($newPath);
    }


    /**
     * @param number $mode
     * @return $this
     */
    public function chmod($mode){
        ftp_chmod($this->getConnection(), $mode, $this->path);
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
        $list = $this->_scan(dirname($path));
        return in_array(basename($path), $list);
    }

    /**
     * @param string $path
     * @return array
     */
    protected function _scan($path = ''){
        $list = [];
        $files = $this->adapter->rawList($path);
        foreach($files as $item) $list[] = $item[8];
        return $list;
    }

    /**
     * @return string
     */
    public function getPerms(){
        $info = $this->getInfo();
        return $this->getModeFromString($info[0]);
    }

    /**
     * @return int
     */
    public function getMTime(){
        if(!$info = $this->getStat()) return 0;
        $date = preg_replace('~(.{4})(.{2})(.{2})(.{2})(.{2})(.{2})~', '$1-$2-$3 $4:$5:$6', $info['modify']);
        return strtotime($date);
    }

    /**
     * @return int
     */
    public function getSize(){
        return ftp_size($this->getConnection(), $this->path);
    }

    /**
     * @return string
     */
    protected function getLocation(){
        $dirs = explode('/', trim($this->path, '/'));
        array_pop($dirs);
        return '/' . implode('/', $dirs);
    }

    /**
     * @return array
     */
    public function getInfo(){
        $list = $this->adapter->rawList($this->getLocation());
        $name = basename($this->path);
        foreach($list as $item) if($item[8] == $name) return $item;
    }

    /**
     * @return null|array
     */
    public function getStat(){
        if(!$list = $this->adapter->ftp_mlsd($this->getLocation())) return null;
        $name = basename($this->path);
        foreach($list as $item) if($item['name'] == $name) return $item;
    }


    /**
     * @return resource
     */
    public function getConnection(){
        return $this->adapter->getConnection();
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
        $date = date('YmdHis', $time);
        try {
            ftp_raw($this->getConnection(), 'MFMT ' . $date . ' ' . $this->path);
        }catch (\Exception $e){}
        return $this;
    }

    /**
     * @param string $command
     * @return mixed
     */
    public function raw($command){
        return ftp_raw($this->getConnection(), $command);
    }

    /**
     * @param string $mode
     * @return string
     */
    public function getModeFromString( $mode ) {

        $realmode = strtr(substr($mode, -9), ['-' => '0', 'r' => '4', 'w' => '2', 'x' => '1']);
        $arr = preg_split('//', $realmode, -1, PREG_SPLIT_NO_EMPTY);
        $mode = $sum = '';
        foreach($arr as $key => $num){
            $sum = !($key % 3) ? $num : (int)$sum + $num;
            if($key && !( ($key+1) % 3)) $mode .= $sum;
        }

        return '0' . $mode;
    }

}
