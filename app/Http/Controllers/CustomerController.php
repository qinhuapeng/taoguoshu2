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
use App\Tools\UploadTools;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller {

   	public $logs_path;
    function __construct() {
 		$this->logs_path = "customer";
    }


    public function customer_list()
    {
        $params = $this->getAngularjsParam(true);
        $search = $params['search'];
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $tabs = DB::table('wx_users as users')
                ->where(function($query) use($search) {
                    if(isset($search['is_upload'])){
                        $query->where('users.is_upload', $search['is_upload']);
                    }  
                })
                ->where(function($query) use($search) {
                    if(isset($search['nickName'])){
                        $query->where('users.nickName', 'like','%'.$search['nickName'].'%');
                    }  
                })
                ->orderBy('users.id','desc')
                ->select(['users.*'])
                ->paginate($search["itemsPerPage"])
                ->toArray();
        $irrigation_list = $this->irrigation_list();
        $openid_arr = array();
        $openid_arr = array();
        foreach ($tabs['data'] as $key => $value) {
            $tabs['data'][$key]->tree_num = 0;
            $tabs['data'][$key]->irrigation_child = array();
            foreach ($irrigation_list as $k => $val) {
                array_push($tabs['data'][$key]->irrigation_child,array('id'=>$val->id,'name'=>$val->name,'num'=>0));
            }
            array_push($openid_arr,$value->openid);
        }

        //获取养护产品数量
        $irrigation_tabs = DB::table('list_irrigation')->whereIn('openid',$openid_arr)->get(['*']);
        

        //拥有果树数量
        $tree_tabs = DB::table('tree_list_info as info')
                    ->leftJoin('tree_list as list','list.id','=','info.tree_id')
                    ->where('list.is_delete',1)->where('status',1)
                    ->whereIn('info.openid',$openid_arr)->groupBy('info.openid')->get(['info.openid',DB::raw('count(info.id) as num')]);
        
        foreach ($tabs['data'] as $key => $value) {
            foreach ($tree_tabs as $t_key => $t_value) {
                if($t_value->openid == $value->openid){
                    $tabs['data'][$key]->tree_num = $t_value->num;
                }
            }
            foreach ($value->irrigation_child as $k => $val) {
                foreach ($irrigation_tabs as $ir_key => $ir_value) {
                    if(($value->openid == $ir_value->openid) && ($val['id']==$ir_value->irrigation_id)){
                        $tabs['data'][$key]->irrigation_child[$k]['num'] = $ir_value->num;
                    }
                }
            }   
        }
        $res['data'] = $tabs;
    END:
        return Response::json($res);  
    }


    public function customer_is_upload()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $sqlData['is_upload'] = isset($params['uploadinfo']['is_upload'])?$params['uploadinfo']['is_upload']:2;
        DB::table('wx_users')->where('id',$params['uploadinfo']['id'])->update($sqlData);
        $res['data'] = $params['uploadinfo'];
    END:
        return Response::json($res); 
    }


    public function irrigation_nfo()
    {
        $params = $this->getAngularjsParam(true);
        $irrigation = $params['irrigation'];
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $irrigation_list = $irrigation['irrigation_child'];
        $irrigation_info = DB::table('tree_list_irrigation_info as info')
                            ->leftJoin('tree_list as list','list.id','=','info.tree_id')
                            ->leftJoin('tree_base as base','base.id','=','list.base_id')
                            ->leftJoin('tree_catagory as catagory','catagory.id','=','list.catagory_id')
                            ->leftJoin('wx_users as users','users.openid','=','info.irrigation_openid')
                            ->where('info.openid',$params['irrigation']['openid'])
                            ->where('list.is_delete',1)
                            ->where('list.status',1)
                            ->orderBy('info.id','desc')
                            ->get(['info.*','base.name as base_name','catagory.name as catagory_name','list.scale_w','list.scale_h','catagory.code','users.nickName as irrigation_name']);
        $data = array();
        foreach ($irrigation_info as $key => $value) {
            $tree_id = $value->tree_id;
            $data[$tree_id]['tree_id'] = $value->tree_id;
            $data[$tree_id]['openid'] = $value->openid;
            $data[$tree_id]['code'] = $value->code."-".($value->scale_w<10?'0'.$value->scale_w:$value->scale_w)."-".($value->scale_h<10?'0'.$value->scale_h:$value->scale_h);
            $data[$tree_id]['irrigation_list'] = $irrigation_list;

        }


        $data = array_values($data);
        
        foreach ($data as $key => $value) {
            foreach ($irrigation_info as $k => $val) {
                if($val->tree_id == $value['tree_id']){
                    foreach ($value['irrigation_list'] as $child_key => $child_value) {
                        if($child_value['id'] == $val->irrigation_type){
                            if(!isset($data[$key]['irrigation_list'][$child_key]['child'])){
                                $data[$key]['irrigation_list'][$child_key]['child']= array();
                            }   
                            array_push($data[$key]['irrigation_list'][$child_key]['child'],array('creattime'=>$val->creattime,'irrigation_name'=>$val->irrigation_name,'catagory_name'=>$val->catagory_name,'base_name'=>$val->base_name));
                        }
                    }
                }
            }
        }

        $res['data'] = $data;
    END:
        return Response::json($res); 
        //tree_list_irrigation_nfo
    }



}


