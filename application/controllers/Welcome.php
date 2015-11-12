<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->library('ai');
		$this->load->library('common');
		$this->load->library('template');
		$this->load->library('captcha');
		$this->load->library('session');
		$this->load->library('facebook');
		//$this->load->library('pdf');
		//$this->load->library('docx');
	}
	
	public function index()
	{
		$logged_in = false;

		if(!$this->session->user_id){
			
			$user_info = $this->facebook->getUserInfo();
			
			if(is_array($user_info) & isset($user_info['email'])){
				$logged_in = true;
				$name = $user_info['name'];
				$email = $user_info['email'];
				$logoutUrl = $user_info['logoutUrl'];
				$this->common->addUser($name,$email);
				$id = $this->common->getUserIdFromEmail($email);
				$this->session->set_userdata('user_id', $id);
				$this->session->set_userdata('user_name', $name);
				$this->session->set_userdata('user_email', $email);		
				$questionsHistory = $this->common->getQuestionHistory($id);
				$level = $this->common->getWordLevel($id);	
				
				$data = array('user_name' => $name,'logoutUrl' => $logoutUrl,'level'=>$level);				
				$this->template->show('','ai_home',$data);
				
	
			 }else{
			 	$user_info = $this->facebook->getUserInfo();
				$data = array('loginUrl' => $user_info);	
				$this->template->show('','home',$data);		    
			}
		
		}else{
			
			$name = $this->session->name;
			$logoutUrl = $this->session->logoutUrl;
			$id = $this->session->user_id;			 	
			$questionsHistory = $this->common->getQuestionHistory($id);
			$level = $this->common->getWordLevel($id);		
			$data = array('user_name' => $name,'logoutUrl' => $logoutUrl,'level'=>$level);				
			$this->template->show('','ai_home',$data);
		}
		 
		 
	}
	
	public function logout(){
		
		$this->session->unset_userdata('user_id');
		$this->session->unset_userdata('user_name');
		$this->session->unset_userdata('user_email');
		session_destroy();
		header('Location: /');
		
	}
	
	public function getQuestionHistory(){
			
		if(!$this->session->user_id){
			return false;
		}
		
		$keyword =  $this->input->post("keyword");
		$fromdate =  $this->input->post("fromdate");
		$todate =  $this->input->post("todate");
		
		$user_id = $this->session->user_id;
		$data = $this->common->getQuestionHistory($user_id,$keyword,$fromdate,$todate);
		echo $data;
	}
	
	
	public function getResponseHistory(){
			
		if(!$this->session->user_id){
			return false;
		}
		
		$keyword =  $this->input->post("keyword");
		$fromdate =  $this->input->post("fromdate");
		$todate =  $this->input->post("todate");
		
		$user_id = $this->session->user_id;
		$data = $this->common->getResponseHistory($user_id,$keyword,$fromdate,$todate);
		echo $data;
	}
	
	public function getWordHistory(){
			
		if(!$this->session->user_id){
			return false;
		}
		
		$user_id = $this->session->user_id;
		$data = $this->common->getWordHistory($user_id);
		echo $data;
	}
	
	
	
	public function response(){
		
		$query =  $this->input->post("query");
		$correct =  filter_var($this->input->post("correct"), FILTER_VALIDATE_BOOLEAN);
		$captcha =  $this->input->post("captcha");
		$user_id = $this->session->user_id;
		$this->common->addQuestion($user_id,$query);
		//Validate Captcha
		//$this->captcha->validate($captcha);
		
		if($correct){
			$this->ai->setCorrectionAsTrue();
		}
		
		if(isset($user_id)){
			$this->ai->setUserId($user_id);
		}else{
			return false;
		}
		
		$this->ai->setInput($query);
		$response = $this->ai->getResponse();
		$dumbResponse = "Teach me to respond to \"" .$query . "\"";

		if($response != $dumbResponse && $response != "No Text Entered" && trim($response) != ""){
			$this->common->addResponse($user_id,$query,$response);
		}
		
		echo $response;
	}
	
	public function captcha(){
		$this->captcha->load();
	}
	
	public function getWordsused(){
		
		
	}
	
	public function getQuestionsAsked(){
		
		
	}
	
	public function getResponses(){
		
		
	}
	
	public function think(){
		
		
	}
}