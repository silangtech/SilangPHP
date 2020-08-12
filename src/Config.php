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
namespace SilangPHP;
use SilangPHP\Traits\Instance;
Class config
{
    use Instance;
    //框架配置文件
    public $config = [];
    /**
     * 初始化配置
     * 先要引进文件
     */
    public static function include($filename='',$reload=false):void
    {
        if(!empty($filename))
        {
            if(isset(self::instance()->config[$filename]))
            {
                if($reload == false)
                {
                    return;
                }
            }
            //加载配置文件
            $file2 = PS_CONFIG_PATH.$filename.".php";
            $file_exist2 = file_exists($file2);
            if($file_exist2)
            {
                $file_arr2 = include_once $file2;
            }
            if($file_exist2)
            {
                self::instance()->config[$filename] = $file_arr2;
            }
        }

    }



    /**
     * 获取配置
     * get 获取配置
     * get database.mysql
     * 获取database文件里的mysql数组配置
     * config::get("database.mysql");
     */
    public static function get($key)
    {
        $keys = explode(".",$key);
        $count = count($keys);

        if($count>1)
        {
            $config_name = array_shift($keys);
            self::instance()->include($config_name);
            $data = self::instance()->config[$config_name];
            if($data)
            {
                foreach($keys as $cckey)
                {
                    if(isset($data[$cckey]))
                    {
                        $data = $data[$cckey];
                    }else{
                        break;
                    }
                }
                return $data;
            }
        }else{
            self::instance()->include($key);
            if(!isset(self::instance()->config[$key]))
            {
                return '';
            }
            return self::instance()->config[$key];
        }
    }
    /**
     * 递归设置数据
     */
    public static function walk_key($data,$keys,$value)
    {
        $ckey = array_shift($keys);
        if(empty($keys))
        {
            return $value;
        }else{
            $tmp = self::walk_key($data,$keys,$value);
            $data[$ckey] = $tmp;
        }
        return $data;
    }
    /**
     * 更新配置
     * 代码自动生成数据配置
     * Config::set("database.mysql.dbname","phpshow");
     */
    public static function set($key,$value):void
    {
        $keys = explode(".",$key);
        $count = count($keys);
        if($count>1)
        {
            $config_name = array_shift($keys);
            $data = self::instance()->config[$config_name];
            $data = self::walk_key($data,$keys,$value);
        }else{
            $config_name = $keys;
            $data = self::instance()->config[$keys] = $value;
        }
        //只能生成在应用层config
        file_put_contents(PS_CONFIG_PATH.$config_name.".php",$data);
    }

}