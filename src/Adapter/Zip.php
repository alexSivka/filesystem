<?php

namespace Sivka\Filesystem\Adapter;


use Sivka\Filesystem;
use Sivka\Filesystem\AdapterInterface;
use Sivka\Filesystem\Adapter\Local\File;
use Sivka\Filesystem\Adapter\Local\Directory;
use Sivka\Filesystem\Adapter\Local\Link;

class Zip implements AdapterInterface {

    /** @var object */
    protected $zip;

    /** @var object */
    protected $file;

    protected $tmpFile;

    /**
     * Local constructor.
     * @param string|object $file
     */
    function __construct($file){
        $this->file = is_string($file) ? Filesystem::get($file) :  $file;
        $this->zip = new \ZipArchive();
        $this->zip->open($file->getPath());
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
        return '/';
    }




    public function getName(){
        return 'Zip';
    }
}
