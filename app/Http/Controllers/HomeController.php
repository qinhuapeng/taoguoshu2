<?php
/**
 * Created by PhpStorm.
 * User: link
 * Date: 2016/4/25
 * Time: 15:22
 */

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Log\Writer;
use Auth;
use App\User;
use Crypt;
use Hash;

class HomeController extends Controller {

   	public $logs_path;
    function __construct() {
 		$this->logs_path = "home";
    }


    public function overview()
    {
        $params= $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $day_3 = date("Y-m-d",strtotime("-3 day")); 
        $day_7 = date("Y-m-d",strtotime("-7 day")); 
        $day_30 = date("Y-m-d",strtotime("-30 day")); 
        dd($day_30);
        //获取最近3天 7天 30天的各个数据
        $user_30 = DB::select(DB::raw("select count(id) as num,creattime from  wx_users where DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= creattime group by creattime"));
        foreach ($user_30 as $key => $value) {
            
        }

        
        $res['user_tabs'] = $user_30;
    END:
        return Response::json($res);
    }







}
