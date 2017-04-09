<?php  
require_once "API.php";
require_once "config.php";

if(empty($_FILES))
{  
    echo "上传的文件为空";
	exit;  
}
$files = $_FILES[FILENAME]; 
$searchimg = new UploadSearch();
$searchimg->searchimg($files);

//根据上传的图片搜索符合条件的图片并显示出来	
class UploadSearch
{
	public function searchimg($files)
	{
 		$dest_filename = $this->predealImg($files);//上传的图片进行预检查
 		if(!$dest_filename)
 		{
 			echo "图片上传未成功，请重新上传";
 			return false;
 		}
	    $this->verifyimg($dest_filename);//判断上传的图片是否需要调整宽和高
		
		echo "<br>上传的图片为：<br>";    	
		echo "<img src='".$dest_filename."' /><br>";
		echo "<br>搜索出来的图片为：<br>";
		$product_ai = new API(ACCESS_KEY_ID, SECRET_KEY);		
		$result = $product_ai->searchImage(SERVICE_TYPE, SERVICE_ID, '@'.$dest_filename);//搜索符合条件的图片
		if(!empty($result))
		{
		    foreach($result['results'] as $val)
		    {
		    	echo "<img src='".$val['url']."' /><br>";
		    }
		}
		else
		{
			echo "<br>对不起，没有搜索到合适的图片！<br>";			
		}
	}
	//对上传的图片进行预处理
	public function predealImg($files)
	{
		if(!is_uploaded_file($files['tmp_name']))//验证上传文件是否存在  
		{
		    echo "请选择你想要上传的图片";  
	    	return false;  
		}        
	     
	    if(MAX_FILE_SIZE < $files['size'])      //判断文件是否超过限制大小
	    {  
	        echo "图片太大了,传个小点的吧(<=3MB)";  
	    	return false;  
	    }  
	    $destination_dir = 'pic/'.date("Ymd").'/';  
		$dest_filename = $destination_dir.$files['name'];      
	    if(!file_exists($destination_dir))       //判断上传目录是否存在,不存在则创建一个.
	    {  
	        if(!mkdir($destination_dir,0777,true)) {  
	            echo "创建目录 {".$destination_dir."} 失败<可能是权限问题>";  
	    		return false;  
	        }  
	    }      
	    if($this->isImage($files['name'],$files['type'])!==true) 
	    {
	    	echo "您上传的文件不是图片格式，请重新上传";  
	    	return false; 
	    }
 	 
	    if(!move_uploaded_file ($files['tmp_name'], $dest_filename)) //上传文件
	    {  
	        echo "上传文件失败";  
	    	return false;  
	    } 
	    return $dest_filename;
	}	
	
	public function isImage($filename,$contentType) 
	{
    	$arrfile = explode(".",$filename);
        $Extlist = ".BMP.GIF.JPEG.JPG.PNG";
        if (strripos($Extlist,$arrfile[count($arrfile)-1]))
        {
            return true;
        }
        else
        {
            $conType = " image/pjpeg image/gif image/bmp image/x-png image/tiff image/png image/jpeg";
            if (strripos($conType,$contentType))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    
    public function verifyimg($dest_filename) //检查图片长宽是否小于800px，如大于则进行等比例缩小，小于则不做任何处理
    {
    	list($width, $height) = getimagesize($dest_filename);
		if($width>800 || $width>800)
		{
			if($width>$height)
			{
				$per = 800.0/$width;
			}
			else
			{
				$per = 800.0/$height;
			}
			$n_w=$width*$per;
			$n_h=$height*$per;
			$new=imagecreatetruecolor($n_w, $n_h);
			$img=imagecreatefromjpeg($dest_filename);
			//拷贝部分图像并调整
			imagecopyresized($new, $img,0, 0,0, 0,$n_w, $n_h, $width, $height);
			//图像输出新图片、另存为
			imagejpeg($new, $dest_filename);
			imagedestroy($new);
			imagedestroy($img);
			return true;
		}
		return false;
    }
}
?> 