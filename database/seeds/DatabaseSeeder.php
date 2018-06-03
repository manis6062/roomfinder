<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
            	for ($i=0; $i < 5; $i++) { 

	    	DB::table('feedback')->insert([

	            'feedback' => str_random(100),
	        ]);

	    

    	}
    }
}
