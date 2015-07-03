<?php

use Fuel\Core\Model;
class Model_Employee extends Model{

	public static function find_by_cd($cd){


		//$query = DB::query('select * from employee where empno = :cd');
		$query = DB::select()->from('employee')->where('empno','=',10001);
		$query->bind('cd', $cd);     // クエリに割り当てる。
		$result = $query->execute();	//sqlを実行する
		Log::debug('aaaaaaaaaaaaa');

		return $result;
	}


}
