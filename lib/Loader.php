<?php

class Loader{
    // 声明目录文件
    static $dirs;
    const UNABLE_TO_LOAD = 'Unable to load';
    static $registered;
    // 构造方法。需要传入目录
    function __construct($dirs = array())
    {
        self::init($dirs);
    }
    // 检查文件是否存在方法，如果存在返回true 
    protected static function loadFile($file) {
        if(file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }
    // 自动加载类文件
    public static function autoload($class) {
        $success = false;
        $fn = str_replace('\\',DIRECTORY_SEPARATOR, $class);
        foreach(self::$dirs as $start) {
            // 检查当前文件存不存在
            $file = $start. DIRECTORY_SEPARATOR .$fn.'.php';
            if(self::loadFile($file)) {
                $success = true;
                break;
            }
        }
        // 如果文件不存在就抛出错误
        if(!$success) {
            if(!self::loadFile(__DIR__.DIRECTORY_SEPARATOR. $fn)) {
                throw new \Exception(
                    self::UNABLE_TO_LOAD. ' ' .$class
                );
            }
        }
        return $success;
    }
    // 添加目录到dir中，如果目录是数组，就合并数组
    public static function addDirs($dirs) {
        if(is_array($dirs)) {
            self::$dirs = array_merge(self::$dirs, $dirs);
        } else{
            self::$dirs[] = $dirs;
        }
    }
    // 初始化方法
    public static function init($dirs = []) {
        if($dirs) {
            self::addDirs($dirs);
        }
        if(self::$registered == 0) {
            spl_autoload_register(__CLASS__."::autoload");
            self::$registered++;
        }
    }
}