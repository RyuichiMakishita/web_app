<?php

class Controller_Check extends Controller{

	public function action_index(){
		echo '<pre>';
		echo Fuel::VERSION . "\n";
		echo setlocale(LC_ALL, '') . "\n";
		echo Date::forge()->format('mysql') . "\n";
		echo ini_get('default_charset') . "\n";
		echo '</pre>';

		$cd = '10001';
 		$result = Model_Employee::find_by_cd($cd);

 		foreach ($result->as_array() as $row){
 			echo $row['lname'] . "\n";
 			echo $row['fname'] . "\n";

 		}
 		echo count($result);



	}
}