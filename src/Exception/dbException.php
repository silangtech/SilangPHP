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
namespace SilangPHP\Exception;
/**
 * 数据库异常
 * Class dbException
 * @package SilangPHP\Exception
 */
Class dbException extends \PDOException
{
    private $errorCode;
    private $sql;

    /**
     * 初始化异常
     */
    public function __construct($code = "", $message = "",  $sql = "")
    {
        parent::__construct($message, $code);
        $this->errorCode = $code;
        $this->sql = $sql;
    }

    /**
     *  获取错误码
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * 获取错误的sql语句
     */
    public function getSql():string
    {
        return $this->sql;
    }

    /**
     * 格式化输出异常码，异常信息，请求id
     * @return string
     */
    public function __toString()
    {
        return "[".__CLASS__."]"." code:".$this->errorCode.
            " message:".$this->getMessage().
            " sql:".$this->sql.lr;
    }

}