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
| Supports: http://www.github.com/phpsl/SilangPHP                       |
+-----------------------------------------------------------------------+
*/
namespace SilangPHP;

class Tpl
{
    public $instance = null;
    //数据集合
    public static $tpl_result = array('test');

    /**
     * 当前Action的赋值
     * 数据赋值
     * @param $key
     * @param $value
     */
    public static function assign($key,$value)
    {
        self::$tpl_result[$key] = $value;
    }

    /**
     * 加载所需文件
     */
    public static function include_file($file_name)
    {
        return PS_APP_PATH.'/view/'.$file_name.".php";
    }

    /**
     * 显示模板
     * @param $file_name
     */
    public static function display($file_name = '')
    {
        $result = self::$tpl_result;
        ob_start();
        include self::include_file($file_name);
        $res = ob_get_contents();
        ob_end_clean();
        response::end($res);

    }
}