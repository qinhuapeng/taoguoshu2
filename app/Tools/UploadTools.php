<?php
namespace App\Tools;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Log\Writer;


class UploadTools {

    public static function upload_file_wx($type, $file_info, $urlpath = null) {
        $data = isset($file_info) ? $file_info: null;
        $res['ret'] = 0;
        $res['msg'] = 'ok';
 
        if(!$data) {
            $res['ret'] = -1;
            $res['msg'] = '上传失败，没有文件数据';
            goto END;
        }


        
        $baseheadArr = explode(';base64,', $data);
        if (count($baseheadArr) != 2) {
            $res['ret'] = -1;
            $res['msg'] = '上传失败，数据编码不正确';
            goto END;
        }
        $basehead = $baseheadArr[0];
        preg_match('/^data:.*/', $basehead, $matches);
        if (count($matches) == 0) {
            $res['ret'] = -1;
            $res['msg'] = '上传失败，文件不识别';
            goto END;
        }
        $filetype = substr($matches[0], strlen('data:'), strlen($matches[0]) - strlen('data:'));
        if ($filetype !== false) {
            $filetypemap = explode('/', $filetype);
            if (count($filetypemap) >= 2) {
                $filetype = $filetypemap[1];
            }
        }
        $filedata = base64_decode(substr( $data, strlen($matches[0]) + strlen(';base64,') ));
        
        $res['filetype'] = $filetype;
        
        $basepath = public_path();
        if (empty($urlpath)) {
            $upload_dir = $type.'_upload';
            $urlpath = '/admin/uploads/'.$upload_dir.'/'.date('Y').'/'.date('m');
        }

        if(!is_dir($basepath.$urlpath)) {
            mkdir($basepath.$urlpath, 0755, true);
        }
        
        if (!empty($filedata)) {
            $filename = $type . '_' . time();

            $uploadPath = $basepath.$urlpath.'/'.$filename;
            if (PHP_OS == 'WINNT') {
                $uploadPath = iconv('UTF-8', 'GBK', $uploadPath);
            }
            if (file_put_contents($uploadPath, $filedata) === FALSE) {
                $res['ret'] = -2;
                $res['msg'] = '上传失败，文件写入出错';
                goto END;
            }
            
            if(!$file = fopen($uploadPath, "w")) {
                $res['ret'] = -2;
                $res['msg'] = '上传失败，文件创建失败';
                goto END;
            }
            if (fwrite($file, $filedata) === FALSE) {
                fclose($file);
                $res['ret'] = -2;
                $res['msg'] = '上传失败，文件写入出错';
                goto END;
            }
            fclose($file);
            
            $res['fileurl'] = $urlpath.'/'.$filename;
            
        } else {
            $res['ret'] = 1;
            $res['msg'] = '没有文件';
            goto END;
        }
        
    END:
        return $res;
    }




    public static function upload_file($type, $file_info, $urlpath = null) {
        $data = isset($file_info['data']) ? $file_info['data'] : null;
        $file_name = isset($file_info['file_name']) ? $file_info['file_name'] : null;
        $file_size = isset($file_info['file_size']) ? $file_info['file_size'] : null;
        $res['ret'] = 0;
        $res['msg'] = 'ok';
        
        if (empty($type)) {
            $res['ret'] = -1;
            $res['msg'] = '上传失败，类型错误';
            goto END;
        }
        if(!$data) {
            $res['ret'] = -1;
            $res['msg'] = '上传失败，没有文件数据';
            goto END;
        }

        if(empty($file_name)) {
            $res['ret'] = -1;
            $res['msg'] = '上传失败，文件名称为空';
            goto END;
        }
        
        $baseheadArr = explode(';base64,', $data);
        if (count($baseheadArr) != 2) {
            $res['ret'] = -1;
            $res['msg'] = '上传失败，数据编码不正确';
            goto END;
        }
        $basehead = $baseheadArr[0];
        preg_match('/^data:.*/', $basehead, $matches);
        if (count($matches) == 0) {
            $res['ret'] = -1;
            $res['msg'] = '上传失败，文件不识别';
            goto END;
        }
        $filetype = substr($matches[0], strlen('data:'), strlen($matches[0]) - strlen('data:'));
        if ($filetype !== false) {
            $filetypemap = explode('/', $filetype);
            if (count($filetypemap) >= 2) {
                $filetype = $filetypemap[1];
            }
        }
        $filedata = base64_decode(substr( $data, strlen($matches[0]) + strlen(';base64,') ));
        
        $res['filetype'] = $filetype;
        
        $basepath = public_path();
        if (empty($urlpath)) {
            $upload_dir = $type.'_upload';
            $urlpath = '/admin/uploads/'.$upload_dir.'/'.date('Y');
        }

        if(!is_dir($basepath.$urlpath)) {
            mkdir($basepath.$urlpath, 0755, true);
        }
        
        if (!empty($filedata)) {
            $filename = $type . '_' . self::microtime_float() . '_' . $file_name;

            $uploadPath = $basepath.$urlpath.'/'.$filename;
            if (PHP_OS == 'WINNT') {
                $uploadPath = iconv('UTF-8', 'GBK', $uploadPath);
            }
            if (file_put_contents($uploadPath, $filedata) === FALSE) {
                $res['ret'] = -2;
                $res['msg'] = '上传失败，文件写入出错';
                goto END;
            }
            /*
            if(!$file = fopen($uploadPath, "w")) {
                $res['ret'] = -2;
                $res['msg'] = '上传失败，文件创建失败';
                goto END;
            }
            if (fwrite($file, $filedata) === FALSE) {
                fclose($file);
                $res['ret'] = -2;
                $res['msg'] = '上传失败，文件写入出错';
                goto END;
            }
            fclose($file);
            */
            $res['fileurl'] = $urlpath.'/'.$filename;
            
        } else {
            $res['ret'] = 1;
            $res['msg'] = '没有文件';
            goto END;
        }
        
    END:
        return $res;
    }


    public static function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
        

}



?>
