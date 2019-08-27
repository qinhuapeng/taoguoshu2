<?php
namespace App\Tools;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

class WaterMark {
/**
     * 图片加水印（适用于png/jpg/gif格式）
     *
     * @param $srcImg  原图片
     * @param $waterImg 水印图片
     * @param $savepath 保存路径
     * @param $savename 保存名字
     * @param $positon  水印位置：1:顶部居左, 2:顶部居右, 3:居中, 4:底部局左, 5:底部居右
     * @param $alpha   透明度：0:完全透明, 100:完全不透明
     *
     * @return 成功 -- 加水印后的新图片地址
     *   失败 -- -1:原文件不存在, -2:水印图片不存在, -3:原文件图像对象建立失败，-4:水印文件图像对象建立失败 -5:加水印后的新图片保存失败
     */
    
    public static function water_mark_y($srcImg, $waterImg, $savepath=null, $savename=null, $positon=1, $alpha=50) {
        	$temp = pathinfo($srcImg);
	        $name = $temp['basename'];
	        $path = $temp['dirname'];
	        $ext  = $temp['extension'];
	        $savename = $savename ? $savename : $name;
	        $savepath = $savepath ? $savepath : $path;
	        $savefile = $savepath .'/'. $savename;
			$bigImgPath = $srcImg; //原图
			$logo = $waterImg; //水印
			$im = imagecreatefromstring(file_get_contents($bigImgPath));
			//获取水印源
			$watermark = imagecreatefromstring(file_get_contents($logo));
			//获取图、水印 宽高类型
			list($bgWidth, $bgHight, $bgType) = getimagesize($bigImgPath);
			list($logoWidth, $logoHight, $logoType) = getimagesize($logo);
			//定义平铺数据
			$x_length = $bgWidth; //x轴总长度
			$y_length = $bgHight; //y轴总长度
			//创建透明画布 伪白色
			$opacity=$alpha;
			$w = imagesx($watermark);
			$h = imagesy($watermark);
			$cut = imagecreatetruecolor($w,$h);
			$white = imagecolorallocatealpha($cut, 0,0,0,0);
			imagefill( $cut, 0, 0, $white );
			//整合水印
			imagecopy($cut, $watermark, 0, 0, 0, 0, $w, $h);
			//循环平铺水印
			for ($x = 0; $x < $x_length; $x++)
			{
			    for ($y = 0; $y < $y_length; $y++) {
			        imagecopymerge($im, $cut, $x, $y, 0, 0, $logoWidth, $logoHight, $opacity);
			        imagecopymerge($srcImgObj, $waterImgObj, $x, $y, 0, 0, $srcImgInfo[0], $srcImgInfo[1], $alpha);
			        $y += $logoHight;
			    }
			    $x += $logoWidth;
			}
			//header("Content-type:image/png");
			// imagejpeg 的第二个参数不传, 默认是显示图片
			imagejpeg($im,$savefile);
	}


	public static function water_mark($srcImg, $waterImg, $savefile, $positon, $alpha) {
		//判断文件是否存在
        $srcImgInfo = @getimagesize($srcImg);
        if(!$srcImgInfo){
            return -1;
        }
        $waterImgInfo = getimagesize($waterImg);
        if(!$waterImgInfo){
            return -1;
        }

        //建立图像对象
        $srcImgObj = self::image_create_from_ext($srcImg, $srcImgInfo[2]);
        if(!$srcImgObj){
            return -3; //原文件图像对象建立失败
        }
        $waterImgObj = self::image_create_from_ext($waterImg, $waterImgInfo[2]);
        if(!$waterImgObj){
            return -4; //原文件图像对象建立失败
        }

        // $color=imagecolorallocate($waterImgObj,0,0,0); 
        // //3.设置透明 
        // imagecolortransparent($waterImgObj,$color); 
        // imagefill($waterImgObj,0,0,$color); 

        //确定生成水印的位置
        switch($positon){
            //1顶部居左
            case 1: 
                $x=$y=0; 
                break;
            //2顶部居右
            case 2: 
                $x = $srcImgInfo[0]-$waterImgInfo[0]; $y = 0; 
                break;
            //3居中
            case 3: 
                $x = ($srcImgInfo[0]-$waterImgInfo[0])/2; $y = ($srcImgInfo[1]-$waterImgInfo[1])/2; 
                break;
            //4底部居左
            case 4: 
                $x = 0; $y = $srcImgInfo[1]-$waterImgInfo[1]; 
                break;
            //5底部居右
            case 5: 
                $x = $srcImgInfo[0]-$waterImgInfo[0]; $y = $srcImgInfo[1]-$waterImgInfo[1]; 
                break;
             default: 
                $x=$y=0; 
        }


        imagecopymerge($srcImgObj, $waterImgObj, $x, $y, 0, 0, $waterImgInfo[0], $waterImgInfo[1], $alpha);

        
        //输出图片
        switch ($srcImgInfo[2]) {
            case 1: 
                imagegif($srcImgObj, $savefile); 
                break;
            case 2: 
                imagejpeg($srcImgObj, $savefile); 
                break;
            case 3: 
                imagepng($srcImgObj, $savefile); 
                break;
            default: 
                return -5; //保存失败
        }
        //销毁图像资源
        imagedestroy($srcImgObj);
        imagedestroy($waterImgObj);
        return $savefile;
	}


