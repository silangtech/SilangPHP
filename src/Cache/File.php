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
namespace SilangPHP\Cache;
class File
{
    //缓存文件
    private $_cache_file = '';
    
    //hash算法掩码0x7FFF = 32767，总数据量 ≈ $this->_mask_value * $this->_link_max / 2
    //private $_mask_value = 0x7FFF; 0x186A0
    private $_mask_value = 32767; //0x5FFFF;
    
    //链表最大长度（当单个hash链表太长时，性能将比较差，直接清空这个链表）
    private $_link_max = 10000;
    
    //缓存文件最大大小，超过会后rebuild收缩(单位M/默认1G)
    private $_file_max = 1024;
    
    //重建的最小间隔时间(如果重建后数据仍然过大, 会超过这个时间才重新rebuild以免死循环)
    private $_rebuild_time = 86400;
    
    //默认保留块大小(serialize 后 byte)
    //对于小于或等于这个块的数据reset时直接更新块从而不占用新的空间，此参数也可以在进行set时指定
    //如果block相对于数据块平均值过大， 会影响写入速度， 1 表示使用动态大小
    private $_default_block = 1;
    
    //文件防下载编码
    private $_exit_code = '<?php exit(); ?>';
    
    //文件防下载编码长度
    private $_exit_code_length = 16;
    
    //删除元素时是否保留块
    //保留则下次再set时, 可以使用回这个块, 但缺点是可能导致链表增长查询变慢,_empty_key
    //不保留重复删除和set则可能导致数据量增长, 视情况选择
    private $_reserve_del_block = true;
    
    //删除替代符(32位以上,非a-e的字母)
    private $_empty_key = 'hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh';
    
    //基本信息长度
    private $_meta_length = 22;
    
    private $_cache_fp = null;
    
    //是否单用户模式（这个模式时，进行写操作不锁定文件）
    public $is_single = false;
    
   /**
    * 构造函数
    * @return void
    */
    public function __construct( $cache_file='filecache_data', $is_single = false )
    {
        $this->_file_max = $this->_file_max * 1024 * 1024;
        $this->_cache_file = PS_RUNTIME_PATH.'/'.$cache_file.'.php';
        if( !file_exists( $this->_cache_file ) )
        {
            $this->_create();
        }
        else if( filesize($this->_cache_file) > $this->_file_max )
        {
            $this->rebuild();
        }
    }
    
    //析造
    public function __destruct()
    {
        if( $this->_cache_fp )
        {
            @fclose( $this->_cache_fp );
            $this->_cache_fp = null;
        }
    }
    
    /**
     * 清除所有缓存(实际就是重设缓存文件)
     *
     * @param $cachefile='' 文件名. 默认在 PATH_DAT.'/cache/qf_quick_cache.dat';
     *                     如果手工指定其它缓存文件, 需要用绝对路径
     * @return void
     */ 
    public function clear( )
    {
        $this->_create( $this->_cache_file );
    }
    
    /**
     * 获取指定key_index的所有链表数据
     * @parem $key 键值
     * @return array()
     */ 
    public function get_list( $key )
    {
        //检查文件是否已经打开
        $this->open();
        if( $key=='' ) {
            return false;
        }
        $index_signs = $this->_get_index_sign( $key );
        fseek($this->_cache_fp, $index_signs[0] * 4 + $this->_exit_code_length);
        $darr = unpack('l1h', fread($this->_cache_fp, 4));
        $head_pos = $darr['h'];
        if( $head_pos==0 ) {
            return false;
        }
        $n_pos = $head_pos;
        $n = 0;
        $link_datas = array();
        do
        {
            fseek($this->_cache_fp, $n_pos);
            $cur_node = array();
            $info_dat  = fread($this->_cache_fp, $this->_meta_length);
            $cur_node  = unpack('S1key_len/l1data_len/l1pre/l1next/l1time/l1exptime', $info_dat);
            $cur_node['pos'] = $n_pos;
            $cur_node['key'] = fread($this->_cache_fp, $cur_node['key_len']);
            if( $cur_node['data_len'] > 0 ) {
                $cur_node['data'] = unserialize( fread($this->_cache_fp, $cur_node['data_len']) );
            } else {
                $cur_node['data'] =  "**mark delete status**";
            }
            $link_datas[] = $cur_node;
            $n_pos = $cur_node['next'];
        } while( $n_pos > 0 && $n < $this->_link_max );
        return $link_datas;
    }
    
