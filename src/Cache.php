<?php
/*LICENSE
+-----------------------------------------------------------------------+
| SilangPHP Framework                                                   |
+-----------------------------------------------------------------------+
| This program is free software; you can redistribute it and/or modify  |
| it under the terms of the GNU General Public License as published by  |
| the Free Software Foundation. You should have received a copy of the  |
| GNU General Public License along with this program.  If not, see      |
| http://www.gnu.org/licenses/.                                         |
| Copyright (C) 2020. All Rights Reserved.                              |
+-----------------------------------------------------------------------+
| Supports: http://www.github.com/silangtech/SilangPHP                  |
+-----------------------------------------------------------------------+
*/
declare(strict_types=1);
namespace SilangPHP;
Class Cache{
    //缓存记录内存变量
    private $caches = array();

    //文件缓存系统或redis
    private $mc_handle = null;

    //缓存类型（file|redis）
    public static $cache_type = 'file';

    //key默认前缀
    private static $df_prefix = 'mc_df_';

    //默认缓存时间
    private static $cache_time = 7200;

    //当前类实例
    private static $instance = null;

    //是否使用内存数组
    public static $need_mem = true;

    public static $fileName = 'cachefilec';

    /**
     * 构造函数
     * @return void
     */
    public function __construct($type = '', $fileName = '' , $cache_time='3600')
    {
        self::$cache_time = $cache_time;
        if(empty($type))
        {
            self::$cache_type = \SilangPHP\SilangPHP::$cacheType;
        }else{
            self::$cache_type = $type;
        }
        if(empty($fileName))
        {
            $fileName = self::$fileName;
        }
        if( self::$cache_type == 'file' )
        {
            $this->mc_handle = \SilangPHP\Cache\File::factory( $fileName );
        }elseif ( self::$cache_type == 'redis' )
        {
            $this->mc_handle = new \SilangPHP\Cache\Redis();
        }
    }

    /**
     * 为自己创建实例，以方便对主要方法进行静态调用
     */
    protected static function _check_instance()
    {
        if( self::$instance == null ) {
            self::$instance = new Cache(self::$cache_type);
        }
        return self::$instance;
    }

    /**
     * 获取key
     */
    protected static function _get_key($prefix, $key)
    {
        $key = base64_encode(Cache::$df_prefix.'_'.$prefix.'_'.$key);
        if( strlen($key) > 32 ) $key = md5( $key );
        return $key;
    }

    /**
     * 增加/修改一个缓存
     * @param $prefix     前缀
     * @parem $key        键(key=base64($prefix.'_'.$key))
     * @param $value      值
     * @parem $cachetime  有效时间(0不限, -1使用系统默认)
     * @return void
     */
    public static function set( $key, $value, $cachetime=-1)
    {
        if( self::_check_instance()===false ) {
            return false;
        }
        if($cachetime==-1) {
            $cachetime = self::$cache_time;
        }
//        $key = self::_get_key( $key);
        if( self::$need_mem ) {
            self::$instance->mc_handle->caches[ $key ] = $value;
        }
//        var_dump(self::$instance->mc_handle);
        return self::$instance->mc_handle->set($key, $value, $cachetime);
    }

    /**
     * 删除缓存
     * @param $prefix     前缀
     * @parem $key        键
     * @return void
     */
    public static function del($key)
    {
        if( self::_check_instance()===false ) {
            return false;
        }
//        $key = self::_get_key($prefix, $key);
        if( isset(self::$instance->mc_handle->caches[ $key ]) ) {
            self::$instance->mc_handle->caches[ $key ] = false;
            unset(self::$instance->mc_handle->caches[ $key ]);
        }
        return self::$instance->mc_handle->delete( $key );
    }

    /**
     * 读取缓存
     * @param $prefix     前缀
     * @parem $key        键
     * @return void
     */
    public static function get($key)
    {
        //全局禁用cache(调试使用的情况)
        if( defined('NO_CACHE') && NO_CACHE ) {
            return false;
        }
        if( self::_check_instance()===false ) {
            return false;
        }
//        $key = self::_get_key($prefix, $key);
        if( isset(self::$instance->mc_handle->caches[ $key ]) ) {
            return self::$instance->mc_handle->caches[ $key ];
        }
        return self::$instance->mc_handle->get( $key );
    }

    /**
     * 清除保存在缓存类的缓存
     * @return void
     */
    public static function free_mem()
    {
        if( isset(self::$instance->mc_handle->caches) ) {
            self::$instance->mc_handle->caches = [];
        }
    }

    /**
     * 关闭链接
     * @return void
     */
    public static function free()
    {
        if( self::_check_instance()===false ) {
            return false;
        }
        if( self::$cache_type != 'memcached' ) {
            self::$instance->mc_handle->close();
        }
    }
}