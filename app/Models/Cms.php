<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cms extends Model
{
    protected $table = 'cms';
    protected $fillable = ['cms_type','slug','attached_with','title_eng','title_thai','subject_eng','subject_thai','content_eng','content_thai','status'];

    public static function store($data){
    	return self::create([
    		'cms_type'				=>	$data['cms_type'],	
    		'title_eng'				=>	$data['title_eng'],	
            'title_thai'             =>  $data['title_thai'], 
            'subject_eng'             =>  @$data['subject_eng'], 
            'subject_thai'             =>  @$data['subject_thai'], 
            'slug'                  =>  $data['slug'], 
            'attached_with'             =>  $data['attached_with'], 
            'content_eng'             =>  $data['content_eng'], 
            'content_thai'             =>  $data['content_thai'],            
            'status'             =>  $data['status']
    	])->id;
    }

}
