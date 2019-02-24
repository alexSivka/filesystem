# Sivka\Filesystem

The **real** filesystem abstraction layer. Work easy with filesystem as never before.

## Features

- simple api
- filesystem entities as model like objects
- extendable by adapters
- copy, move files and folders inside and between different filesystems by one command
- symlink support (win/unix)
- more...

## Installation

```
composer require sivka/filesystem
```

## Requirements

- PHP 5.6.0+

## Example

```php
use Sivka\Filesystem as FS;
use Sivka\Filesystem\Adapter\Ftp;

$fs = new FS(); // used local adapter by default

$fs->directory('my-folder')
    ->copyTo('all-folders')
    ->rename('new-folder');

$fs->file('my-folder/1.txt')
    ->moveTo('new-folder')
    ->write('new-text')
    ->append('more text');

// between filesystems

// for local may be used short call
$localDir = FS::get('all-folders/new-folder');

$ftpAdapter = new Ftp([
    'host' => '127.0.0.1',
    'username' => 'user',
    'password' => 'pass',
]);

$ftpFs = new FS($ftpAdapter);

$fptDir = $ftpFs->get('ftp-folder');

$localDir->copyTo($ftpDir, 0744)
        ->createFile('ftp-file.txt', 0644)
        ->write("hello I'm on remote.")
        ->moveTo($localDir)
        ->append("Now I'm in local");

```

Full documentation read here

- [english](./docs/en/index.md)
- [russian](./docs/ru/index.md)


## License

This project is licensed under the [MIT License](LICENSE.md)