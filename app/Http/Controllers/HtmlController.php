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


    public function all()
    {
        $params = Request::all();
        $database = getenv('DB_DATABASE');        
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $game_id = $params['game_id'];
        $category_list = DB::table($database.'.game_category')->where($database.'.game_category.game_id',$game_id)->where($database.'.game_category.is_delete',0)->get([$database.'.game_category.*']);
        $category_arr = [];
        foreach ($category_list as $key => $value) {
            $arr = explode("_",$value->mysql_table);
            $category_list[$key]->catalog = "/pages/".$arr[count($arr)-1]."/index";
            $category_list[$key]->child = array();
        }
        //DB::connection("mysql_other")->table("user")->where("id",$id)->update([]);
        $servername = getenv('DB_HOST_'.$game_id);
        $username = getenv('DB_USERNAME_'.$game_id);
        $password = getenv('DB_PASSWORD_'.$game_id);

        $conn = mysqli_connect($servername, $username, $password);
        if(! $conn )
        {
            die('连接失败: ' . mysqli_error($conn));
        }
        // 设置编码，防止中文乱码
        //mysqli_query($conn , "set names utf8");
        //dd($category_list);
        foreach ($category_list as $key => $value) {
            $sql = "SELECT * FROM ".$value->mysql_table." order By id desc limit ".$value->show_num;
            mysqli_select_db($conn,getenv('DB_DATABASE_'.$game_id));
            $result = mysqli_query($conn,$sql);
            if(!$result){
                 dd(mysqli_errno($conn).': '.mysqli_error($conn));
            }


            while($row =$result->fetch_array(MYSQLI_ASSOC)){
                $arr =explode("/",$value->catalog);
                $row['catalog'] ="/pages/".$arr[2]."/info";
                $category_list[$key]->child[] = $row;
            }
           
        }
        
        mysqli_close($conn);

        $game_list = DB::table($database.'.game_list')->where($database.'.game_list.id',$game_id)->get([$database.'.game_list.*'])[0];

        $swiper_list = DB::table($database.'.swiper_list')->where($database.'.swiper_list.game_id',$game_id)->where($database.'.swiper_list.is_delete',0)->get([$database.'.swiper_list.*']);
        foreach ($swiper_list as $key => $value) {
            //https://m.019103.com/news/1263.html
            $arr = explode("/",$value->url);
            $swiper_list[$key]->catalog = "/pages/".$arr[count($arr)-2]."/info";
            $swiper_list[$key]->action_id = explode(".",$arr[count($arr)-1])[0];
        }    
        // $res['recommend_list'] = array_values($recommend_list);
        $res['swiper_list'] = $swiper_list;
        $res['game_list'] = $game_list;
        $res['category_list'] = $category_list;
    END:
        return Response::json($res);  
    }


    public function navlist()
    {
        $params = Request::all();
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $database = getenv('DB_DATABASE'); 
        $category_id = (int)$params['actionid'];
        $game_id = (int)$params['game_id'];
        $pagenum = (int)$params['pagenum'];
        // $category_id = 10;
        // $game_id = 4;
        // $pagenum = 1;
        $category_list = DB::table($database.'.game_category')->where($database.'.game_category.game_id',$game_id)->where($database.'.game_category.is_delete',0)->get([$database.'.game_category.*']);

        $servername = getenv('DB_HOST_'.$game_id);
        $username = getenv('DB_USERNAME_'.$game_id);
        $password = getenv('DB_PASSWORD_'.$game_id);

        $conn = mysqli_connect($servername, $username, $password);
        if(! $conn )
        {
            die('连接失败: ' . mysqli_error($conn));
        }
        // 设置编码，防止中文乱码
        mysqli_query($conn , "set names utf8");

        foreach ($category_list as $key => $value) {
            //if($category_id == $value->id){
                $arr = explode("_",$value->mysql_table);
                $category_list[$key]->catalog = "/pages/".$arr[count($arr)-1]."/index";
                $category_list[$key]->child_catalog = "/pages/".$arr[count($arr)-1]."/info";

                $sql = "SELECT * FROM ".$value->mysql_table." order By id desc limit ".($pagenum-1)*$value->nav_num.",".$value->nav_num;
                mysqli_select_db($conn,getenv('DB_DATABASE_'.$game_id));
                $result = mysqli_query($conn,$sql);
                if(!$result){
                     dd(mysqli_errno($conn).': '.mysqli_error($conn));
                }

                while($row =$result->fetch_array(MYSQLI_ASSOC)){
                    $category_list[$key]->child[] = $row;
                }
            //}
           
        }
        
        mysqli_close($conn);
        $res['data'] = $category_list;
    END:
        return Response::json($res); 
    }


    public function get_onReach_navlist()
    {
        $params = Request::all();
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $database = getenv('DB_DATABASE'); 
        $category_id = (int)$params['actionid'];
        $game_id = (int)$params['game_id'];
        $pagenum = (int)$params['pagenum'];
        $list = $params['list'][0];

        $servername = getenv('DB_HOST_'.$game_id);
        $username = getenv('DB_USERNAME_'.$game_id);
        $password = getenv('DB_PASSWORD_'.$game_id);

        $conn = mysqli_connect($servername, $username, $password);
        if(! $conn )
        {
            die('连接失败: ' . mysqli_error($conn));
        }
        // 设置编码，防止中文乱码
        mysqli_query($conn , "set names utf8");

        $sql = "SELECT * FROM ".$list['mysql_table']." order By id desc limit ".($pagenum-1)*$list['nav_num'].",".$list['nav_num'];
        mysqli_select_db($conn,getenv('DB_DATABASE_'.$game_id));
        $result = mysqli_query($conn,$sql);
        if(!$result){
             dd(mysqli_errno($conn).': '.mysqli_error($conn));
        }
        $data = array();
        while($row =$result->fetch_array(MYSQLI_ASSOC)){
            $data[] = $row;
        }
           
        
        
        mysqli_close($conn);
        $res['data'] = $data;
    END:
        return Response::json($res);
    }
   

   public function navinfo()
   {
        $params = Request::all();
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $database = getenv('DB_DATABASE'); 
        $infoid = (int)$params['infoid'];
        $game_id = (int)$params['game_id'];
        $mysql_table = $params['mysql_table'];

        $servername = getenv('DB_HOST_'.$game_id);
        $username = getenv('DB_USERNAME_'.$game_id);
        $password = getenv('DB_PASSWORD_'.$game_id);

        $conn = mysqli_connect($servername, $username, $password);
        if(! $conn )
        {
            die('连接失败: ' . mysqli_error($conn));
        }
        // 设置编码，防止中文乱码
        mysqli_query($conn , "set names utf8");

        $sql = "SELECT ".$mysql_table.".*,".$mysql_table."_data_1.newstext FROM ".$mysql_table." inner join ".$mysql_table."_data_1  on ".$mysql_table."_data_1.id = ".$mysql_table.".id where ".$mysql_table.".id = ".$infoid;
        mysqli_select_db($conn,getenv('DB_DATABASE_'.$game_id));
        $result = mysqli_query($conn,$sql);
        if(!$result){
             dd(mysqli_errno($conn).': '.mysqli_error($conn));
        }
        $data = array();
        while($row =$result->fetch_array(MYSQLI_ASSOC)){
            $row['newstime'] = date("Y-m-d",$row['newstime']);
            // $row['newstext'] = strip_tags(htmlspecialchars_decode($row['newstext']));
            $row['newstext'] = str_replace('img src=\"https', 'img src="https', $row['newstext']);
            $row['newstext'] = str_replace('img src="\https', 'img src="https', $row['newstext']);            
            $row['newstext'] = str_replace('jpg\"', 'jpg"', $row['newstext']);
            $data[] = $row;
        }
           
        
        
        mysqli_close($conn);
        $res['data'] = $data;
    END:
        return Response::json($res);
   }

}