    /**
     * 重建缓存(此操作可以删除原来缓存多次修改后占用的空间)
     *
     * @param $cachefile='' 文件名. 默认在 PATH_DAT.'/cache/qf_quick_cache.dat';
     *                      如果手工指定其它缓存文件, 需要用绝对路径
     *
     * @return void
     */ 
    public function rebuild( $isforce=false )
    {
        //强制在晚上2-6点这个时间才允许rebuild操作, 以避开访问高峰期
        if( !$isforce && (date('G') < 2 || date('G') > 6)  ) {
            return false;
        }
        $this->open();
        fclose( $this->_cache_fp );
        unlink( $this->_cache_file );
        return $this->_create( $this->_cache_file );
    }
    
    /**
     * 打开文件
     * @return filehand
     */ 
    public function open()
    {
        if( $this->_cache_fp ) {
            return $this->_cache_fp;
        }
        
        if( !file_exists($this->_cache_file) ) {
            return $this->_create();
        }
        
        $this->_cache_fp = @fopen($this->_cache_file, 'rb+');
        
        if( !$this->_cache_fp ) {
            throw new Exception ( "Cache file is not exists or no purview!" );
        }
        
        return $this->_cache_fp;
    }
    
    /*
    * 用工厂方法创建对象实例
    *
    * @param $cachefile='' 文件名. 默认在 PATH_DAT.'/cache/'.$this->_cache_file;
    *                      如果手工指定其它缓存文件, 需要用绝对路径
    * @param $type='rb+' 打开模式(rb/rb+), 如果只读的才使用 rb 模式                   
    *
    * @return $this
    */ 
    public static function factory($cachefile='', $type='rb+')
    {
       $qc = new File( $cachefile );
       $qc->open();
       return $qc;
    }
    
    /**
     * 创建新文件
     *
     * @param $cachefile 文件名
     * @return void
     *
     */ 
    private function _create( )
    {
        $this->_cache_fp = fopen($this->_cache_file, 'wb+');
//        @chmod($this->_cache_file, 0664);
        flock($this->_cache_fp, LOCK_EX);
        fwrite($this->_cache_fp, $this->_exit_code);
        for($i=0; $i <= $this->_mask_value; $i++)
        {
            fwrite($this->_cache_fp, pack("l", 0));
        }
        rewind($this->_cache_fp);
        flock($this->_cache_fp, LOCK_UN);
        return $this->_cache_fp;
    }
    
    /**
     * 获得索引数组
     * @param $key
     * @return array
     *
     */ 
    private function _get_index_sign( $key )
    {
        if( strlen($key) > 32 )  $key = md5( $key );
        $keyindex = $this->_get_index( $key );
        return array($keyindex, $key);
    }
    
    /**
     * 删除整个链接(把header设为0)
     * @param $key
     * @return bool
     *
     */ 
    private function _del_link( $keyindex )
    {
        fseek($this->_cache_fp, $keyindex * 4 + $this->_exit_code_length);
        fwrite($this->_cache_fp, pack("l", 0));
    }
    
    /**
     * 删除缓存(仅进行标识)
     *
     * @param $key
     * @return void
     *
     */ 
    public function delete( $key )
    {
        //检查文件是否已经打开
        $this->open();
        if( $key=='' ) {
            return false;
        }
        $cur_node = $this->get($key, true);
        //把数据长度标识为0表示这数据已经删除，而不是实际性的删除
        if( $cur_node['curkey'] != false && $cur_node['curkey'] )
        {
            fseek($this->_cache_fp, $cur_node['pos'] + 2);
            flock($this->_cache_fp, LOCK_EX);
            fwrite($this->_cache_fp, pack('S', 0) );
            flock($this->_cache_fp, LOCK_UN);
        }
        return true;
    }
    
