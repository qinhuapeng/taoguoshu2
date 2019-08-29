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

class HtmlController extends Controller {

   	public $logs_path;
    function __construct() {
 		$this->logs_path = "html";
    }

    public function index()
    {
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $params = Request::all();
        //$openid = $params['openid'];
        $openid = 'fe464eede6c2d1bef9609e5eac1742e8';
        $data = array();
        $tree_id_arr = array();
        $data['steal_level'] = DB::table('wx_users as user')
        						->leftJoin('steal_level as level','level.id','=','user.steal_level')
        						->get(['user.*','level.name as steal_level_name']);
        $irrigation_list = DB::table('irrigation_set')->get(['id','name','day']);
        $tree_tabs = DB::table('tree_list as list')
                    ->leftJoin('tree_base as base','base.id','=','list.base_id')
                    ->leftJoin('tree_catagory as catagory','catagory.id','=','list.catagory_id')
                    ->where('list.is_delete',1)
                    ->where('list.status',1)
                    ->where('openid',$openid)
                    ->get(['list.*','catagory.name as catagory_name','catagory.code as code','base.name as base_name']);
        foreach ($tree_tabs as $key => $value) {
        	array_push($tree_id_arr,$value->id);
        }

        $data['video_list'] = array();
        $data['img_list'] = array();
        $iv_tabs = DB::table('information_admin_img')->where('is_delete',0)->whereIn('tree_id',$tree_id_arr)->orderBy('id','desc')->get(['*']);
        foreach ($iv_tabs as $key => $value) {
        	if($value->type == 1){
        		array_push($data['img_list'],$value);
        	}else{
				array_push($data['video_list'],$value);
        	}
        }
        $tree_irrigation_tabs = DB::table('tree_list_irrigation_info as info')
        			->leftJoin('tree_list as list','list.id','=','info.tree_id')
        			->where('list.is_delete',1)
        			->where('list.status',1)
        			->where('info.openid',$openid)
        			->orderBy('info.id','desc')
        			->groupBy('info.irrigation_type')
        			->groupBy('info.tree_id')
        			->get([DB::raw('max(info.creattime) as irrigation_time'),'info.irrigation_type','info.tree_id','info.openid']);
        $irrigation_list_day = $this->irrigation_list_day();

        $tree_irrigation_data = array();
        dd($tree_irrigation_tabs);
        foreach ($tree_irrigation_tabs as $key => $value) {
            $tree_id = $value->tree_id;
            $tree_irrigation_data[$tree_id]['tree_id'] = $value->tree_id;
            $tree_irrigation_data[$tree_id]['openid'] = $value->openid;
            if(!isset($tree_irrigation_data[$tree_id]['child'])){
                $tree_irrigation_data[$tree_id]['child'] = array();
            }
            $irrigation_type =$value->irrigation_type;
            $tree_irrigation_data[$tree_id]['child'][$irrigation_type]['irrigation_type'] = $value->irrigation_type;
            $tree_irrigation_data[$tree_id]['child'][$irrigation_type]['irrigation_time'] = $value->irrigation_time;
            $tree_irrigation_data[$tree_id]['child'] = array_values($tree_irrigation_data[$tree_id]['child']);
        }
        $tree_irrigation_data = array_values($tree_irrigation_data);


        dd($tree_irrigation_data);

        foreach ($irrigation_list_day as $key => $value) {
            
        }
        foreach ($tree_tabs as $key => $value) {
            $tree_tabs[$key]->irrigation_list  = $irrigation_list;
            $tree_tabs[$key]->irrigation_need = array();
            foreach ($tree_irrigation_tabs as $k => $val) {
                if($val->tree_id == $value->id){

                }
            }
        }
        dd($tree_irrigation_tabs);
        $data['tree_list'] = $tree_tabs;
        $res['data'] = $data;
    END:
        return Response::json($res); 
    }

}
