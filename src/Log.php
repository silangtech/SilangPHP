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

/**
 * 日志类
 * 暂时去掉seaslog
 */
class Log
{
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    public $type = 'text';
    //日志起始地址
    private $pathRoot = '';
    //日志地址
    private $path = '';
    //日志目录类型, 默认按日期存放
    public $default_dir_type = 'date';
    public function __construct($default_dir = '' , $logPath = PS_RUNTIME_PATH.'/log/', $type = 'text')
    {
        $this->type = $type;
        // 统一日志路径
        if(!empty($default_dir))
        {
            $logPath .= $default_dir.'/';
        }
        $this->setLogPath($logPath);
    }

    /**
     * 记录log
     */
    public function log($level = '', $message = '', array $context = [])
    {
        $record = [
            'level'   => $level,
            'message' => $message,
            'context' => $context,
        ];
        if($this->default_dir_type == 'date')
        {
            $default_dir = date("Ymd");
            $this->setLogDirName($default_dir);
        }else{
            $this->setLogDirName($this->default_dir_type);
        }
        // 记录每小时的变化
        $date = date("YmdH");
        if($this->type == 'text')
        {
            // 增加回时间显示
            $text = "[{$level}]".date("Y-m-d H:i:s")."|".$this->interpolate($message, $context);
        }elseif($this->type == 'json')
        {
            $text = json_encode($record);
        }
        file_put_contents($this->path.'/'.$date.'.log', $text."\r\n",FILE_APPEND|LOCK_EX);
    }

    /**
     * 用上下文信息替换记录信息中的占位符
     */
    function interpolate($message, array $context = array())
    {
        // 构建一个花括号包含的键名的替换数组
        $replace = array();
        foreach ($context as $key => $val) {
            // 检查该值是否可以转换为字符串
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        // 替换记录信息中的占位符，最后返回修改后的记录信息。
        return strtr($message, $replace);
    }

    /**
     * debug日志
     */
    public function debug($message = '', array $context = array())
    {
        $this->log(Log::DEBUG, $message = '', $context);
    }

    /**
     * info日志
     */
    public function info($message = '', array $context = array())
    {
        $this->log(Log::INFO, $message, $context);
    }

    /**
     * notice日志
     */
    public function notice($message = '', array $context = array())
    {
        $this->log(Log::NOTICE, $message, $context);
    }

    /**
     * error日志
     */
    public function error($message = '', array $context = array())
    {
        $this->log(Log::ERROR, $message, $context);
    }

    /**
     * critical日志
     */
    public function critical($message = '', array $context = array())
    {
        $this->log(Log::CRITICAL, $message, $context);
    }

    /**
     * alert日志
     */
    public function alert($message = '', array $context = array())
    {
        $this->log(Log::ALERT, $message, $context);
    }

    /**
     * emergency日志
     */
    public function emergency($message = '', array $context = array())
    {
        $this->log(Log::EMERGENCY, $message, $context);
    }

    /**
     * 设置日志地址
     */
    public function setLogPath($path = '')
    {
        $this->pathRoot = $path;
    }

    /**
     * 设置日志规则
     * @todo 每次设置都要return一个新的对象factory
     * @param string $name
     * @return string
     */
    public function setLogDirName($name = '')
    {
        if(empty($name))
        {
            return '';
        }
        $this->path = $this->pathRoot."/".$name."/";
        //要判断一下mkdir吧
        if (!is_dir($this->path)){
            $this->dir_make($this->path);
        }
}

    /**
     * 创建文件夹
     * @return bool
     */
    public function dir_make($path)
    {
        $tmp = mkdir($path, 0777, true);
        return $tmp;
    }
}