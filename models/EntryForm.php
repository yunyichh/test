<?php
namespace app\models;
use yii\base\Model;

class EntryForm extends Model{
	public $name;
	public $password;
	public $email;
	const SCENARIOS_LOGIN = "login";
	const SCENARIOS_REGISTER = 'register';

	public function scenarios(){
		 $scenarios = parent::scenarios();
		 // $scenarios[SCENARIOS_LOGIN] = 
		 return $scenarios;

	}

	public function rules(){
		return [
            [['name','email'],'required','on'=>'login'],
			[['name','email','password'],'required','on'=>'register'],
			['email','email']
		];
	}



}

?>