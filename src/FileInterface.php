<?php
	
namespace Sivka\Filesystem;
	
interface FileInterface {

    public function create($path, $content, $mode = 0775);
	
	public function rename($newPath);
	
	public function exists();
	
	public function read();
	
	public function write($content);
	
	public function append($content);
	
	public function delete();

	public function copyTo($newPath, $mode = 0775);

    public function moveTo($newPath);

	public function getMime();

    public function getMTime();

    public function getSize();

	public function getExtension();

    public function getName();

    public function getAdapter();

    public function getPath();


}