    /**
     * delete 同名函数
     */
    public function del( $key )
    {
        return $this->delete( $key );
    }
    
    /**
     * 读缓存
     *
     * @param $key
     * @param $need_cur_node 返回当前或链表最后一个节点（仅适合于内部调用）
     * @return void
     *
     */ 
    public function get( $key, $need_cur_node = false )
    {
        //检查文件是否已经打开
        $this->open();
        if( $key=='' ) {
            return false;
        }
        $index_sign = $this->_get_index_sign( $key );
        $key_index = $index_sign[0];
        $key_sign  = $index_sign[1];
        fseek($this->_cache_fp, $key_index * 4 + $this->_exit_code_length);
        $darr = unpack('l1h', fread($this->_cache_fp, 4));
        $head_pos = $darr['h'];
        if( $head_pos==0 ) {
            return false;
        }
        $n_pos = $head_pos;
        $n = 0;
        do
        {
            fseek($this->_cache_fp, $n_pos);
            $cur_node = array();
            $info_dat  = fread($this->_cache_fp, $this->_meta_length);
            if( strlen($info_dat) != $this->_meta_length ) { return false; }
            $cur_node  = unpack('S1key_len/l1data_len/l1pre/l1next/l1time/l1exptime', $info_dat);
            $cur_node['pos'] = $n_pos;
            $cur_node['curkey'] = false;
            if($cur_node['key_len'] == 0) {
                $n_pos = $cur_node['next'];
                continue;
            }
            $_key = fread($this->_cache_fp, $cur_node['key_len']);
            if( $_key==$key_sign )
            {
                if( $cur_node['data_len'] > 0 ) {
                    $data = unserialize( fread($this->_cache_fp, $cur_node['data_len']) );
                } else {
                    $data = false;
                }
                $cur_node['curkey'] = true;
                return $need_cur_node ? $cur_node : $this->_check_data($cur_node, $data);
            } else {
                $n_pos = $cur_node['next'];
            }
        } while( $n_pos > 0 && $n < $this->_link_max );
        return $need_cur_node ? $cur_node : false;
    }
    
   /**
    * 检查数据状态
    * @param $key
    * @return void
    */ 
    private function _check_data( &$node, &$data )
    {
        if( $node['data_len'] == 0 || ($node['exptime'] > 0 && $node['time'] + $node['exptime'] < time()) ) {
            return false;
        } else {
            return $data;
        }
    }
    
