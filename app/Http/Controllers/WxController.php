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
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Log\Writer;
use Auth;
use App\Tools\Tools;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;


class WxController extends Controller {

    public $logs_path;
    public $appid;
    public $appsecret;
    public $mch_id;
    public $wx_pay_key;
    function __construct() {
        $this->logs_path = "wx";
        $this->appid = getenv("WX_APPID");
        $this->appsecret = getenv("WX_APPSECRET");
        $this->mch_id = getenv("MCH_ID");
        $this->wx_pay_key = getenv("WX_PAY_KEY");
    }



    public function login()
    {
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $params = Request::all();
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$this->appid."&secret=".$this->appsecret."&js_code=".$params['code']."&grant_type=authorization_code";
        $result = Tools::curl_get($url,10);
        if(!$result){
            $res['ret'] = 1;
            $res['msg'] = '网络错误';
            goto END;
        }
        $loginData = json_decode($result);

        $openid = md5(getenv("APP_URL").'-'.$loginData->openid);
        Cache::forget('session_key'.$openid);
        Cache::forever('session_key'.$openid, $loginData->session_key);

        $userinfo = DB::table('wx_users')->where('openid',$openid)->get(['*']);
        if(count($userinfo) != 1){
           DB::table('wx_users')->insertGetId((array('openid'=>$openid))); 
           $userinfo = array();
           $userinfo[0]['openid'] = $openid;
           $userinfo[0]['nickName'] = "";
           $userinfo[0]['avatarUrl'] = "";
        }
        $res['data'] = $userinfo;
    END:
        return Response::json($res);
    }

    public function setuserinfo()
    {
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $params = $this->getAngularjsParam(true);
        $params['userinfo']['jsonStr'] = json_encode($params['userinfo']);
        DB::table('wx_users')->where('openid',$params['openid'])->update($params['userinfo']);
        $res['data'] = $params['userinfo'];
    END:
        return Response::json($res);
    }

    public function getuserinfo()
    {
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $params = $this->getAngularjsParam(true);
        $openid = $params['openid'];
        $res['data'] = DB::table('wx_users')->where('openid',$openid)->get(['*']);
    END:
        return Response::json($res);
    }

    public function get_access_token()
    {
        if (Cache::has('access_token')) {
            $access_token = Cache::get('access_token');
        }else{
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->appsecret;
            $jsonData = Tools::curl_get($url,15);
            $result = json_decode($jsonData,true);
            Cache::put('access_token', $result['access_token'], ($result['expires_in'])/60);
            $access_token =$result['access_token']; 
        }
        return $access_token;
    }



    public function get_jsapi_ticket()
    {
        if (Cache::has('jsapi_ticket')) {
            $result['errcode'] = 0;
            $result['ticket'] = Cache::get('jsapi_ticket');
        }else{
            $access_token = $this->get_access_token();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
            $jsonData = Tools::curl_get($url,15);
            $result = json_decode($jsonData,true);
            
            // {
            // "errcode":0,
            // "errmsg":"ok",
            // "ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKd8-41ZO3MhKoyN5OfkWITDGgnr2fwJ0m9E8NYzWKVZvdVtaUgWvsdshFKA",
            // "expires_in":7200
            // }
            if($result['errcode'] == 0){
                Cache::put('jsapi_ticket', $result['ticket'], ($result['expires_in'])/60);
            }
            
        }
        return $result;
    }



