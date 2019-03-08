<?php
	
namespace Sivka\Filesystem;
	
interface AdapterInterface {
    
    public function file($path = '');

    public function directory($path = '');

    public function getAbsolutePath($path);

    public function getName();

}