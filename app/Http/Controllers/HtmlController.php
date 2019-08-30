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

        $tree_list = DB::table('tree_list as list')
                    ->leftJoin('tree_catagory as catagory','catagory.id','=','list.catagory_id')
                    ->leftJoin('tree_cycle as cycle', function ($join) {
                        $join->on('cycle.base_id', '=', 'list.base_id')
                        ->on('cycle.catagory_id', '=', 'list.catagory_id');
                    })
                    ->where('list.is_delete',1)->where('list.status',1)
                    ->where('openid',$openid)
                    ->get(['list.*','cycle.irrigation_open','cycle.starttime','cycle.endtime','catagory.code']);

        foreach ($tree_list as $key => $value) {
            $curing_proportion = json_decode($value->curing_proportion,true);
            $curing_proportion = $this->irrigation_price($value->id,$value->price,$value->steal_time,$curing_proportion,$value->starttime,$value->endtime);
            foreach ($curing_proportion as $cp_key => $cp_value) {
                $curing_proportion[$cp_key]['irrigation_time'] = $value->steal_time;
                $curing_proportion[$cp_key]['is_irrigation'] = false;
                foreach ($tree_irrigation_tabs as $k => $val) {
                    if($val->tree_id == $value->id){
                        if($val->irrigation_type == $cp_value['id']){
                            $curing_proportion[$cp_key]['irrigation_time'] = $val->irrigation_time;
                        }
                    }
                }
                $tree_list[$key]->curing_proportion = $curing_proportion;
            }
        }
        foreach ($tree_list as $key => $value) {
            $tree_list[$key]->irrigation_price = "0.00";
            $tree_list[$key]->scale_w = $value->scale_w<10?'0'.$value->scale_w:$value->scale_w;
            $tree_list[$key]->scale_h = $value->scale_h<10?'0'.$value->scale_h:$value->scale_h;
            foreach ($value->curing_proportion as $k => $val) {
                $now = date('Y-m-d H:i:s');
                if($val['type'] == 1){
                    $limit_day = $this->time_interval($val['irrigation_time'],$now)['day'] - $irrigation_list_day[$val['id']];
                    if($limit_day >= 0){
                        $tree_list[$key]->curing_proportion[$k]['is_irrigation'] = true;
                        $tree_list[$key]->irrigation_price += $val['price'];
                    }
                }else{
                    if($value->irrigation_open == 1){
                        $tree_list[$key]->curing_proportion[$k]['is_irrigation'] = true;
                        $tree_list[$key]->irrigation_price += $val['price'];
                    }
                }
                
            }
        }
        $data['tree_list'] = $tree_list;
        $res['data'] = $data;
        dd($data);
    END:
        return Response::json($res); 
    }


    public function irrigation_price($tree_id,$price,$steal_time,$curing_proportion,$starttime,$endtime)
    {
        $irrigation_id_arr = array();
        foreach ($curing_proportion as $key => $value) {
            if($value['type'] == 2){
                $price -= $value['num'];
            }else{
                array_push($irrigation_id_arr,$value['id']);
            }
        }
        $tabs = DB::table('tree_list_irrigation_info')->where('tree_id',$tree_id)->whereIn('irrigation_type',$irrigation_id_arr)->get(['*']);
        if(count($tabs) > 0){
            foreach ($tabs as $key => $value) {
                $price -= $value->irrigation_price;
            }
        }
        //获取还有多少天
        $day_arr = $this->time_interval($steal_time,$endtime);
        
        $day_sy = $day_arr['hour']/24+$day_arr['day'];
        $irrigation_list_day = $this->irrigation_list_day();
        foreach ($curing_proportion as $key => $value) {
            if($value['type'] == 2){
                $curing_proportion[$key]['price'] = $value['num'];
            }else{
                $curing_proportion[$key]['price']=round($price*$value['num']/100*$irrigation_list_day[$value['id']]/$day_sy,2);
            }
        }
        return $curing_proportion;
    }

}