    public function get_nonceStr($length=16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
          $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function get_signature($jsapi_ticket,$nonceStr,$timestamp,$wxurl)
    {
        $string = "jsapi_ticket=".$jsapi_ticket."&noncestr=".$nonceStr."&timestamp=".$timestamp."&url=".$wxurl;
        $signature = sha1($string);
        return $signature;
    }

    public function get_WXGZH_config()
    {
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $params = Request::all();
        $jsapi_ticket_result = $this->get_jsapi_ticket();
        if($jsapi_ticket_result['errcode'] != 0){
            $res['ret'] = -1;
            $res['msg'] = 'jsapi_ticket_err';
            goto END;
        }else{
            $jsapi_ticket = $jsapi_ticket_result['ticket'];
        }
        $timestamp = time();
        $nonceStr = $this->get_nonceStr();   
        //$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";  
        $wxurl = $params['wxurl']; 
        $signature = $this->get_signature($jsapi_ticket,$nonceStr,$timestamp,$wxurl);
        $res['data'] = array('debug'=>false,'appId'=>$this->appid,'timestamp'=>$timestamp,'nonceStr'=>$nonceStr,'signature'=>$signature,'jsApiList'=>['chooseVideo','chooseImage','getLocalImgData']);
    END:
        return Response::json($res);
    }

    //根据腾讯地图地址获取经纬度
    public function get_longitude_latitude_by_address()
    {
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $params = $this->getAngularjsParam(true);
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?address='".$params['address']."'&key=".getenv("ADDRESS_KEY");
        $jsonData = Tools::curl_get($url,15);
        if(!$jsonData){
            $res['ret'] = 1;
            $res['msg'] = '网络错误';
            goto END;
        }
        $result = json_decode($jsonData,true);
        if($result['status'] != 0){
            $res['ret'] = 1;
            $res['msg'] = $result['message'];
            goto END;
        }
        $res['data'] = $result['result'];
    END:
        return Response::json($res);
    }


    //获取手机号码
    public function getPhoneNumber()
    {
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $params = $this->getAngularjsParam(true);
        $iv = $params['iv'];
        $encryptedData = $params['encryptedData'];
        $openid = $params['openid'];
        $code = $params['code'];
        if(!Cache::has('session_key'.$openid)){
            if($result['status'] != 0){
            $res['ret'] = 1;
            $res['msg'] = "手机获取失败";
            goto END;
        }
            
        }
        $session_key = Cache::get('session_key'.$openid);
        $result = $this->decryptData($encryptedData,$iv,$session_key,$code);
        DB::table('wx_users')->where('openid',$openid)->update(array('phone'=>$result->phoneNumber));
        $res['data'] = $result;
    END:
        return Response::json($res);
    }


    public function decryptData( $encryptedData, $iv,$session_key,$code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$this->appid."&secret=".$this->appsecret."&js_code=".$code."&grant_type=authorization_code";
        $result = Tools::curl_get($url,10);
        $loginData = json_decode($result);
        $session_key = $loginData->session_key;

        if (strlen($session_key) != 24) {
            return "encodingAesKey 非法";
        }
        $aesKey=base64_decode($session_key);
        
        if (strlen($iv) != 24) {
            return "iv 非法";
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);
        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj=json_decode($result);
        if( $dataObj  == NULL )
        {
            return "aes1 解密失败";
        }
        if( $dataObj->watermark->appid != $this->appid )
        {
            return "aes2 解密失败";
        }
        $data = $result;
        
        return $dataObj;
    }




     // -41001: encodingAesKey 非法
     // -41003: aes 解密失败
     // -41004: 解密后得到的buffer非法
     // -41005: base64加密失败
     // -41016: base64解密失败
        // public static $OK = 0;
        // public static $IllegalAesKey = -41001;
        // public static $IllegalIv = -41002;
        // public static $IllegalBuffer = -41003;
        // public static $DecodeBase64Error = -41004;
        // 
    public function getwxuserinfo_gongzhonghao()
    {
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $params = Request::all();
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxebf0cf6e03b9791f&secret=69f8472c848c5ce72c043c742ce4ea51&code=".$params['code']."&grant_type=authorization_code";
        $json_str = Tools::curl_get($url,10);
        $result = json_decode($json_str,true);
        $access_token = $result['access_token'];
        $openid = $result['openid'];
        //获取用户信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $json_str = Tools::curl_get($url,10);
        $result = json_decode($json_str,true);
        
        $sqlData = array();
        $sqlData['openid'] = $result['openid'];
        $sqlData['nickname'] = $result['nickname'];
        $sqlData['sex'] = $result['sex'];
        $sqlData['headimgurl'] = $result['headimgurl'];
        $sqlData['province'] = $result['province'];
        $sqlData['city'] = $result['city'];

        $repeat_count = DB::table('wx_users')->where('openid',$result['openid'])->count();
        if($repeat_count == 0){
            DB::table('wx_users')->insert($sqlData);
        }

        $url = getenv('APP_URL').$params['redirectUrl'];
        return redirect()->to($url)
                            ->cookie('userinfo',json_encode($sqlData),config::get('session.lifetime'),null,null,false,false);
    }

    //每次支付前，先微信内部生成pay_id
    public function wx_pay_order()
    {
        $pay_url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        //参数生成xml
        $data = array();
        $data['appid'] = $this->appid;
        $data['body'] = '购买储值卷';
        $data['mch_id'] = $this->mch_id;
        $data['nonce_str'] = $this->get_nonceStr();
        $data['notify_url'] = ;
        $data['out_trade_no'] = 'abxksk21920skaldksla';//自己的订单id
        $data['spbill_create_ip'] = $_SERVER['SERVER_ADDR'];
        $data['total_fee'] = 1; //价格1分   
        $data['trade_type'] = 'JSAPI';
        $sign = $this->wx_sign($data,$this->wx_pay_key);
        $data['sign'] = $sign;
    }


    public function get_WX_config()
    {

    }


    //数组转xml
    public function ArrToXml($arr)
    {
        if(!is_array($arr) || count($arr) == 0) return '';
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    public function wx_sign($params,$KEY){
        //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);  //参数进行拼接key=value&k=v
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 将参数拼接为url: key=value&key=value
     * @param $params
     * @return string
     */
    public function ToUrlParams($params){
        $string = '';
        if( !empty($params) ){
            $array = array();
            foreach( $params as $key => $value ){
                $array[] = $key.'='.$value;
            }
            $string = implode("&",$array);
        }
        return $string;
    }


}
