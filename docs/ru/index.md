# Старт

Для начала необходимо подключить адаптер. Список доступных, на текущий момент, адаптеров и ссылки на документацию к ним [здесь](./adapters-list.md).

```php
use Sivka\Filesystem as FS;
use Sivka\Filesystem\Adapter\Local;

$localAdapter = new Local($_SERVER['DOCUMENT_ROOT']);

$fs = new FS($localAdapter);
```

Для локального адаптера указывать путь от корня необязательно, по умолчанию он будет равен `$_SERVER['DOCUMENT_ROOT']`. Для локальной файловой системы, как самой часто используемой, можно вообще не указывать адаптер. При инициации Filesystem без адаптера, по умолчанию будет использован локальный адаптер.

После подключения адаптера, можно оперировать с объектами файловой системы. Для получения объекта необходимо вызвать соответствующий метод.

```php
$dir = $fs->directory('my-folder');
$file = $fs->file('my-folder/test.txt');
$link = $fs->link('my-folder/link.lnk');

// или просто
$dir = $fs->get('my-folder');
$file = $fs->get('my/folder/test.txt');
```
Для локальной файловой системы, предусмотрен короткий способ получения объектов.

```php
use Sivka\Filesystem as FS;

$dir = FS::get('my-folder');
```
Все адаптеры наследуют единый интерфейс и соответственно имеют одинаковые методы, возвращающие одинаковый результат. Присутствуют, конечно, некоторые исключения, связанные с особенностями целевой файловой системы. Например в протоколе фтп отсутствует возможность создавать символические ссылки.

