<?php

namespace Sivka\Filesystem\Adapter;


use Sivka\Filesystem\AdapterInterface;
use Sivka\Filesystem\Adapter\Ftp\File;
use Sivka\Filesystem\Adapter\Ftp\Directory;
use Sivka\Filesystem\Adapter\Ftp\Link;

class Ftp implements AdapterInterface {

    /** @var resource */
    protected $connection;

    /** @var array */
    protected $config;

    /** @var string */
    protected $root;

    /** @var bool */
    public $useCache = true;

    /** @var array */
    protected $cache = ['raw' => [], 'mlsd' => []];

    /** @var array */
    protected $default = [
        'host' => '',
        'port' => 21,
        'username' => '',
        'password' => '',
        'ssl' => false,
        'timeout' => 60,
        'root' => '/', // always ended slash
        'permPrivate' => 0744,
        'permPublic' => 0755,
        'passive' => true,
        //'transferMode',
        //'systemType',
        //'ignorePassiveAddress',
        //'recurseManually',
        'utf8' => false,
    ];

    /**
     * Ftp constructor.
     * @param array $config
     */
    function __construct($config){
        $this->config = array_merge($this->default, $config);
        $this->setRoot($this->config['root']);
        $this->connect();
    }

    /**
     * @param string $path
     * @return Directory|File|Link
     */
    public function get($path){
        $fileName = basename($path);
        $location = str_replace('\\', '/', dirname($this->getAbsolutePath($path)));
        $list = $this->rawList($location);
        $file = null;
        foreach ($list as $item){
            if($item[8] == $fileName) {
                if(strpos($item[0], 'd') === 0) return $this->directory($path);
                elseif(strpos($item[0], 'l') === 0) return $this->link($path);
                return $this->file($path);
            }
        }

    }

    /**
     * @param string $path
     * @return \Sivka\Filesystem\Adapter\Ftp\File
     */
    public function file($path = ''){
        return new File($this->getAbsolutePath($path), $this);
    }

    /**
     * @param string $path
     * @return \Sivka\Filesystem\Adapter\Ftp\Directory
     */
    public function directory($path = ''){
        return new Directory($this->getAbsolutePath($path), $this);
    }

    /**
     * @param string $path
     * @return \Sivka\Filesystem\Adapter\Ftp\Link
     */
    public function link($path = ''){
        return new Link($this->getAbsolutePath($path), $this);
    }

    /**
     * connect to remote server
     */
    public function connect(){
        if ($this->config['ssl']) {
            $this->connection = ftp_ssl_connect($this->config['host'], $this->config['port'], $this->config['timeout']);
        } else {
            $this->connection = ftp_connect($this->config['host'], $this->config['port'], $this->config['timeout']);
        }
        if (!$this->connection) {
            throw new \RuntimeException('Could not connect to host: ' . $this->config['host']);
        }

        $login = ftp_login(
            $this->connection,
            $this->config['username'],
            $this->config['password']
        );

        if(!$login){
            $this->disconnect();
            throw new \RuntimeException('Could not login to host: ' . $this->config['host']);
        }

        if($this->config['utf8']) $this->setUtf8Mode();

        $this->setPassiveMode($this->config['passive']);

        if (!ftp_chdir($this->connection, $this->config['root'])) {
            throw new \RuntimeException('Root '. $this->config['root'] .' does not exist: ');
        }

    }

    /**
     * destroy connection
     * @return $this
     */
    public function disconnect(){
        if (is_resource($this->connection)) ftp_close($this->connection);
        $this->connection = null;
        return $this;
    }

    /**
     * @return $this
     */
    public function setUtf8Mode(){
        $res = ftp_raw($this->connection, "OPTS UTF8 ON");
        if (substr($res[0], 0, 3) !== '200') throw new \RuntimeException('Could not set UTF-8 mode for host: ' . $this->config['host']);
        return $this;
    }

