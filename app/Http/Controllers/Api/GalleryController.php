<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use \Eventviva\ImageResize;

class GalleryController extends Controller
{
    public static function saveImage(Request $request,$path_to_save,$input_field_name,$width=400,$height = 300)
    {  
        ini_set('memory_limit','256M');
     
        $image_name = $request->file($input_field_name)->getClientOriginalName(); 
        $image_extension = $request->file($input_field_name)->getClientOriginalExtension();
        $image_extension = strtolower($image_extension); 

        $rand = md5(microtime(true));
        $new_name = $rand.".".$image_extension;
        $original_image = $path_to_save.'full/'.strtolower($new_name);
        $request->file($input_field_name)
            ->move( $path_to_save.'full/', strtolower($new_name) );
        $origin_size = getimagesize( $original_image );
        $origin_width = $origin_size[0];
        $origin_height = $origin_size[1];
        // resize
        //echo $original_image; die;
        $image_resize = new ImageResize($original_image );
        
       
        /*if($width > 0 && $height > 0)
        {
            $image_resize->resize($width, $height); 
        }
        else if($width == 0 && $height > 0)
        {
            $image_resize->resizeToHeight($height);
        }
        else if($width > 0 && $height == 0) 
        {
            $image_resize->resizeToWidth($width);
        }*/
         //for mid image
        $image_resize->crop(800, 600);
        $image_resize->save( $path_to_save.'mid/' . $new_name );

        //for thumbnail
         $image_resize->crop($width, $height);
        $image_resize->save( $path_to_save.'thumb/' . $new_name );
        return $new_name;
    } 


    public static function saveSingleImage(Request $request,$path_to_save,$input_field_name,$width=400,$height = 300)
    {  
        ini_set('memory_limit','256M');
     
        $image_name = $request->file($input_field_name)[0]->getClientOriginalName(); 
        $image_extension = $request->file($input_field_name)[0]->getClientOriginalExtension();
        $image_extension = strtolower($image_extension); 

        $rand = md5(microtime(true));
        $new_name = $rand.".".$image_extension;
        $original_image = $path_to_save.'full/'.strtolower($new_name);
        $request->file($input_field_name)[0]
            ->move( $path_to_save.'full/', strtolower($new_name) );
        $origin_size = getimagesize( $original_image );
        $origin_width = $origin_size[0];
        $origin_height = $origin_size[1];
        // resize
        //echo $original_image; die;
        $image_resize = new ImageResize($original_image );
        
       
        /*if($width > 0 && $height > 0)
        {
            $image_resize->resize($width, $height); 
        }
        else if($width == 0 && $height > 0)
        {
            $image_resize->resizeToHeight($height);
        }
        else if($width > 0 && $height == 0) 
        {
            $image_resize->resizeToWidth($width);
        }*/
         //for mid image
        $image_resize->crop(800, 600);
        $image_resize->save( $path_to_save.'mid/' . $new_name );

        //for thumbnail
         $image_resize->crop($width, $height);
        $image_resize->save( $path_to_save.'thumb/' . $new_name );
        return $new_name;
    }  

    public static function saveImages($data,$width=400,$height = 300)
    {  
        ini_set('memory_limit','256M');

        $photos = $data['request']->image;

        $images = array();
       // var_dump($photos); die;
        foreach($photos as $photo){
            $image_name = $photo->getClientOriginalName(); 
            $image_extension = $photo->getClientOriginalExtension();
            $image_extension = strtolower($image_extension); 

            $rand = md5(microtime(true));
            $new_name = $rand.".".$image_extension;
            $original_image = $data['path_to_save'].'full/'.strtolower($new_name);
            $photo->move( $data['path_to_save'].'full/', strtolower($new_name) );
            $origin_size = getimagesize( $original_image );
            $origin_width = $origin_size[0];
            $origin_height = $origin_size[1];
            // resize
            $image_resize = new ImageResize($original_image );          
           
            /*if($width > 0 && $height > 0)
            {
                $image_resize->resize($width, $height); 
            }
            else if($width == 0 && $height > 0)
            {
                $image_resize->resizeToHeight($height);
            }
            else if($width > 0 && $height == 0) 
            {
                $image_resize->resizeToWidth($width);
            }*/ 

        $image_resize->crop(800, 600);
        $image_resize->save( $data['path_to_save'].'mid/' . $new_name );

        //for thumbnail
         $image_resize->crop($width, $height);
        $image_resize->save( $data['path_to_save'].'thumb/' . $new_name );

            $images[] = $new_name;
        }
       
        return $images;         
    } 
}
