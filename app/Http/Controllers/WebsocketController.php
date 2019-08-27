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

class WebsocketController extends Controller {

   	public $logs_path;
    function __construct() {
 		$this->logs_path = "websocket";
    }


    public function users_list()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $tabs = DB::table('users')->get(['*']);
        $res['data'] = $tabs;
    END:
        return Response::json($res);
    }


    public function push_msg()
    {
    	$params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        
        // 指明给谁推送，为空表示向所有在线用户推送
		$to_uid = isset($params['usersinfo']['user_id'])?(int)$params['usersinfo']['user_id']:'';
		// 推送的url地址，使用自己的服务器地址
		$push_api_url = getenv('PUSH_MSG_URL');
		$post_data = array(
		   "type" => "publish",
		   "content" => $params['usersinfo']['content'],
		   "to" => $to_uid, 
		);
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $push_api_url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Expect:"));
		$return = curl_exec ( $ch );
		curl_close ( $ch );
		var_export($return);

	// 	if($return != "ok1"){
	// 		$res['ret'] = 1;
 //        	$res['msg'] = '消息推送失败';
	// 	}
	// END:
 //        return Response::json($res);
    }
    





}
