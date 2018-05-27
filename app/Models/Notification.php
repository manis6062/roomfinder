<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Library\CarRentFunctions; 

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = ['user_id','content_eng','content_chn','content_idn','content_lao','content_cam','content_bur','content_rus','content_mys','content_link','is_read','notification_type','invoker_user_id',
    'content_link','mobile_target','mobile_target_id'];
    
    public static function getTargetLink($notification){
    	
    	switch ($notification->mobile_target) {
    		case 'user_profile':
    			return url('/user/'.$notification->mobile_target_id);
    			break;    		
    		case 'my_rentals':
    			return url('/booking/my-rentals');
    			break;
    		case 'my_car_rentals':
    			return url('/booking/my-car-rentals');
    			break;
    		
    		case 'my_car_rentals_detail':
    			return url('/booking/my-car-rentals/'.$notification->mobile_target_id);
    			break;
    		case 'my_rentals_detail':
    			return url('/booking/my-rentals/'.$notification->mobile_target_id);
    			break;
    		
    		case 'my_car_list':
    			return url('/car/list');
    			break;
    	}
    }

    public static function createNotification($data){
        $notification_type = 'user';
        $email_slug = $data['slug'];
        if(isset($data['notification_type']) and $data['notification_type'] != 'user'){
            $notification_type = $data['notification_type'];
        }
        $content_eng = $content_thai = $content_chn = $content_idn = $content_lao = $content_cam = $content_bur = $content_rus = $content_mys = '';
        if($template = CarRentFunctions::getEmailTemplate($email_slug)){
            //dd($template);
            $variables = CarRentFunctions::getEmailTemplateVariables();
            foreach($data['replace_array'] as $key=>$val){
                if(in_array('{'.$key.'}',$variables)){
                    $search[] = '{'.$key.'}';
                    $replace[] = $val;  
                }
            }
        /* $variables = self::getEmailTemplateVariables();
        foreach($data['replace_array'] as $key=>$val){
            if(in_array('{'.$key.'}',$variables)){
                $search[] = '{'.$key.'}';
                $replace[] = $val;  
            }
        }*/
        /*$return_array['content'] = str_replace($search, $replace, $email_template->$content_col);
        $return_array['subject'] = str_replace($search, $replace, $email_template->$subject_col);
        $return_array['subject'] = str_replace($search, $replace, $email_template->$subject_col);*/
            $noti_id = Notification::create(
            [ 
                'user_id' => $data['user_id'],
                'content_eng' => str_replace($search,$replace,$template->subject_eng), 
                'content_thai' => str_replace($search,$replace,$template->subject_thai),
                'content_chn' => str_replace($search,$replace,$template->subject_chn),
                'content_idn' => str_replace($search,$replace,$template->subject_idn),
                'content_lao' => str_replace($search,$replace,$template->subject_lao),
                'content_cam' =>str_replace($search,$replace, $template->subject_cam),
                'content_bur' => str_replace($search,$replace,$template->subject_bur),
                'content_mys' => str_replace($search,$replace,$template->subject_mys),
                'content_rus' => str_replace($search,$replace,$template->subject_rus),  
                'content_link'=>@$data['content_link'], 
                'mobile_target' => @$data['mobile_target'],
                'mobile_target_id' => @$data['mobile_target_id'],
                'notification_type' => $notification_type,
                'invoker_user_id' => @$data['invoker_user_id'] 
            ])->id; 
            return $noti_id; 
        }else{
            return false;
        }
        

    }

}
