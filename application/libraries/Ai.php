<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai{
	
	private $text,$correct,$user_id,$state;
	
	public function __construct()
    {   	
    	$this->CI =& get_instance();
		$this->CI->load->library('common');
		$this->CI->load->model('connections_model','connections');
		$this->CI->load->model('current_input_model','current_input');
		$this->CI->load->model('current_output_model','current_output');
		$this->CI->load->model('words_model','words');
		$this->CI->load->model('word_flow_model','word_flow');
		$this->correct = false;
    }
	
	public function setInput($text){
		$this->text = $text;
	}
	
	public function setUserId($user_id){
		$this->user_id = $user_id;
	}
	
	public function setCorrectionAsTrue(){
		$this->correct = true;		
	}
	
	public function getResponse(){
		
		if(!$this->text){
			return "No Text Entered";
		}
		
		$words = array();
		$words = explode(" ",$this->text);

  		foreach($words as $index=>$word){

      		$wordId = $this->CI->words->get_word_id($this->user_id,$word);

	  		if(!$wordId){
		  		$this->CI->words->insert_word($this->user_id,$word);
	  		}
		}
		
		$this->wordConnect();

		if($this->correct){
			$input = $this->CI->current_output->get_current_output();
			$this->CI->current_output->truncate_current_output();
  		}else{
			$input = $this->CI->current_input->get_current_input();
			$this->CI->current_input->truncate_current_input();
  		}
		
		
		if(!$input){
			
	  		//traverse;

			$ids = $this->CI->words->get_ids($this->text);
			
			$response = $this->traverseX($ids,$this->text);

			if(!$response){
				$response = "Teach me to respond to \"" . $this->text . "\"";
			}else{

				$response = $this->orderIt($this->fetchWords($response));
				$this->CI->current_output->truncate_current_output();
				$this->CI->current_output->add_current_output($this->text);
			}
		
				return $this->removeConsecutiveRepeat($response);
  
  			}else{
  				
			  //connect nodes;
			 
			  foreach($words as $index=>$word){
				  $curr_id = $this->CI->words->get_word_id($this->user_id,$word);
				  $Iwords = explode(" ",$input);
					foreach($Iwords as $Iindex=>$Iword){
				       $IwordId = $this->CI->words->get_word_id($this->user_id,$Iword);
                       $this->connectNodes($IwordId,$curr_id);
					}  
		       }
	 
		 	$this->CI->current_output->truncate_current_output();
		 	echo "Ok";
		
  		}
		
	}

	
	private function removeConsecutiveRepeat($text){
		$words = explode(" ",$text);
		$size = sizeOf($words);
		
		for($i=0;$i<$size;$i++){
			$j = $i +1;
		
			if($j <= $size){
				
				if(strtolower($words[$i]) === strtolower($words[$j])){
					unset($words[$i]);
				}
			}
		}
		
		return implode(" ",$words);
	}
	
	private function wordConnect(){
		
		
		$words = explode(" ",$this->text);
		
		$size = sizeOf($words);
		$totalWords = $size;
		
		for($i=0;$i<$size;$i++){
			$j = $i +1;
			$from = $this->CI->words->get_word_id($this->user_id,$words[$i]);			
			
			if($i >= ($size -1)){
				continue;
			}
			
			$to = $this->CI->words->get_word_id($this->user_id,$words[$j]);
			$this->CI->word_flow->link_words($from,$to);
		}
	}
	
	private function orderIt($text){
		
		$words = explode(" ",$text);
		$size  = sizeOf($words);
		$result = $this->getOrder(0,$words,$words,array());
 		return implode(" ",$result);
		
	}
	
	private function getOrder($current,$array,$orig,$prev_arrays){
			
		$size  = sizeOf($array);
		$prev_match = false;
	
		for($i=$current;$i<$size;$i++){
			
			if($array[$current] == $array[$i]){
				continue;
			}
			
			if(!$this->getWordLink($array[$current],$array[$i])){
					
					$placeholder  = $array[$i];
					$array[$i] = $array[$current];
					$array[$current] = $placeholder;
					
					foreach ($prev_arrays as $prev_array){
						if($array === $prev_array){
							$prev_match = true;
						}
					}
					
					$disk_break = ($array === $orig)|$prev_match;

					if(!$disk_break){	
							$prev_arrays[] = $array;
							return $this->getOrder($current,$array,$orig,$prev_arrays);
					}
			}			
		}
		
		if($current < ($size-1)){
			return $this->getOrder($current+1,$array,$orig,array());
		}else{
			return $array;
		}
		
	}
	
	private function getWordLink($word1,$word2){
		
		$id1 = $this->CI->words->get_word_id($this->user_id,$word1);
		$id2 = $this->CI->words->get_word_id($this->user_id,$word2);
		
		$fowardLink = $this->CI->word_flow->word_link_exists($id1,$id2);
		
		$reverseLink = $this->CI->word_flow->word_link_exists($id2,$id1);
		
		if($fowardLink && $reverseLink){
			return ($fowardLink > $reverseLink)?true:false;
		}
		
		if($fowardLink){
			
			return true;
		}
		
		if($reverseLink){
			return false;
		}
		
		if(!$fowardLink && !$reverseLink){
			return true; 
		}
	}
	
		
	private function fetchWords($ids){
		
		$text = "";
	
		foreach($ids as $id){
			
			$word_i = $this->CI->words->get_word($this->user_id,$id);	

			if($word_i){
				$text .=  $word_i . " ";
			}
		}
		
		return rtrim($text);
	}
	
	private function connectNodes($id1,$id2){
			
		$existing = $this->CI->connections->get_connection($id1,$id2);

		if($existing){
			$this->CI->connections->increment_strength($existing);
		}else{
		    $this->CI->connections->set_connection($id1,$id2);
		}
	}
	
	
	private function traverseX($ids,$text){
	
		$finalResult = array();
		$checked = array();
		$this->state = array();
	
		foreach($ids as $id){
		
			
			$ph_array = $this->traverse($id,$ids,$finalResult,$checked);
			
			$ph = $ph_array["results"];
			
			$checked = array_unique (array_merge ($checked, $ph_array["checked"]));
			
			if($ph){
	
				foreach($ph as $phval){
					array_push($finalResult, $phval);
				}
				
			}
		}

		if(empty($finalResult)){
			$this->CI->current_input->add_current_input($text);
			return false;
		}else{
			return ($finalResult);
		}
	
	}
	
	function traverse($id,$ids,&$finalResult,&$checked,$results = array(),$prev_id = 0,$itr=0){

		$oneWordSelf = false;
	
		if(sizeof($ids)==1 & $id==$ids[0]){
			$oneWordSelf = true;
		}
	
		array_push($checked,$id);
		
		
		$connection = $this->CI->connections->get_connection($id,$id);		
		
		if($connection & (!$oneWordSelf)){
			$results[] = $id;
		}else{

			
			$cons = $this->CI->connections->get_connections($id);

			if(!(is_array($cons) & sizeof($cons) > 0 )){
				return false;			
			}

			foreach($cons as $val){
				
				if($this->CI->connections->match_connections($val,$ids)){
					if(in_array($val,$finalResult)){
						continue;
					}
					
					$results[] = $val;
					continue;
				}else{
					
					
						if($itr >=6){
							continue;
						}
		
						$shortCircuit = $this->shortCircuit($prev_id,$id,$val);
						if(!in_array($val,$checked) && !$shortCircuit && $val!=$id){	
							$this->updateState($prev_id,$id,$val);
							return $this->traverse($val,$ids,$finalResult,$checked,$results,$id,++$itr);
						}	
				}
		  }
		}
	
		$response = array("checked" => $checked,"results"=>$results);
		
		if(empty($results)){
			return false;
		}else{
			return $response;
		}
	
	}
	
	private function updateState($prev_id,$id,$val){
		$this->state[] = array($prev_id,$id,$val);
	}
	
	private function shortCircuit($prev_id,$id,$val){
		$nowStates = $this->state;
		foreach ($nowStates as $nowState){
			if(in_array($prev_id,$nowState) &&  in_array($id,$nowState) &&  in_array($val,$nowState)){
				return true;
			}
		}	
			return false;
	}
}