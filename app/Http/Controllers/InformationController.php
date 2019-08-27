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

class InformationController extends Controller {

   	public $logs_path;
    function __construct() {
 		$this->logs_path = "information";
    }


    public function information_set()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $res['data'] = DB::table('information_set')->get(['*']);
    END:
        return Response::json($res);  
    }


    public function information_set_edit()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        DB::table('information_set')->update(array('is_shenhe'=>$params['information']['is_shenhe'],'creattime'=>now()));
        $res['data'] = $params['information'];
    END:
        return Response::json($res);  
    }

    public function information_requirement_list()
    {
        $params = $this->getAngularjsParam(true);

        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $search = $params['search'];
        $tabs = DB::table('information_requirement as ir')
                ->leftJoin('wx_users','wx_users.openid','=','ir.openid')
                ->where(function($query) use($search) {
                    if(isset($search['is_shenhe'])){
                        $query->where('ir.is_shenhe', $search['is_shenhe']);
                    }  
                })
                ->where(function($query) use($search) {
                    if(isset($search['type'])){
                        $query->where('ir.type', $search['type']);
                    }  
                })
                ->where(function($query) use($search) {
                    if(isset($search['transaction_status'])){
                        $query->where('ir.transaction_status', $search['transaction_status']);
                    }  
                })
                ->orderBy('ir.id','desc')
                ->select(['ir.*','wx_users.nickName','wx_users.avatarUrl','wx_users.phone'])
                ->paginate($search["itemsPerPage"])
                ->toArray();
        $tree_catagory_list_name = $this->tree_catagory_list_name();
        //dd($tree_catagory_list_name);
        $admin_list_name = $this->admin_list_name();
        foreach ($tabs['data'] as $key => $value) {
            if($value->admin_id != 0){
                $tabs['data'][$key]->admin_name = $admin_list_name[$value->admin_id];
            }
            if(!empty($value->transaction_openid)){
                $transaction_user = DB::table('wx_users1')
                    ->where('openid',$value->transaction_openid)
                    ->get(['nickName','avatarUrl','phone']);
                    dd($transaction_user);
                $tabs['data'][$key]->transaction_nickName = $transaction_user[0]->nickName;
                $tabs['data'][$key]->transaction_avatarUrl = $transaction_user[0]->avatarUrl;
                $tabs['data'][$key]->transaction_phone = $transaction_user[0]->phone;
            }
            $tabs['data'][$key]->have_tree_name = $tree_catagory_list_name[$value->have_tree];
            if($value->need_tree != 0){
                $tabs['data'][$key]->need_tree_name = $tree_catagory_list_name[$value->need_tree];
            }
        }
        $res['data'] = $tabs;
    END:
        return Response::json($res);  
    }


    public function information_requirement_shenhe()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $sqlData['is_shenhe'] = isset($params['shenhe']['is_shenhe'])?$params['shenhe']['is_shenhe']:1;
        $sqlData['shenhe_summary'] = isset($params['shenhe']['shenhe_summary'])?trim($params['shenhe']['shenhe_summary']):'';
        DB::table('information_requirement')->where('id',$params['shenhe']['id'])->update($sqlData);
        $res['data'] = $params['shenhe'];
    END:
        return Response::json($res); 
    }

    public function information_requirement_deliverGoods()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $sqlData['transaction_status'] = isset($params['fahuo']['transaction_status'])?$params['fahuo']['transaction_status']:1;
        $sqlData['fahuo_summary'] = isset($params['fahuo']['shenhe_summary'])?trim($params['fahuo']['fahuo_summary']):'';
        DB::table('information_requirement')->where('id',$params['fahuo']['id'])->update($sqlData);
        $res['data'] = $params['fahuo'];
    END:
        return Response::json($res); 
    }


    public function information_admin()
    {
        $params = $this->getAngularjsParam(true);

        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $search = $params['search'];
        $time = isset($search['time'])?$search['time']:(date("Y-m-d",strtotime("-29 day"))." - ".date("Y-m-d"));
        $time_data = explode(" - ", $time);
        $tabs = DB::table('information_admin as st')
                ->leftJoin('wx_users as wx','wx.openid','=','st.admin_id')
                ->leftJoin('tree_list as tree','tree.id','=','st.tree_id')
                ->leftJoin('tree_catagory as catagory','catagory.id','=','tree.catagory_id')
                ->leftJoin('tree_base as base','base.id','=','tree.base_id')
                ->where(function($query) use($search) {
                    if(isset($search['base_id'])){
                        $query->where('base.id', $search['base_id']);
                    }  
                })
                ->where(function($query) use($search) {
                    if(isset($search['catagory_id'])){
                        $query->where('catagory.id', $search['catagory_id']);
                    }  
                })
                ->whereBetween('st.creattime',[$time_data[0]." 00:00:00",$time_data[1]." 23:59:59"])
                ->orderBy('st.id','desc')
                ->select(['st.*','wx.nickName','tree.scale_w','tree.scale_h','catagory.name as catagory_name','catagory.code','base.name as base_name','base.scale_h as base_scale_h','base.scale_w as base_scale_w'])
                ->paginate($search["itemsPerPage"])
                ->toArray();
        foreach ($tabs['data'] as $key => $value) {
            if($value->scale_w < 10){
                $tabs['data'][$key]->scale_w = "0".$value->scale_w;
            }
            if($value->scale_h < 10){
                $tabs['data'][$key]->scale_h = "0".$value->scale_h;
            }
            $tabs['data'][$key]->imglist = array();
            $tabs['data'][$key]->videolist = array();
            $list = DB::table('information_admin_img')->where('tree_id',$value->tree_id)->get(['*']);
            if(count($list) >0){
                foreach ($list as $k => $val) {
                    if($val->type == 1){
                        array_push($tabs['data'][$key]->imglist, array('id'=>$val->id,'data_path'=>$val->data_path));
                    }else{
                        array_push($tabs['data'][$key]->videolist, array('id'=>$val->id,'data_path'=>$val->data_path));
                    }
                }
            }
            
        }

        $res['data'] = $tabs;
    END:
        return Response::json($res); 
    }


    


}


