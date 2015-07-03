<?php

//不正な入力を処理するためのクラス
class HttpInvalidInputException extends HttpException{
	public function response(){
		$response = Request::forge('error/invalid')->execute(array($this->getmessage()))->response();
		return $response;
	}
}
