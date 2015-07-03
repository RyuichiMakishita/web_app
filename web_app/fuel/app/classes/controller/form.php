<?php

use Fuel\Core\Validation;
use Fuel\Core\Log;
use Fuel\Core\Security;
use Email\EmailValidationFailedException;
use Email\EmailSendingFailedException;
use Fuel\Core\Input;
use Fuel\Core\Package;
use Email\Email;
class Controller_Form extends Controller_Template
{
	public function action_index(){
		$this->template->title = 'コンタクトフォームaabb';
		$this->template->content = View::forge('form/index');
		$this->template->footer = View::forge('form/footer');
	}
	public function forge_validation(){

		$val = Validation::forge();

		$val->add('name','名前')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('max_length',50);

		$val->add('email','メールアドレス')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('max_length',100)
			->add_rule('valid_email');

		$val->add('comment','コメント')
			->add_rule('required')
			->add_rule('max_length',400);

		return $val;
	}


	public function action_confirm(){
		$val = $this->forge_validation();
		Log::debug('バリデーション前');
		if($val->run()){
			Log::debug('バリデーションOK');
			$data['input'] = $val->validated();
			$this->template->title = 'コンタクトフォーム:確認';
			$this->template->content = View::forge('form/confirm',$data);
			$this->template->footer = View::forge('form/footer');
		}else{
			Log::debug('バリデーションNG');
			$this->template->title = 'コンタクトフォーム:エラー';
			$this->template->content = View::forge('form/index');
			$this->template->content->set_safe('html_error',$val->show_errors());
			$this->template->footer = View::forge('form/footer');
		}
	}

	public  function action_send(){

		//CSRF対策
		if(! Security::check_token()){
			throw new HttpInvalidInputException('ページの遷移が正しくありません');
		}

		$val = $this->forge_validation();
		if(! $val->run()){
			$this->template->title = 'コンタクトフォーム:エラー';
			$this->template->content = View::forge('form/index');
			$this->template->content->set_safe('html_error',$val->show_errors());
			$this->template->footer = View::forge('form/footer');
			return;
		}

		$post = $val->validated();
		$data = $this->build_mail($post);

		//メールの送信
		try {
			//$this->sendmail($data);

			Package::load('email');

			$email = Email::forge();
			$email->from($data['from'],$data['from_name']);
			$email->to($data['to'],$data['to_name']);
			$email->subject($data['subject']);
			$email->body($data['body']);

			$email->send();


			$this->template->title = 'コンタクトフォーム:送信完了';
			$this->template->content = View::forge('form/send');
			$this->template->footer = View::forge('form/footer');
			return;
		} catch (EmailValidationFailedException $e) {
			Log::error('メール検証エラー：'.$e->getmesseage(),__METHOD__);
			$html_error = '<p>メールアドレスに誤りがあります</p>';
		} catch (EmailSendingFailedException $e) {
			Log::error('メール送信エラー：'.$e->getmesseage(),__METHOD__);
			$html_error = '<p>メールを送信できませんでした</p>';
		}
		$this->template->title = 'コンタクトフォーム:送信エラー';
		$this->template->content = View::forge('form/index');
		$this->template->content->set_safe('html_error',$html_error);
		$this->template->footer = View::forge('form/footer');

	}

	public function build_mail($post){

		$data['from'] = $post['email'];
		$data['from_name'] = $post['name'];
		$data['to'] = 'info@example.jp';
		$data['to_name'] = 'てすとの';
		$data['subject'] = 'コンタクトフォーム';

		$ip = Input::ip();
		$agent = Input::user_agent();

		$data['body'] = <<< END
-------------------------------------------------
名前：{$post['name']}
メールアドレス：{$post['email']}
IPアドレス：$ip
ブラウザ：$agent
-------------------------------------------------
コメント：
{$post['comment']}
-------------------------------------------------
END;
		return $data;

	}

	public function sendmail($data){

		Package::load('email');

		$email = Email::forge();
		$email->from($data['from'],$data['from_name']);
		$email->to($data['to'],$data['to_name']);
		$email->subject($data['subject']);
		$email->body($data['body']);

		$email->send();
	}
}