	public static function water_mark_pingpu($srcImg, $waterImg, $savefile, $positon=1, $alpha=50) {
		//判断文件是否存在
        $srcImgInfo = @getimagesize($srcImg);
        if(!$srcImgInfo){
            return -1;
        }
        $waterImgInfo = @getimagesize($waterImg);
        if(!$waterImgInfo){
            return -1;
        }

        //建立图像对象
        $srcImgObj = self::image_create_from_ext($srcImg, $srcImgInfo[2]);
        if(!$srcImgObj){
            return -3; //原文件图像对象建立失败
        }
        $waterImgObj = self::image_create_from_ext($waterImg, $waterImgInfo[2]);
        if(!$waterImgObj){
            return -4; //原文件图像对象建立失败
        }

        // $color=imagecolorallocate($waterImgObj,255,255,255); 
        // //3.设置透明 
        // imagecolortransparent($waterImgObj,$color); 
        // imagefill($waterImgObj,0,0,$color); 

        //准备信息：保存路径，保存文件名
        // $temp = pathinfo($srcImg);
        // $name = $temp['basename'];
        // $path = $temp['dirname'];
        // $ext  = $temp['extension'];
        // $savename = $savename ? $savename : $name;
        // $savepath = $savepath ? $savepath : $path;
        // $savefile = $savepath .'/'. $savename;

        //确定生成水印的位置
        switch($positon){
            //1顶部居左
            case 1: 
                $x=$y=0; 
                break;
            //2顶部居右
            case 2: 
                $x = $srcImgInfo[0]-$waterImgInfo[0]; $y = 0; 
                break;
            //3居中
            case 3: 
                $x = ($srcImgInfo[0]-$waterImgInfo[0])/2; $y = ($srcImgInfo[1]-$waterImgInfo[1])/2; 
                break;
            //4底部居左
            case 4: 
                $x = 0; $y = $srcImgInfo[1]-$waterImgInfo[1]; 
                break;
            //5底部居右
            case 5: 
                $x = $srcImgInfo[0]-$waterImgInfo[0]; $y = $srcImgInfo[1]-$waterImgInfo[1]; 
                break;
             default: 
                $x=$y=0; 
        }

        $x_length = $srcImgInfo[0]; //x轴总长度
        $y_length = $srcImgInfo[1]; //y轴总长度
        $logoWidth = $waterImgInfo[0];
        $logoHight =$waterImgInfo[1];

        for ($x = 0; $x < $x_length; $x++)
        {
            for ($y = 0; $y < $y_length; $y++) {
                //imagecopymerge($im, $cut, $x, $y, 0, 0, $logoWidth, $logoHight, $opacity);
                imagecopymerge($srcImgObj, $waterImgObj, $x, $y, 0, 0, $waterImgInfo[0], $waterImgInfo[1], $alpha);
                $y += $logoHight;
            }
            $x += $logoWidth;
        }
        //添加水印图片
        
        //输出图片
        switch ($srcImgInfo[2]) {
            case 1: 
                imagegif($srcImgObj, $savefile); 
                break;
            case 2: 
                imagejpeg($srcImgObj, $savefile); 
                break;
            case 3: 
                imagepng($srcImgObj, $savefile); 
                break;
            default: 
                return -5; //保存失败
        }
        //销毁图像资源
        imagedestroy($srcImgObj);
        imagedestroy($waterImgObj);
        return $savefile;
	}


	/*
    * 创建图像对象
    * @param $imgFile 图片路径
    * @param $imgExt  图片扩展名
    * @return $im 图像对象
    **/
    public static function image_create_from_ext($imgFile, $imgExt){
        $im = null;
        switch ($imgExt) {
            case 1: 
                $im=imagecreatefromgif($imgFile);
                break;
            case 2: 
                $im=imagecreatefromjpeg($imgFile);
                break;
            case 3: 
                $im=imagecreatefrompng($imgFile);
                break;
        }
        return $im;
    }
	
}

?>
