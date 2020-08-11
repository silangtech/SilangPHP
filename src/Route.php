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
class Route
{
    public static $rules = array();
    protected static $is_load = false;

    /**
     * 加载 rewrite rule 文件
     */
    protected static function load_rule()
    {
        self::$is_load = true;
        $rulefile = PS_CONFIG_PATH.'/rewrite.ini';
        if( file_exists($rulefile) )
        {
            $ds = file($rulefile);
            foreach($ds as $line)
            {
                $line = trim($line);
                if( $line=='' || $line[0]=='#')
                {
                    continue;
                }
                list($s, $t) = preg_split('/[ ]{4,}/', $line); //用至少四个空格分隔，这样即使s、t中有空格也能识别
                $s = rtrim($s);
                $t = ltrim($t);
                if( $s != '' && $t !='' )
                {
                    $_s = preg_replace("#(^[\^]|[\$]$)#", '', $s);
                    $sok = $s[0]=='^' ? '<rw>'.$_s : '<rw>(.*)'.$_s;
                    $s = $s[strlen($s)-1]=='$' ? $sok.'</rw>' : $sok.'([^<]*)</rw>';
                    $s = preg_replace("#(^[\^]|[\$]$)#", '', $s);
                    //$s = '<rw>'.$_s.'</rw>';
                    self::$rules[ $s ] = $t;
                }
            }
        }
    }

    /**
     * 转换要输出的内容里的网址
     * @parem string $html
     */
    public static function convert_html(&$html)
    {
        if( !self::$is_load ) {
            self::load_rule();
        }
        //echo '<xmp>';
        foreach(self::$rules as $s => $t) {
            //echo "$s -- $t \n";
            $html = preg_replace('~'.$s.'~iU', $t, $html);
        }
        //exit();
        $html = preg_replace('#<[/]{0,1}rw>#', '', $html);
        return $html;
    }

    /**
     * 转换单个网址
     * @parem string $url
     */
    public static function convert_url($url)
    {
        if( !self::$is_load )
        {
            self::load_rule();
        }
        foreach(self::$rules as $s=>$t)
        {
            $url = preg_replace('/'.$s.'/iU', $t, $url);
        }
        return $url;
    }
    
}
