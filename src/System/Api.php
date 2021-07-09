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
namespace SilangPHP\System;

/**
 * Api服务
 * 因为内置，故不命令Service
 */
Class Api
{
    public $input;
    public function look()
    {
        $mode = $this->input['date'] ?? 1;
        $date = $this->input['date'] ?? date("Ymd");
        \SilangPHP\Helper\Util::apirun($mode, $date);
    } 

}