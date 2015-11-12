<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Common{
		
	public function __construct()
    {   	
    	$this->CI =& get_instance();
		$this->CI->load->model('words_model','words');
		$this->CI->load->model('users_model','users');
		$this->CI->load->model('questions_model','questions');
		$this->CI->load->model('responses_model','responses');
    }
	

	public function getWordHistory($user_id){
		return json_encode($this->CI->words->get_words($user_id));
	}
	
	public function addQuestion($user_id,$question){
		$this->CI->questions->insert_question($user_id,$question);		
	}
	
	public function addResponse($user_id,$question,$response){
		$this->CI->responses->insert_response($user_id,$question,$response);
	}
	
	public function getQuestionHistory($user_id,$keyword='',$startDate='',$endDate=''){
		return json_encode($this->CI->questions->get_questions($user_id,$keyword,$startDate,$endDate));		
	}
	
	public function getResponseHistory($user_id,$keyword='',$startDate='',$endDate=''){		
		return json_encode($this->CI->responses->get_responses($user_id,$keyword,$startDate,$endDate));	
	}
	
	public function addUser($name,$email){
		
		if(!$this->CI->users->email_exists($email)){
			$this->CI->users->insert_user($name,$email);
		}
	}
	
	public function getWordCount($user_id){
		return $this->CI->words->get_word_count($user_id);
	}
	
	public function getWordLevel($user_id){
		$wordCount = $this->getWordCount($user_id);
		$level = "Dumb";
		
		if($wordCount > 10000){
			$level = "Genius";
		}elseif($wordCount > 5000){
			$level = "Smart";
		}elseif($wordCount > 2000){
			$level = "Below Average";
		}
		
		return $level;
	}
	
	public function getUserIdFromEmail($email){
		return $this->CI->users->get_id_from_email($email);
	}
}