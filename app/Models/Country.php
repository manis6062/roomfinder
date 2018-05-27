<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
   protected $table = 'countries';

   public function countryList(){
   		$country_list = $this->select('id','name')->orderBy('ordering','asc')->get();
   		return $country_list;
   }
   
}
