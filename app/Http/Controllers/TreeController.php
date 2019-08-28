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
use QrCode;

class TreeController extends Controller {

   	public $logs_path;
    function __construct() {
 		$this->logs_path = "tree";
    }


    public function tree_catagory()
    {
        $params = $this->getAngularjsParam(true);

        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $search = $params['search'];
        $tabs = DB::table('tree_catagory')
                ->where(function($query) use($search) {
                    if(isset($search['is_delete'])){
                        $query->where('is_delete', $search['is_delete']);
                    }  
                })
                ->orderBy('id','desc')
                ->select(['*'])
                ->paginate($params["itemsPerPage"])
                ->toArray();
        $res['data'] = $tabs;
    END:
        return Response::json($res);  
    }

    public function get_tree_catagory_one()
    {
        $params = $this->getAngularjsParam(true);
       //dd($params);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $tabs = DB::table('tree_catagory')
                ->where('id',$params['catagory_id'])
                ->get(['*']);
        $res['data'] = $tabs;
    END:
        return Response::json($res);  
    }


    public function tree_catagory_add()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $sqlData['name'] = $params['catagory']['name'];
        $sqlData['title'] = $params['catagory']['title'];
        $sqlData['summary'] = $params['catagory']['summary'];
        $sqlData['code'] = strtoupper($params['catagory']['code']);

        $repeat = DB::table('tree_catagory')
                    ->orWhere('name',$sqlData['name'])
                    ->orWhere('code',$sqlData['code'])
                    ->count();

        if ($repeat!= 0) {
                $res['ret'] = 1;
                $res['msg'] = "该果树种类名称或者编号已经存在";
                goto END;
        }
        $file_info = isset($params['singlePic'])?$params['singlePic']:null; 
        if ($file_info) {
                $result = UploadTools::upload_file("irrigation", $file_info);
                if ($result['ret'] != 0) {
                        $res['ret'] = -2;
                        $res['msg'] = $result['msg'];
                        goto END;
                }

                $sqlData['pic_path'] = $result['fileurl'];
        }else{
            $res['ret'] = 1;
            $res['msg'] = '请上传果树分类图片！'; 
            goto END;
        } 