- [Директории (папки, разделы)](#Директории-папки-разделы)
  - [scan()](#scan)
  - [read()](#read)
  - [create($name [, $perms])](#createname--perms)
  - [createFile($name [, $perms])](#createfilename--perms)
  - [createLink($target, $name)](#createlinktarget-name)
  - [copyTo($target [, $perms])](#copytotarget--perms)
  - [moveTo($target [, $perms])](#movetotarget--perms)
  - [delete()](#delete)
  - [rename($name)](#renamename)
  - [chmod($perms)](#chmodperms)
  - [touch($timestamp)](#touchtimestamp)
  - [exists()](#exists)
  - [getPath()](#getpath)
  - [getName()](#getname)
  - [getPerms()](#getperms)
  - [getMTime()](#getmtime)
- [Файлы](#Файлы)
  - [create($path [, $content [, $perms]])](#createpath--content--perms)
  - [read()](#read-1)
  - [write($content)](#writecontent)
  - [append($content)](#appendcontent)
  - [copyTo($target [, $perms])](#copytotarget--perms-1)
  - [moveTo($target [, $perms])](#movetotarget--perms-1)
  - [delete()](#delete-1)
  - [rename($name)](#renamename-1)
  - [chmod($perms)](#chmodperms-1)
  - [touch($timestamp)](#touchtimestamp-1)
  - [exists()](#exists-1)
  - [getPath()](#getpath-1)
  - [getName()](#getname-1)
  - [getExtension()](#getextension)
  - [getPerms()](#getperms-1)
  - [getMTime()](#getmtime-1)
  - [getSize()](#getsize)
  - [getMime()](#getmime)
- [Символические ссылки](#Символические-ссылки)
  - [create($target, $path)](#createtarget-path)
  - [read()](#read-2)
  - [copyTo($target [, $perms])](#copytotarget--perms-2)
  - [moveTo($target [, $perms])](#movetotarget--perms-2)
  - [delete()](#delete-2)
  - [rename($name)](#renamename-2)
  - [exists()](#exists-2)
  - [getPath()](#getpath-2)
  - [getName()](#getname-2)

## Директории (папки, разделы)

### scan()

Метод `scan()` возвращает массив имен элементов(файлы, папки, ссылки) директории.

```php
$list = $fs->get('my-folder')->scan();
```

### read()

Метод `read()` возвращает массив объектов элементов(файлы, папки, ссылки) директории.

```php
$objectsList = $fs->get('my-folder')->read();
```

### create($name [, $perms])

Метод `create()` создает внутри директории новую директорию. Метод имеет два аргумента - имя новой директории и необязательный права новой директории. Возвращает объект новой директории. Директории могут создавать новые директории, файлы и ссылки, в то время как файлы и ссылки могут создавать только себе подобных.

```php
$newDirectory = $fs->directory('my-folder')->create('new-folder');
$newDirectory = $fs->directory('my-folder')->create('new-folder', 0755);
```

### createFile($name [, $perms])

Создает новый файл внутри директории. Возвращает объект нового файла.

```php
$newFile = $fs->directory('my-folder')->createFile('new-file.txt');
$newFile = $fs->directory('my-folder')->createFile('new-file.txt', 0644);
```

### createLink($target, $name)

Создает новую символическую внутри директории. Возвращает объект новой ссылки. Работает только там, где это поддерживается. Первый аргумент путь к целевому элементу ссылки, второй имя ссылки.

```php
$newLink = $fs->directory('my-folder')->createLink('new-file.txt', 'new-file.lnk');
```
### copyTo($target [, $perms])

Рекурсивно копирует директорию. В качестве первого аргумента можно передать строку с путем или объект целевой директории. Если передана строка, копирование происходит внутри текущей файловой системы, если объект - то в файловую систему объекта. Возвращает объект новой директории. Один нюанс, при попытке скопировать родительскую директорию в дочернюю, копирование будет осуществлено частично.

```php
$newFolder = $fs->directory('my-folder')->copyTo('another-folder', 0755);

$anotherFolder = $fs->directory('another-folder');
$newFolder = $fs->directory('my-folder')->copyTo($anotherFolder);
```

Пример копирования между файловыми системами.

```php
use Sivka\Filesystem as FS;
use Sivka\Filesystem\Adapter\Ftp;

$ftpFs = new Fs( new Ftp([/* config */]) );

$ftpDir = $ftpFs->get('ftp-folder');

$newFolder = FS::get('my-folder')->copyTo($ftpDir);

// туда и обратно
$localFolder = FS::get('another-folder');
$newFolder = FS::get('my-folder')->copyTo($ftpDir)->copyTo($anotherFolder);
```

### moveTo($target [, $perms])

Метод `moveTo` полностью аналогичен методу `copyTo` за исключением того, что вместо копирования происходит `перемещение` директории.

```php
$newFolder = $fs->directory('my-folder')->moveTo('another-folder', 0755);
```

### delete()

Удаляет директорию.
```php
$fs->directory('my-folder')->delete();
```

### rename($name)

Переименование директории. Возвращает объект директории.

```php
$fs->directory('my-folder')->rename('new-name');
```

### chmod($perms)

Устанавливает права для директории

```php
$fs->directory('my-folder')->chmod(0777);
```

### touch($timestamp)

Устанавливает время модификации

```php
$fs->directory('my-folder')->touch(1546344000);
```

### exists()

Проверяет существует ли директория.

```php
if(!$fs->directory('folders/my-folder')->exists()){
    $fs->directory('folders')->create('my-folder');
}
```

### getPath()

Возвращает полный путь директории

### getName()

Возвращает имя директории

### getPerms()

Возвращает права директории

### getMTime()

Возвращает время модификации


## Файлы

### create($path [, $content [, $perms]])

Метод `create()` создает файл по указанному пути. Если указан второй аргумент - в файл будет записано указанное содержимое. Третий аргумент - права.

```php
$file = $fs->file()->create('new.txt', 'hi, world');
```

### read()

Метод `read()` возвращает содержимое файла.

```php
echo $fs->file('new.txt')->read(); // hi, world
```

### write($content)

Записывает новое содержимое в файл

```php
$fs->file('new.txt')->write('Hello world');
```

### append($content)

Добавляет новое содержимое в файл

```php
$fs->file('new.txt')->append("\n Hello all");
```

### copyTo($target [, $perms])

Копирует файл в указанную директорию. В качестве первого аргумента можно передать строку с путем или объект целевой директории. Если передана строка, копирование происходит внутри текущей файловой системы, если объект - то в файловую систему объекта. Возвращает объект нового файла.

```php
$newFile = $fs->file('new.txt')->copyTo('my-folder', 0755);

$anotherFolder = $fs->directory('another-folder');
$newFile = $fs->file('new.txt')->copyTo($anotherFolder);
```

Пример копирования между файловыми системами.

```php
use Sivka\Filesystem as FS;
use Sivka\Filesystem\Adapter\Ftp;

$ftpFs = new Fs( new Ftp([/* config */]) );

$ftpDir = $ftpFs->get('ftp-folder');

$newFile = FS::get('new.txt')->copyTo($ftpDir);
```

### moveTo($target [, $perms])

Метод `moveTo` полностью аналогичен методу `copyTo` за исключением того, что вместо копирования происходит `перемещение` файла.

```php
$newFile = $fs->file('new.txt')->moveTo('my-folder', 0755);
```

### delete()

Удаляет файл.
```php
FS::get('new.txt')->delete();
```

### rename($name)

Переименование файла. Возвращает объект файла.

```php
$fs->file('my,txt')->rename('new.txt');
```

### chmod($perms)

Устанавливает права для файла

```php
$fs->file('my.txt')->chmod(0777);
```

### touch($timestamp)

Устанавливает время модификации

```php
$fs->file('my.txt')->touch(1546344000);
```

### exists()

Проверяет существует ли файл.

```php
if(!$fs->file('my-folder/my.txt')->exists()){
    $fs->directory('my-folder')->createFile('my.txt');
}
```

### getPath()

Возвращает полный путь файла

### getName()

Возвращает имя файла

### getExtension()

Возвращает расширение файла

### getPerms()

Возвращает права файла

### getMTime()

Возвращает время модификации файла

### getSize()

Возвращает размер файла в байтах

### getMime()

Возвращает mime тип файла

```php
echo FS::get('new.txt')->getMime() // text/plain
```

## Символические ссылки

### create($target, $path)

Создает ссылку. Первый аргумент путь цели для ссылки. Второй имя ссылки, включая путь.
Некоторые адаптеры могут не поддерживать ссылки. Также в операционных системах windows ссылки поддерживаются начиная с windows 7.

```php
$link = $fs->link()->create('my-folder/new.txt', 'my-folder/new-text.lnk');
```

### read()

Возвращает путь целевого объекта ссылки.

```php
echo $fs->link('my-folder/new-text.lnk')->read(); // may-folder/new.txt
```


### copyTo($target [, $perms])

Копирует не саму ссылку, а пытается скопировать целевой объект в указанную директорию. В качестве первого аргумента можно передать строку с путем или объект целевой директории. Если передана строка, копирование происходит внутри текущей файловой системы, если объект - то в файловую систему объекта. Возвращает объект нового элемента.

```php
$newFile = $fs->link('my-folder/new-text.lnk')->copyTo('new-folder', 0755);
```


### moveTo($target [, $perms])

Метод `moveTo` полностью аналогичен методу `copyTo` за исключением того, что вместо копирования происходит `перемещение` объекта.

```php
$newFile = $fs->link('my-folder/new-text.lnk')->moveTo('my-folder', 0755);
```

### delete()

Удаляет саму ссылку. Целевой объект не удаляется.
```php
FS::get('my-folder/new-text.lnk')->delete();
```

### rename($name)

Переименование ссылки. Возвращает объект ссылки.

```php
$fs->link('my-folder/new-text.lnk')->rename('new.lnk');
```

### exists()

Проверяет существует ли ссылка.

```php
if(!$fs->link('my-folder/new-text.lnk')->exists()){
    $fs->directory('my-folder')
            ->createLink('my-folder/new.txt', 'my-folder/new-text.lnk');
}
```

### getPath()

Возвращает полный путь ссылки

### getName()

Возвращает имя ссылки