    /**
     * @param number $mode
     * @return $this
     */
    public function setPassiveMode($mode){
        if(!ftp_pasv($this->connection, $mode))
                throw new \RuntimeException('Could not set passive mode for host: ' . $this->config['host']);
        return $this;
    }

    /**
     * @return resource
     */
    public function getConnection(){
        return $this->connection;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getAbsolutePath($path){
        return $path ? $this->root . trim($path, '/') : rtrim($this->root, '/') . '/';
    }

    /**
     * @param string $path
     */
    public function setRoot($path){
        $this->root = $path == '/' ? '/' : '/' . trim($path, '/') . '/';
    }

    /**
     * @return string
     */
    public function getRoot(){
        return $this->root;
    }

    /**
     * @return array
     */
    public function getConfig(){
        return $this->config;
    }

    /**
     * @param string $path
     * @return array
     */
    public function rawList($path){
        //if($list = $this->getCached('raw', $path)) return $list;
        $files = ftp_rawlist($this->getConnection(), $path);
        if(!$files) return [];
        $list = [];

        foreach($files as $line){
            $info = preg_split('~[\s]+~', $line, -1, PREG_SPLIT_NO_EMPTY);
            if($info[8] != '.' && $info[8] != '..') $list[] = $info;
        }
        //if($this->useCache) $this->cache['raw'][$path] = $list;
        return $list;
    }

    /**
     * ftp_mlsd() polyfill
     * @param string $directory
     * @return array|bool
     */
    function ftp_mlsd($directory){

        //if($list = $this->getCached('mlsd', $directory)) return $list;

        if(function_exists('ftp_mlsd')){
            $files = ftp_mlsd($this->connection, $directory);
            //if($this->useCache) $this->cache['mlsd'][$directory] = $files;
            return $files;
        }

        $adapter = new Ftp($this->config);

        if (!@ftp_chdir($adapter->getConnection(), $directory)) return false;
        $ret = ftp_raw($adapter->getConnection(), 'PASV');
        if (!count($ret)) return false;
        if (!preg_match('/^227.*\\(([0-9]+,[0-9]+,[0-9]+,[0-9]+),([0-9]+),([0-9]+)\\)$/', $ret[0], $matches)) return false;

        $conn_IP = str_replace(',', '.', $matches[1]);
        $conn_Port = intval($matches[2]) * 256 + intval($matches[3]);
        $socket = @fsockopen($conn_IP, $conn_Port, $errno, $errstr, $this->config['timeout']);
        if (!$socket) return false;
        stream_set_timeout($socket, $this->config['timeout']);

        ftp_raw($adapter->getConnection(), 'MLSD');
        $s = '';
        while (!feof($socket)) {
            $s  .= fread($socket, 1024);
            $stream_meta_data = stream_get_meta_data($socket);
            if ($stream_meta_data['timed_out']) {
                fclose($socket);
                return false;
            }
        }
        fclose($socket);
        $files = [];
        foreach (explode("\n", $s) as $line) {
            if (!$line) continue;
            $file = [];
            $elements = explode(';', $line, 8);
            if(!count($elements)) continue;
            $file['name'] = trim(end($elements));
            foreach($elements as $key => $item){
                if($key < 7){
                    $item = explode('=', $item, 2);
                    $file[$item[0]] = $item[1];
                }
            }
            $files[] = $file;
        }

        ftp_close($adapter->getConnection());

        //if($this->useCache) $this->cache['mlsd'][$directory] = $files;

        return $files;
    }

    public function getName(){
        return 'Ftp';
    }


    protected function getCached($key, $path){
        if($this->useCache && isset($this->cache[$key][$path])) return $this->cache[$key][$path];
    }

    public function clearCache(){
        $this->cache = ['raw' => [], 'mlsd' => []];
        return $this;
    }

    public function cacheable($flag){
        $this->useCache = $flag;
    }


}
