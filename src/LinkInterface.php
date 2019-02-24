<?php

namespace Sivka\Filesystem;

interface LinkInterface {

    public function create($target, $link);

    public function exists();

    public function rename($newPath);

    public function delete();

    public function getName();

    public function getAdapter();

    public function getPath();

    public function read();

}