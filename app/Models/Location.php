<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $fillable = [
    'city', 'address', 'city_th', 'slug', 'loc_lat', 'loc_loc', 'content_eng', 'content_thai', 
    'banner','credit_source','credit_url','seo_title','seo_keywords','seo_description',
    'seo_thai_title','seo_thai_keywords','seo_thai_description'
    ];
    public static function store($data){
    	self::create([
            'city'      => $data['city'],       
    		'city_th' 		=> $data['city'],    	
            'address'   => $data['address'],   
    		'slug' 		=> $data['place_url'],   
            'loc_lat'   => $data['loc_lat'],          
            'loc_loc'   => $data['loc_lon'],         
            'content_eng'   => $data['content_eng'],         
            'content_thai'   => $data['content_thai'],           
            'banner'   => $data['photo'],
            'credit_source' => $data['credit_source'],
            'credit_url' => $data['credit_url'],  
            'seo_title' => $data['seo_title'],  
            'seo_keywords' => $data['seo_keywords'],              
            'seo_description' => $data['seo_description'],
            'seo_thai_title' => $data['seo_thai_title'],  
            'seo_thai_keywords' => $data['seo_thai_keywords'],              
            'seo_thai_description' => $data['seo_thai_description']     		
    	]);
    }
}