         $params['catagory']['id'] = DB::table('tree_catagory')->insertGetId($sqlData);
         $params['catagory']['is_delete'] = 1;
         $params['catagory']['pic_path'] = $sqlData['pic_path'];
         $res['data'] = $params['catagory'];
    END:
        return Response::json($res);  
    }


    public function tree_catagory_edit()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $sqlData['name'] = $params['catagory']['name'];
        $sqlData['title'] = $params['catagory']['title'];
        $sqlData['summary'] = $params['catagory']['summary'];
        $sqlData['code'] = strtoupper($params['catagory']['code']);
        $repeat = DB::table('tree_catagory')
                    ->where('id','<>',$params['catagory']['id'])
                    ->where(function($query) use($sqlData) {
                        $query->orWhere('name',$sqlData['name']);
                        $query->orWhere('code',$sqlData['code']);
                    })
                    ->count();

        if ($repeat != 0) {
                $res['ret'] = 1;
                $res['msg'] = "该果树种类名称或者编号已经存在";
                goto END;
        }
        $file_info = isset($params['singlePic'])?$params['singlePic']:null; 
        if ($file_info) {
                $result = UploadTools::upload_file("irrigation", $file_info);
                if ($result['ret'] != 0) {
                        $res['ret'] = -2;
                        $res['msg'] = $result['msg'];
                        goto END;
                }

                $sqlData['pic_path'] = $result['fileurl'];
                $params['catagory']['pic_path'] = $result['fileurl'];
        }

        DB::table('tree_catagory')->where('id',$params['catagory']['id'])->update($sqlData);

        $params['catagory']['is_delete'] = 1;
        $res['data'] = $params['catagory'];
    END:
        return Response::json($res);  
    }


    public function tree_catagory_remove()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $is_delete = 1;
        if($params['catagory']['is_delete'] == 1){
            $is_delete = 2;
        }
        DB::table('tree_catagory')->where('id',$params['catagory']['id'])->update(array('is_delete'=>$is_delete));
        $params['catagory']['is_delete'] = $is_delete;
        $res['data'] = $params['catagory'];
    END:
        return Response::json($res);  
    }








    public function tree_base()
    {
        $params = $this->getAngularjsParam(true);

        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $search = $params['search'];
        $tabs = DB::table('tree_base')
                ->where(function($query) use($search) {
                    if(isset($search['is_delete'])){
                        $query->where('is_delete', $search['is_delete']);
                    }
                })
                ->where(function($query) use($search) {
                    if(isset($search['name'])){
                        $query->where('name', 'like','%'.$search['name'].'%');
                    }
                })
                ->orderBy('sort','asc')
                ->orderBy('id','desc')
                ->select(['*'])
                ->paginate($params['search']["itemsPerPage"])
                ->toArray();
        foreach ($tabs['data'] as $key => $value) {
            $tabs['data'][$key]->plant_picture_list = DB::table('tree_base_img')
                                                            ->where('is_delete',0)
                                                            ->where('base_id',$value->id)
                                                            ->where('type','plant')
                                                            ->get(['pic_path','id'])->toArray();
            $tabs['data'][$key]->content_picture_list = DB::table('tree_base_img')
                                                            ->where('is_delete',0)
                                                            ->where('base_id',$value->id)
                                                            ->where('type','info')
                                                            ->get(['pic_path','id'])->toArray();
        }
        $res['data'] = $tabs;
    END:
        return Response::json($res);  
    }

    public function get_tree_base_one()
    {
        $params = $this->getAngularjsParam(true);
       //dd($params);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $tabs = DB::table('tree_base')
                ->where('id',$params['base_id'])
                ->get(['*']);
        foreach ($tabs as $key => $value) {
            $tabs[$key]->plant_picture_list = json_decode($value->plant_picture,true);
            $tabs[$key]->content_picture_list = json_decode($value->content_picture,true);
        }
        $res['data'] = $tabs;
    END:
        return Response::json($res);  
    }


    public function tree_base_add()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $sqlData['name'] = $params['base']['name'];
        $sqlData['scale_w'] = $params['base']['scale_w'];
        $sqlData['scale_h'] = $params['base']['scale_h'];
        $sqlData['title'] = $params['base']['title'];
        $sqlData['summary'] = $params['base']['summary'];
        $sqlData['content'] = $params['base']['content'];

        $repeat = DB::table('tree_base')
                    ->where('name',$sqlData['name'])
                    ->count();

        if ($repeat!= 0) {
                $res['ret'] = 1;
                $res['msg'] = "该果树基地名称存在";
                goto END;
        }


        $file_info1 = isset($params['singlePic_logo'])?$params['singlePic_logo']:null; 
        if ($file_info1) {
                $result = UploadTools::upload_file("tree", $file_info1);
                if ($result['ret'] != 0) {
                        $res['ret'] = -2;
                        $res['msg'] = $result['msg'];
                        goto END;
                }

                $sqlData['base_logo'] = $result['fileurl'];
        }else{
            $res['ret'] = 1;
            $res['msg'] = '请上传基地logo！'; 
            goto END;
        } 

        $file_info2 = isset($params['singlePic_full'])?$params['singlePic_full']:null; 
        if ($file_info2) {
                $result = UploadTools::upload_file("tree", $file_info2);
                if ($result['ret'] != 0) {
                        $res['ret'] = -2;
                        $res['msg'] = $result['msg'];
                        goto END;
                }
                $sqlData['full_picture'] = $result['fileurl'];
        }else{
            $res['ret'] = 1;
            $res['msg'] = '请上传基地全貌图片！'; 
            goto END;
        } 


        // $file_info_array1 = isset($params['multiplePic_info'])?$params['multiplePic_info']:null;
        // if ($file_info_array1) {
        //     $img_array = array();
        //     for ($i=0; $i < count($file_info_array1) ; $i++) {
        //         $file_info = array();
        //         $file_info["data"] = $file_info_array1[$i]['data'];
        //         $file_info["file_name"] = $file_info_array1[$i]['file_name'];
        //         $file_info["file_size"] = $file_info_array1[$i]['file_size'];
        //         $result = UploadTools::upload_file("tree", $file_info);
        //         if ($result['ret'] != 0) {
        //                 $res['ret'] = -2;
        //                 $res['msg'] = $result['msg'];
        //                 goto END;
        //         }
        //         array_push($img_array, $result['fileurl']);
        //     }
        //     $sqlData['content_picture'] = json_encode($img_array);  
        // } else{
        //         $res['ret'] = -1;
        //         $res['msg'] = '请上传基地详情图片~';
        //         goto END;
        // }

        // $file_info_array2 = isset($params['multiplePic_plant'])?$params['multiplePic_plant']:null;
        // if ($file_info_array2) {
        //     $img_array = array();
        //     for ($i=0; $i < count($file_info_array2) ; $i++) {
        //         $file_info = array();
        //         $file_info["data"] = $file_info_array2[$i]['data'];
        //         $file_info["file_name"] = $file_info_array2[$i]['file_name'];
        //         $file_info["file_size"] = $file_info_array2[$i]['file_size'];
        //         $result = UploadTools::upload_file("tree", $file_info);
        //         if ($result['ret'] != 0) {
        //                 $res['ret'] = -2;
        //                 $res['msg'] = $result['msg'];
        //                 goto END;
        //         }
        //         array_push($img_array, $result['fileurl']);
        //     }
        //     $sqlData['plant_picture'] = json_encode($img_array);  
        // } else{
        //         $res['ret'] = -1;
        //         $res['msg'] = '请上传种植状况图片~';
        //         goto END;
        // }



        $params['base']['id'] = DB::table('tree_base')->insertGetId($sqlData);
        $params['base']['base_logo'] = $sqlData['base_logo'];
        $params['base']['full_picture'] = $sqlData['full_picture'];
        $params['base']['is_delete'] = 1;
        $res['data'] = $params['base'];
    END:
        return Response::json($res);  
    }


    public function tree_base_edit()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $sqlData['name'] = $params['base']['name'];
        $sqlData['scale_w'] = $params['base']['scale_w'];
        $sqlData['scale_h'] = $params['base']['scale_h'];
        $sqlData['title'] = $params['base']['title'];
        $sqlData['summary'] = $params['base']['summary'];
        $sqlData['content'] = $params['base']['content'];

        $repeat = DB::table('tree_base')
                    ->where('id','<>',$params['base']['id'])
                    ->where('name',$sqlData['name'])
                    ->count();

        if ($repeat != 0) {
                $res['ret'] = 1;
                $res['msg'] = "该果树基地名称已经存在";
                goto END;
        }

        $file_info1 = isset($params['singlePic_logo'])?$params['singlePic_logo']:null; 
        if ($file_info1) {
                $result = UploadTools::upload_file("tree", $file_info1);
                if ($result['ret'] != 0) {
                        $res['ret'] = -2;
                        $res['msg'] = $result['msg'];
                        goto END;
                }

                $sqlData['base_logo'] = $result['fileurl'];
                $params['base']['base_logo'] = $result['fileurl'];
        }

        $file_info2 = isset($params['singlePic_full'])?$params['singlePic_full']:null; 
        if ($file_info2) {
                $result = UploadTools::upload_file("tree", $file_info2);
                if ($result['ret'] != 0) {
                        $res['ret'] = -2;
                        $res['msg'] = $result['msg'];
                        goto END;
                }
                $sqlData['full_picture'] = $result['fileurl'];
                $params['base']['full_picture'] = $result['fileurl'];
        }


        // $file_info_array1 = isset($params['multiplePic_info'])?$params['multiplePic_info']:null;
        // if ($file_info_array1) {
        //     $img_array = array();
        //     for ($i=0; $i < count($file_info_array1) ; $i++) {
        //         $file_info = array();
        //         $file_info["data"] = $file_info_array1[$i]['data'];
        //         $file_info["file_name"] = $file_info_array1[$i]['file_name'];
        //         $file_info["file_size"] = $file_info_array1[$i]['file_size'];
        //         $result = UploadTools::upload_file("tree", $file_info);
        //         if ($result['ret'] != 0) {
        //                 $res['ret'] = -2;
        //                 $res['msg'] = $result['msg'];
        //                 goto END;
        //         }
        //         array_push($img_array, $result['fileurl']);
        //     }
        //     $sqlData['content_picture'] = json_encode($img_array);  
        // }

        // $file_info_array2 = isset($params['multiplePic_plant'])?$params['multiplePic_plant']:null;
        // if ($file_info_array2) {
        //     $img_array = array();
        //     for ($i=0; $i < count($file_info_array2) ; $i++) {
        //         $file_info = array();
        //         $file_info["data"] = $file_info_array2[$i]['data'];
        //         $file_info["file_name"] = $file_info_array2[$i]['file_name'];
        //         $file_info["file_size"] = $file_info_array2[$i]['file_size'];
        //         $result = UploadTools::upload_file("tree", $file_info);
        //         if ($result['ret'] != 0) {
        //                 $res['ret'] = -2;
        //                 $res['msg'] = $result['msg'];
        //                 goto END;
        //         }
        //         array_push($img_array, $result['fileurl']);
        //     }
        //     $sqlData['plant_picture'] = json_encode($img_array);  
        // }
        
        
        DB::table('tree_base')->where('id',$params['base']['id'])->update($sqlData);
        $res['data'] = $params['base'];
    END:
        return Response::json($res);  
    }


    public function tree_base_remove()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = '操作成功';
        $is_delete = 1;
        if($params['base']['is_delete'] == 1){
            $is_delete = 2;
        }
        DB::table('tree_base')->where('id',$params['base']['id'])->update(array('is_delete'=>$is_delete));
        $params['base']['is_delete'] = $is_delete;
        $res['data'] = $params['base'];
    END:
        return Response::json($res);  
    }


    public function tree_base_img_add()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        
        $sqlData = array();
        $sqlData['base_id'] = $params['base']['id'];
        $sqlData['type'] = $params['type'];
        $file_info_array1 = isset($params['multiplePic_plant'])?$params['multiplePic_plant']:null;
        if ($file_info_array1) {
            $img_array = array();
            for ($i=0; $i < count($file_info_array1) ; $i++) {
                $file_info = array();
                $file_info["data"] = $file_info_array1[$i]['data'];
                $file_info["file_name"] = $file_info_array1[$i]['file_name'];
                $file_info["file_size"] = $file_info_array1[$i]['file_size'];
                $result = UploadTools::upload_file("tree", $file_info);
                if ($result['ret'] != 0) {
                        $res['ret'] = -2;
                        $res['msg'] = $result['msg'];
                        goto END;
                }

                array_push($img_array, $result['fileurl']);
            }
        }else{
            $res['ret'] = -1;
            $res['msg'] = '请上传图片~';
            goto END;
        }

        $imglist = array();
        foreach ($img_array as $key => $value) {
            $sqlData['pic_path'] = $value;
            $imgid = DB::table('tree_base_img')->insertGetId($sqlData);
            array_push($imglist,array('id'=>$imgid,'pic_path'=>$value));
        }

        $sqlData['img_array'] = $imglist;
        $res['data'] = $sqlData;
    END:
        return Response::json($res);  
    }

    public function tree_base_img_remove()
    {
        $params = $this->getAngularjsParam(true);
        $img = $params['img'];
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        DB::table('tree_base_img')->where('id',$img['id'])->update(array('is_delete'=>1));
        $res['data'] = $img;
    END:
        return Response::json($res); 
    }

    public function tree_base_sort()
    {
        $params = $this->getAngularjsParam(true);
        $base = $params['base'];
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        DB::table('tree_base')->where('id',$base['id'])->update(array('sort'=>$base['sort']));
        $res['data'] = $base;
    END:
        return Response::json($res); 
    }


    public function tree_cycle()
    {
        $params = $this->getAngularjsParam(true);

        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $search = $params['search'];
        $tabs = DB::table('tree_cycle as cycle')
                ->leftJoin('tree_catagory as catagory','catagory.id','cycle.catagory_id')
                ->leftJoin('tree_base as base','base.id','cycle.base_id')
                ->where(function($query) use($search) {
                    if(isset($search['base_id'])){
                        $query->where('cycle.base_id', $search['base_id']);
                    }  
                })
                ->where(function($query) use($search) {
                    if(isset($search['catagory_id'])){
                        $query->where('cycle.catagory_id', $search['catagory_id']);
                    }  
                })
                ->orderBy('cycle.id','desc')
                ->select(['cycle.*','base.name as base_name','catagory.name as catagory_name'])
                ->paginate($search["itemsPerPage"])
                ->toArray();
        $res['data'] = $tabs;
    END:
        return Response::json($res);  
    }




    public function tree_cycle_add()
    {
        $params = $this->getAngularjsParam(true);

        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $time_arr = $params['cycleinfo']['time'];
        $sqlData['base_id'] = $params['cycleinfo']['base_id'];
        $sqlData['catagory_id'] = $params['cycleinfo']['catagory_id'];
        $sqlData['starttime'] = $time_arr['startDate'];
        $sqlData['endtime'] = $time_arr['endDate'];

        $repeat = DB::table('tree_cycle')
                    ->where('base_id',$sqlData['base_id'])
                    ->where('catagory_id',$sqlData['catagory_id'])
                    ->where('catagory_id',$sqlData['catagory_id'])
                    ->count();

        if ($repeat!= 0) {
                $res['ret'] = 1;
                $res['msg'] = "该生长周期存在";
                goto END;
        }

         $params['cycleinfo']['id'] = DB::table('tree_cycle')->insertGetId($sqlData);


         $params['cycleinfo']['starttime'] = $sqlData['starttime'];
         $params['cycleinfo']['endtime'] = $sqlData['endtime'];
         $params['cycleinfo']['base_name'] = $params['cycleinfo']['base_name'];
         $params['cycleinfo']['catagory_name'] = $params['cycleinfo']['catagory_name'];

         $res['data'] = $params['cycleinfo'];
    END:
        return Response::json($res);  
    }


    public function tree_cycle_edit()
    {
        $params = $this->getAngularjsParam(true);

        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $time_arr = $params['cycleinfo']['time'];
        $sqlData['base_id'] = $params['cycleinfo']['base_id'];
        $sqlData['catagory_id'] = $params['cycleinfo']['catagory_id'];
        $sqlData['starttime'] = $time_arr['startDate'];
        $sqlData['endtime'] = $time_arr['endDate'];

        $repeat = DB::table('tree_cycle')
                    ->where('id','!=',$params['cycleinfo']['id'])
                    ->where('base_id',$sqlData['base_id'])
                    ->where('catagory_id',$sqlData['catagory_id'])
                    ->where('catagory_id',$sqlData['catagory_id'])
                    ->count();

        if ($repeat!= 0) {
                $res['ret'] = 1;
                $res['msg'] = "该生长周期存在";
                goto END;
        }

         DB::table('tree_cycle')->where('id',$params['cycleinfo']['id'])->update($sqlData);

         $params['cycleinfo']['starttime'] = $sqlData['starttime'];
         $params['cycleinfo']['endtime'] = $sqlData['endtime'];

         $res['data'] = $params['cycleinfo'];
    END:
        return Response::json($res);  
    }


    public function tree_cycle_remove()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $is_delete = 1;
        if($params['catagory']['is_delete'] == 1){
            $is_delete = 2;
        }
        DB::table('tree_catagory')->where('id',$params['catagory']['id'])->update(array('is_delete'=>$is_delete));
        $params['catagory']['is_delete'] = $is_delete;
        $res['data'] = $params['catagory'];
    END:
        return Response::json($res);  
    }






    public function tree_list()
    {
        $params = $this->getAngularjsParam(true);

        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $search = $params['search'];
        $tabs = DB::table('tree_list as list')
                ->leftJoin('tree_base as base','base.id','=','list.base_id')
                ->leftJoin('tree_catagory as catagory','catagory.id','=','list.catagory_id')
                ->where(function($query) use($search) {
                    if(isset($search['is_delete'])){
                        $query->where('list.is_delete', $search['is_delete']);
                    }  
                })
                ->where(function($query) use($search) {
                    if(isset($search['status'])){
                        $query->where('list.status', $search['status']);
                    }  
                })
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
                ->orderBy('list.id','desc')
                ->select(['list.*','base.name as base_name','catagory.name as catagory_name'])
                ->paginate($search["itemsPerPage"])
                ->toArray();
        foreach ($tabs['data'] as $key => $value) {
            $tabs['data'][$key]->curing_proportion = json_decode($value->curing_proportion,true);
        }
        $res['data'] = $tabs;
    END:
        return Response::json($res);  
    }



    public function tree_list_remove()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = '操作成功';
        $is_delete = 1;
        if($params['tree']['is_delete'] == 1){
            $is_delete = 2;
        }
        DB::table('tree_list')->where('id',$params['tree']['id'])->update(array('is_delete'=>$is_delete));
        $params['tree']['is_delete'] = $is_delete;
        $res['data'] = $params['tree'];
    END:
        return Response::json($res);  
    }


    public function tree_scale_list()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $base_tabs = DB::table('tree_base')->where('is_delete',1)->get(['*']);

        $data = array();
        foreach ($base_tabs as $key => $value) {
            if(!isset($base_tabs[$key]->scale_w_list)){
                $base_tabs[$key]->scale_w_list = array();
            }
            if(!isset($base_tabs[$key]->scale_h_list)){
                $base_tabs[$key]->scale_h_list = array();
            }
            $scale_list = array();

            for ($i=1; $i <= $value->scale_w; $i++) { 
                array_push($scale_list,array('id'=>$i,'num'=>$i,'child'=>array()));
                for ($j=1; $j <= $value->scale_h; $j++) { 
                    array_push($scale_list[$i-1]['child'],array('id'=>$j,'num'=>$j));
                }
            }

            $base_tabs[$key]->scale_list = $scale_list;
        }


        $tree_tabs = DB::table('tree_list as list')
                    ->leftJoin('tree_cycle as cycle', function ($join) {
                        $join->on('cycle.base_id', '=', 'list.base_id')
                        ->on('cycle.catagory_id', '=', 'list.catagory_id')
                        ->on('list.creattime','>=','cycle.starttime')
                        ->on('list.creattime','<=','cycle.endtime');
                    })
                    ->whereNotNull('cycle.starttime')
                    ->where('list.is_delete',1)->where('list.status',1)
                    ->get(['list.*','cycle.starttime','cycle.endtime']);
        foreach ($base_tabs as $key => $value) {
            foreach ($tree_tabs as $k => $val) {
                if($val->base_id == $value->id){
                    foreach ($value->scale_list as $scale_k => $scale_val) {
                        
                        if($params['type'] == 'add'){
                            if($val->scale_w == $scale_val['num']){
                                unset($base_tabs[$key]->scale_list[$val->scale_w-1]['child'][$val->scale_h-1]);
                                
                            }
                        }else{
                            if($val->scale_w == $scale_val['num'] && $val->scale_h != $params['treelist']['scale_h']){
                                unset($base_tabs[$key]->scale_list[$val->scale_w-1]['child'][$val->scale_h-1]);
                                $base_tabs[$key]->scale_list[$val->scale_w-1]['child'] = array_values($base_tabs[$key]->scale_list[$val->scale_w-1]['child']);
                            }
                        }
                        
                    }
                }
                
            }
        }
        $res['data'] = $base_tabs;
    END:
        return Response::json($res);  
    }

    public function tree_list_add()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $sqlData['catagory_id'] = $params['treelist']['catagory_id'];
        $sqlData['base_id'] = $params['treelist']['base_id'];
        $sqlData['scale_h'] = $params['treelist']['scale_h'];
        $sqlData['scale_w'] = $params['treelist']['scale_w'];
        $sqlData['tree_weight'] = $params['treelist']['tree_weight'];
        $sqlData['price'] = $params['treelist']['price'];
        $sqlData['curing_proportion'] = json_encode($params['irrigation']);

        $repeat = DB::table('tree_list')
                    ->where('base_id',$sqlData['base_id'])
                    ->where('scale_h',$sqlData['scale_h'])
                    ->where('scale_w',$sqlData['scale_w'])
                    ->count();

        if ($repeat!= 0) {
                $res['ret'] = 1;
                $res['msg'] = "该位置已经有果树了！";
                goto END;
        }

        $cycle_tabs = DB::table('tree_cycle')->where('base_id',$sqlData['base_id'])->where('catagory_id',$sqlData['catagory_id'])->get(['*']);
        $now = date('Y-m-d');
        if($now > $cycle_tabs[0]->endtime || $now < $cycle_tabs[0]->starttime){
            $res['ret'] = 1;
                $res['msg'] = "生长周期有误！";
                goto END;
        }
        

         $params['treelist']['id'] = DB::table('tree_list')->insertGetId($sqlData);
         $params['treelist']['curing_proportion'] = $params['irrigation'];
         $params['treelist']['is_delete'] = 1;
         $params['treelist']['status'] = 1;
         $params['treelist']['catagory_name'] = DB::table('tree_catagory')->where('id',$sqlData['catagory_id'])->get(['name as catagory_name'])[0]->catagory_name;
         $params['treelist']['base_name'] = DB::table('tree_base')->where('id',$sqlData['base_id'])->get(['name as base_name'])[0]->base_name;

         //生成二维码
         $filepath = '/admin/uploads/erweima/'.date('Y-m');
         if(!file_exists(public_path($filepath))){
            mkdir(public_path($filepath));
         }
         QrCode::format('png')->size(200)->generate('http://www.baidu.com?treeid='.$params['treelist']['id'],public_path($filepath.'/'.$params['treelist']['id'].'.png'));
         $params['treelist']['erweima'] = $filepath.'/'.$params['treelist']['id'].'.png';
         DB::table('tree_list')->where('id',$params['treelist']['id'])->update(array('erweima'=>$params['treelist']['erweima']));
         $res['data'] = $params['treelist'];
    END:
        return Response::json($res);  
    }



    public function tree_list_edit()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $sqlData = array();
        $sqlData['catagory_id'] = $params['treelist']['catagory_id'];
        $sqlData['base_id'] = $params['treelist']['base_id'];
        $sqlData['scale_h'] = $params['treelist']['scale_h'];
        $sqlData['scale_w'] = $params['treelist']['scale_w'];
        $sqlData['tree_weight'] = $params['treelist']['tree_weight'];
        $sqlData['price'] = $params['treelist']['price'];
        $sqlData['curing_proportion'] = json_encode($params['irrigation']);

        $repeat = DB::table('tree_list')
                    ->where('id','!=',$params['treelist']['id'])
                    ->where('base_id',$sqlData['base_id'])
                    ->where('scale_h',$sqlData['scale_h'])
                    ->where('scale_w',$sqlData['scale_w'])
                    ->count();

        if ($repeat!= 0) {
                $res['ret'] = 1;
                $res['msg'] = "该位置已经有果树了！";
                goto END;
        }
        

         DB::table('tree_list')->where('id',$params['treelist']['id'])->update($sqlData);
         $params['treelist']['curing_proportion'] = $params['irrigation'];
         $params['treelist']['is_delete'] = 1;
         $params['treelist']['status'] = 1;
         $params['treelist']['catagory_name'] = DB::table('tree_catagory')->where('id',$sqlData['catagory_id'])->get(['name as catagory_name'])[0]->catagory_name;
         $params['treelist']['base_name'] = DB::table('tree_base')->where('id',$sqlData['base_id'])->get(['name as base_name'])[0]->base_name;
         $res['data'] = $params['treelist'];
    END:
        return Response::json($res);  
    }


    public function down_csv()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $filename = "tree.csv";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename);
        header('Cache-Control: max-age=0');
        //直接输出到浏览器
        $fp = fopen('php://output', 'a');
        //在写入的第一个字符串开头加 bom。
        $bom =  chr(0xEF).chr(0xBB).chr(0xBF);


        // 创建并打开“tree.csv”文件进行写入
        $file = fopen($filename, 'w');
        // 保存列标题
        $irrigation_list = $this->irrigation_list();
        $header = array($bom.'果树种类','基地名称','横排','纵排','总价','重量');
        foreach ($irrigation_list as $key => $value) {
            array_push($header,$value->name);
        }
        fputcsv($file, $header);
        // 样本数据，这可以从MySQL中获取
        // $data = array(
        //     array('Data 11', 'Data 12', 'Data 13', 'Data 14', 'Data 15'),
        //     array('Data 21', 'Data 22', 'Data 23', 'Data 24', 'Data 25'),
        //     array('Data 31', 'Data 32', 'Data 33', 'Data 34', 'Data 35'),
        //     array('Data 41', 'Data 42', 'Data 43', 'Data 44', 'Data 45'),
        //     array('Data 51', 'Data 52', 'Data 53', 'Data 54', 'Data 55')
        // );
        // // 保存每一行数据
        // foreach ($data as $row)
        // {
        //     fputcsv($file, $row);
        // }
        // 关闭文件
        fclose($file);
        $res['data'] = "/".$filename;
    END:
        return Response::json($res); 
    }


    public function uploadcsv()
    {
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $res['err_data'] = array();
        $file_info = isset($params['singlePic'])?$params['singlePic']:null; 
        if ($file_info) {
                $result = UploadTools::upload_file("csv", $file_info);
                if ($result['ret'] != 0) {
                        $res['ret'] = -2;
                        $res['msg'] = $result['msg'];
                        goto END;
                }
                $result = $this->mysql_csv($result['fileurl']);
                if($result['ret'] != 0){
                    $res['ret'] = 1;
                    $res['msg'] = $result['msg']; 
                    $res['err_data'] = $result['err_data']; 
                    goto END;
                }
        }else{
            $res['ret'] = 1;
            $res['msg'] = '请上传模板文件'; 
            goto END;
        } 
    END:
        return Response::json($res); 
    }

    public function mysql_csv($filename)
    {
        $err_data = array();
        $params = $this->getAngularjsParam(true);
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        $res['err_data'] =$err_data;

        $file = fopen(getenv('APP_URL').$filename, "r");
        while (!feof($file)) {
            $data[] = fgetcsv($file);
        }
        $data = eval('return ' . iconv('gbk', 'utf-8', var_export($data, true)) . ';');

        $irrigation_list = $this->irrigation_list();
        $tree_catagory_list_id = $this->tree_catagory_list_id();
        $tree_base_list_id = $this->tree_base_list_id();
        //先判断数据准确性
        
        foreach ($data as $key => $value) {
            $flag = true;
            if($key == 0){
                continue;
            }
            $sqlData = array();
            $sqlData['catagory_id'] = $tree_catagory_list_id[$value[0]];
            $sqlData['base_id'] = $tree_base_list_id[$value[1]];

            $cycle_tabs = DB::table('tree_cycle')->where('base_id',$sqlData['base_id'])->where('catagory_id',$sqlData['catagory_id'])->get(['*']);
            $now = date('Y-m-d');
            if($now > $cycle_tabs[0]->endtime || $now < $cycle_tabs[0]->starttime){
                $flag= false;
            }

            $sqlData['scale_w'] = $value[2];
            $sqlData['scale_h'] = $value[3];
            $sqlData['price'] = $value[4];
            $sqlData['tree_weight'] = $value[5];
            $curing_proportion = array();
            $i = 6;
            $num = 0;
            foreach ($irrigation_list as $k => $val) {
                $num += $value[$k+$i];
            }
            if($num != 100){
                $flag = false;
            }

            if(!$flag){
                array_push($err_data,$value[0].'-'.$value[1].'-横排'.$value[2].'-纵排'.$value[3]);
            }

        }
        if(count($err_data) > 0){
            $res['ret'] = -1;
            $res['msg'] = "数据有误";
            $res['err_data'] = $err_data;
            return $res;
        }
        foreach ($data as $key => $value) {
            if($key == 0){
                continue;
            }
            $sqlData = array();
            $sqlData['catagory_id'] = $tree_catagory_list_id[$value[0]];
            $sqlData['base_id'] = $tree_base_list_id[$value[1]];
            $sqlData['scale_w'] = $value[2];
            $sqlData['scale_h'] = $value[3];
            $sqlData['price'] = $value[4];
            $sqlData['tree_weight'] = $value[5];
            $curing_proportion = array();
            $i = 6;
            foreach ($irrigation_list as $k => $val) {
                array_push($curing_proportion,array('id'=>$val->id,'name'=>$val->name,'num'=>$value[$k+$i]));
            }
            $sqlData['curing_proportion'] = json_encode($curing_proportion);

            $id = DB::table('tree_list')->insertGetId($sqlData);
            $filepath = '/admin/uploads/erweima/'.date('Y-m');
            if(!file_exists(public_path($filepath))){
                mkdir(public_path($filepath));
            }
            QrCode::format('png')->size(200)->generate('http://www.baidu.com?treeid='.$id,public_path($filepath.'/'.$id.'.png'));
            DB::table('tree_list')->where('id',$id)->update(array('erweima'=>$filepath.'/'.$id.'.png'));
        }
        
        fclose($file);
        return $res; 
    }



}


