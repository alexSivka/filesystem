<?php
	
namespace Sivka\Filesystem;
	
interface DirectoryInterface {

    public function create($name, $mode = 0775);

    public function createFile($name, $mode = 0755, $content = '');

    public function rename($newPath);

    public function exists();

    public function scan();

    public function read();

    public function delete();

    public function copyTo($newPath, $mode = 0775);

    public function moveTo($newPath);

    public function getName();

    public function getAdapter();

    public function getPath();

}