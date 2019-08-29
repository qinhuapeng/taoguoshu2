<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(){
    }

    
    //获取路由参数
    protected function getAngularjsParam($type = False)
    {
        $content = file_get_contents('php://input');
        return json_decode($content, $type);
    }

    //日志
    public function logs($filename,$note)
    {
		$logger = $this->getDailyFileLogger($filename);
        $logger->info($note);
        return TRUE;
    }


    public function getDailyFileLogger($filename) {
		$filepath = '';
		$logger = new Logger($filename);
		$filepath = storage_path() . '/logs/' . $filename .'/'.$filename.'.log';
		$handler = (new RotatingFileHandler($filepath))->setFormatter(new LineFormatter(null, null, true, true));
		$logger->pushHandler($handler);
		return $logger;
	}

    //获取时间差
    public function time_interval($startdate,$enddate)
    {
        $day=floor((strtotime($enddate)-strtotime($startdate))/86400);
        $hour=floor((strtotime($enddate)-strtotime($startdate))%86400/3600);
        $minute=floor((strtotime($enddate)-strtotime($startdate))%86400/60);
        $second=floor((strtotime($enddate)-strtotime($startdate))%86400%60);
        return array('day'=>$day,'hour'=>$hour,'minute'=>$minute,'second'=>$second);
    }

    //生成csv文件
    public function createcsv($csv_header,$csv_body,$filepath,$filename)
    {
        // 头部标题
        $csv_header = $csv_header;
     
        /**
         * 开始生成
         * 1. 首先将数组拆分成以逗号（注意需要英文）分割的字符串
         * 2. 然后加上每行的换行符号，这里建议直接使用PHP的预定义
         * 常量PHP_EOL
         * 3. 最后写入文件
         */
    // 打开文件资源，不存在则创建
        $des_file = $filepath.$filename;
        $fp = fopen($des_file,'a');
    // 处理头部标题
        $header = implode(',', $csv_header) . PHP_EOL;
    // 处理内容
        $content = '';
        foreach ($csv_body as $k => $v) {
            $content .= implode(',', $v) . PHP_EOL;
        }

    // 拼接
        $csv = $header.$content;
    // 写入并关闭资源
        fwrite($fp, $csv);
        fclose($fp);
    }



    /**
     * 文件夹文件拷贝
     *
     * @param string $src 来源文件夹
     * @param string $dst 目的地文件夹
     * @return bool
     */
    public function dir_copy($src = '', $dst = '')
    {
        if (empty($src) || empty($dst))
        {
            return false;
        }
     
        $dir = opendir($src);
        $this->dir_mkdir($dst);
        while (false !== ($file = readdir($dir)))
        {
            if (($file != '.') && ($file != '..'))
            {
                if (is_dir($src . '/' . $file))
                {
                    $this->dir_copy($src . '/' . $file, $dst . '/' . $file);
                }
                else
                {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
     
        return true;
    }
 
    /**
     * 创建文件夹
     *
     * @param string $path 文件夹路径
     * @param int $mode 访问权限
     * @param bool $recursive 是否递归创建
     * @return bool
     */
    public function dir_mkdir($path = '', $mode = 0777, $recursive = true)
    {
        clearstatcache();
        if (!is_dir($path))
        {
            mkdir($path, $mode, $recursive);
            return chmod($path, $mode);
        }
     
        return true;
    }

    //角色列表名称
    public function role_name()
    {
        $data = array();
        $tabs = DB::table('roles')->where('is_delete',0)->get(['*']);

        foreach ($tabs as $key => $value) {
            $data[$value->id] = $value->name;
        }
        return $data;
    }

    //获取角色列表
    public function role_list_get(){
        $data = array();
        $tabs = DB::table('roles')->where('is_delete',0)->get(['*']);
        return $tabs;
    }



    //获取果树类别列表
    public function tree_catagory_list()
    {
        $tabs = DB::table('tree_catagory')->where('is_delete',1)->get(['*']);
        return $tabs;
    }

    public function tree_catagory_list_name()
    {
        $data = array();
        $tabs = $this->tree_catagory_list();
        foreach ($tabs as $key => $value) {
            $data[$value->id] = $value->name;
        }
        return $data;
    }

    public function tree_catagory_list_id()
    {
        $data = array();
        $tabs = $this->tree_catagory_list();
        foreach ($tabs as $key => $value) {
            $data[$value->name] = $value->id;
        }
        return $data;
    }


    //获取果树基地列表
    public function tree_base_list()
    {
        $tabs = DB::table('tree_base')->where('is_delete',1)->get(['*']);
        return $tabs;
    }

    public function tree_base_list_name()
    {
        $data = array();
        $tabs = $this->tree_base_list();
        foreach ($tabs as $key => $value) {
            $data[$value->id] = $value->name;
        }
        return $data;
    }

    public function tree_base_list_id()
    {
        $data = array();
        $tabs = $this->tree_base_list();
        foreach ($tabs as $key => $value) {
            $data[$value->name] = $value->id;
        }
        return $data;
    }


    //获取管理员列表
    public function admin_list()
    {
        $tabs = DB::table('users')->get(['*']);
        return $tabs;
    }

    public function admin_list_name()
    {
        $data = array();
        $tabs = $this->admin_list();
        foreach ($tabs as $key => $value) {
            $data[$value->id] = $value->name;
        }
        return $data;
    }

    //获取养护产品
    public function irrigation_list()
    {
        $tabs = DB::table('irrigation_set')->where('type',1)->get(['*'])->toArray();
        return $tabs;
    }

    public function irrigation_list_name()
    {
        $data = array();
        $tabs = $this->irrigation_list();
        foreach ($tabs as $key => $value) {
            $data[$value->id] = $value->name;
        }
        return $data;
    }

    public function irrigation_list_day()
    {
        $data = array();
        $tabs = $this->irrigation_list();
        foreach ($tabs as $key => $value) {
            $data[$value->id] = $value->day;
        }
        return $data;
    }



 
}
