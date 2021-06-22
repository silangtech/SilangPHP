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
namespace SilangPHP\Httpmode;

use SilangPHP\Log;

Class Appbase{
    public $appname = 'base';
    public $appDir;
    public $config = [];
    public $ct = 'index';
    public $ac = 'index';
    public $debug = 1;
    public $debug_ip = '';
    public $startTime = '';
    public $endTime = '';
    public $cacheType = 'file';
    public $request;
    public $response;
    public $logger;

    public function __construct()
    {
        $this->config = \SilangPHP\Config::get("Site");
        // var_dump($this->config);
        $this->logger = new Log('system');
        if($this->config)
        {
            $this->ct = $this->config['defaultController'] ?? 'index';
            $this->ac = $this->config['defaultAction'] ?? 'index';
            $this->debug = $this->config['debug'];
            $this->debug_ip = $this->config['debug_ip'] ?? '';
            $this->cacheType = $this->config['cacheType'] ?? 'file';
        }
    }

    /**
     * http初始化
     */
    public function initialize()
    {
        if($this->debug == '1')
        {
            $safe_ip = '';
            if($this->debug_ip)
            {
                $safe_ip = explode(",", $this->debug_ip);
            }
            $debug = 1;
            // 开启ip的情况
            if($safe_ip)
            {
                $ip = \SilangPHP\Helper\Util::get_client_ip();
                if( (in_array($ip, $safe_ip)) )
                {
                    $debug = 1;
                }else{
                    $debug = 0;
                }
            }
            if($debug)
            {
                error_reporting(E_ALL);
                \SilangPHP\Error::register();
            }else{
                error_reporting(0);
            }
        }else{
            error_reporting(0);
        }
    }
}