    /**
     * 写缓存
     *
     * 单条数据格式(sign/pre/next/datalen/data)
     *
     * @param $key
     * @param $value
     * @parem $compress 是否压缩（无用选项，这里仅是为了和memcache方法一致）
     * @parem $exptime 超时时间
     * @parem $block_size (这版本弃用了此值)
     *        块大小，如果这个key的数据要经常更新， 把这个值设置比实际数据大一些， 这样在在重复set时， 就不会占用新的空间，
     *        值默认为 1 表示使用实际数据大小，在实际文件中 $block_size == 0 表示这个数据为删除状态
     * @return void
     *
     */
    public function set( $key, $value, $compress=0, $exptime=0, $block_size=1 )
    {
        if( $key=='' ) {
            return false;
        }
        //检查文件是否已经打开
        $this->open();
        
        $index_sign = $this->_get_index_sign( $key );
        $key_index  = $index_sign[0];
        $key_sign   = $index_sign[1];
        
        fseek($this->_cache_fp, $key_index * 4 + $this->_exit_code_length);
        $darr = unpack('l1h', fread($this->_cache_fp, 4));
        $head_pos = $darr['h'];
        
        //待保存的数据
        $value = serialize( $value );
        
        //链表为空
        if( $head_pos==0 )
        {
            //锁定文件
            if( !$this->is_single ) {
                flock($this->_cache_fp, LOCK_EX);
            }
            //4个int标识分别是key_len,value_len,link_pre_pos,link_next_pos,time,exptime
            $save_data = pack('Slllll', strlen($key_sign), strlen($value), 0, 0 , time(), $exptime).$key_sign.$value;
            //保存数据到文件尾部
            fseek($this->_cache_fp, 0, SEEK_END);
            $head_pos = ftell( $this->_cache_fp );
            fwrite($this->_cache_fp, $save_data);
            //保存链表头位置
            fseek($this->_cache_fp, $key_index * 4 + $this->_exit_code_length);
            fwrite($this->_cache_fp, pack('l', $head_pos));
        }
        else
        {
            $cur_node = $this->get($key, true);
            //锁定文件
            if( !$this->is_single ) {
                flock($this->_cache_fp, LOCK_EX);
            }
            //不存在相同的key数据，直接在文件末尾写数据
            if( !$cur_node['curkey'] )
            {
                $save_data = pack('Slllll', strlen($key_sign), strlen($value), $cur_node['pos'], 0, time(), $exptime).$key_sign.$value;
                //保存数据到文件尾部
                fseek($this->_cache_fp, 0, SEEK_END);
                $head_pos = ftell( $this->_cache_fp );
                fwrite($this->_cache_fp, $save_data);
                //改变最后一个节点link_next_pos的指向
                fseek($this->_cache_fp, $cur_node['pos'] + 10);
                fwrite($this->_cache_fp, pack('l', $head_pos));
            }
            //如果新数据比旧数据小，直接在原来位置修改数据
            else if( strlen($value) <= $cur_node['data_len'] )
            {
                $save_data = pack('Slllll', strlen($key_sign), strlen($value), $cur_node['pre'], $cur_node['next'], time(), $exptime).$key_sign.$value;
                fseek($this->_cache_fp, $cur_node['pos']);
                fwrite($this->_cache_fp, $save_data);
            }
            //如果新数据比旧数据大，在文件末尾追加数据
            else
            {
                $save_data = pack('Slllll', strlen($key_sign), strlen($value), $cur_node['pre'], $cur_node['next'], time(), $exptime).$key_sign.$value;
                //保存数据到文件尾部
                fseek($this->_cache_fp, 0, SEEK_END);
                $head_pos = ftell( $this->_cache_fp );
                fwrite($this->_cache_fp, $save_data);
                //改变前一个节点link_next_pos的指向
                if( $cur_node['pre'] > 0)
                {
                    fseek($this->_cache_fp, $cur_node['pre'] + 10);
                    fwrite($this->_cache_fp, pack('l', $head_pos));
                }
                else
                {
                    fseek($this->_cache_fp, $key_index * 4 + $this->_exit_code_length);
                    fwrite($this->_cache_fp, pack('l', $head_pos));
                }
                //改变后一个节点link_pre_pos的指向
                if( $cur_node['next'] > 0)
                {
                    fseek($this->_cache_fp, $cur_node['next'] + 6);
                    fwrite($this->_cache_fp, pack('l', $head_pos));
                }
            }
        }
        //解除文件写保护
        if( !$this->is_single ) {
            flock($this->_cache_fp, LOCK_UN);
        }
        return true;
    }
    
    /**
     * 关闭文件
     * (只有写入数据时, 在执行过程中才会有文件保护, 但写完后会自行解锁, 因此一般情况不需要关闭文件)
     *
     * @return void
     */ 
    public function close()
    {
        @fclose( $this->_cache_fp );
        $this->_cache_fp = null;
    }
    
    /**
     * 根据字符串计算key索引
     * @param $key
     * @return short int
     */
    private function _get_index( $key )
    {
        $l = strlen($key);
        $h = 0x238f13af;
        while ($l--)
        {
            $h += ($h << 5);
            $h ^= ord($key[$l]);
            $h &= 0x7fffffff;
        }
        $i = ($h % $this->_mask_value);
        //if( $i < 6 ) $i = $i+5; //由于被php代码占用，不在前20字节存储数据
        return $i;
    }
}
