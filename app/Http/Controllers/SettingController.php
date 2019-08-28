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

class SettingController extends Controller {

   	public $logs_path;
    function __construct() {
 		$this->logs_path = "setting";
    }


    public function probability_set()
    {
        $params = $this->getAngularjsParam(true);

        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $res['data'] = DB::table('probability_set')->get(['*']);
    END:
        return Response::json($res);  
    }

    public function probability_set_edit()
    {
        $params = $this->getAngularjsParam(true);
        //dd($params);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $data = $params['probability'];
        $res['data'] = DB::table('probability_set')->update($data);
    END:
        return Response::json($res);  
    }

    public function irrigation_set()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $res['data'] = DB::table('irrigation_set')->get(['*']);
    END:
        return Response::json($res);  
    }

    public function get_irrigation_set_one()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $res['data'] = DB::table('irrigation_set')->where('id',$params['irrigation_id'])->get(['*']);
    END:
        return Response::json($res);  
    }

    public function irrigation_set_add()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';

        $file_info = isset($params['singlePic'])?$params['singlePic']:null;
        if ($file_info) {
                $result = UploadTools::upload_file("irrigation", $file_info);
                if ($result['ret'] != 0) {
                        $res['ret'] = -2;
                        $res['msg'] = $result['msg'];
                        goto END;
                }

                $pic_path = $result['fileurl'];
                $params['irrigation']['pic_path'] = $pic_path;
        } else{
            $res['ret'] = 1;
                $res['msg'] = "请上传有效图片";
                goto END;
        } 

        $params['irrigation']['id'] = DB::table('irrigation_set')->insertGetId($params['irrigation']);
        
        $res['data'] = $params['irrigation'];
    END:
        return Response::json($res);  
    }


    public function irrigation_set_edit()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';

        $file_info = isset($params['singlePic'])?$params['singlePic']:null;
        if ($file_info) {
                $result = UploadTools::upload_file("irrigation", $file_info);
                if ($result['ret'] != 0) {
                        $res['ret'] = -2;
                        $res['msg'] = $result['msg'];
                        goto END;
                }

                $pic_path = $result['fileurl'];
                $params['irrigation']['pic_path'] = $pic_path;
        } 
        DB::table('irrigation_set')->where('id',$params['irrigation']['id'])->update($params['irrigation']);
        $res['data'] = $params['irrigation'];
    END:
        return Response::json($res);  
    }


    public function rechargeable_vouchers_list()
    {
        $params = $this->getAngularjsParam(true);

        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $res['data'] = DB::table('rechargeable_vouchers')->get(['*']);
    END:
        return Response::json($res);  
    }


    public function rechargeable_vouchers_add()
    {
        $params = $this->getAngularjsParam(true);
        $rechargeableinfo = $params['rechargeableinfo'];
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $sqlData['price'] = $rechargeableinfo['price'];
        $rechargeableinfo['id'] = DB::table('rechargeable_vouchers')->insertGetId($sqlData);
        $rechargeableinfo['is_delete'] = 1;
        $res['data'] = $rechargeableinfo;
    END:
        return Response::json($res);  
    }

    public function rechargeable_vouchers_edit()
    {
        $params = $this->getAngularjsParam(true);
        $rechargeableinfo = $params['rechargeableinfo'];
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $sqlData['price'] = $rechargeableinfo['price'];
        DB::table('rechargeable_vouchers')->where('id',$rechargeableinfo['id'])->update($sqlData);
        $res['data'] = $rechargeableinfo;
    END:
        return Response::json($res);  
    }

    public function rechargeable_vouchers_remove()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $is_delete = 1;
        if($params['rechargeableinfo']['is_delete'] == 1){
            $is_delete = 2;
        }
        DB::table('rechargeable_vouchers')->where('id',$params['rechargeableinfo']['id'])->update(array('is_delete'=>$is_delete));
        $params['rechargeableinfo']['is_delete'] = $is_delete;
        $res['data'] = $params['rechargeableinfo'];
    END:
        return Response::json($res);  
    }


}


