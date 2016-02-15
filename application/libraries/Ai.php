<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai{
	
	private $text,$correct,$user_id,$state,$done;
	
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
		$this->done = array();
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
				echo $response . "<br />";
				$response = $clean_response = $this->removeConsecutiveRepeat($this->cleanResponse($this->removeConsecutiveRepeat($response)));
				$this->CI->current_output->truncate_current_output();
				$this->CI->current_output->add_current_output($this->text);
			}
		
			$response = trim($response);
			
			if(!$response){
				echo "I dont Know.";
			}else{
				echo trim($response) . ".";
			}
				
  
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
		
		for($i=0;$i<$size-1;$i++){
			for($j=$i+1;$j<$size;$j++){
		
				$from = $this->CI->words->get_word_id($this->user_id,$words[$i]);			
				$to = $this->CI->words->get_word_id($this->user_id,$words[$j]);
				$this->CI->word_flow->link_words($from,$to);
			}
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
	
	private function cleanResponse($text){
	 	
		echo "input text : " . $text . "<br />";
		$words = explode(" ",$text);
		$size  = sizeOf($words);
		$final_response = array();
		$picked_up = array();

		for($i=0;$i<$size;$i++){
			$i_id = $this->CI->words->get_word_id($this->user_id,$words[$i]);
			if(!$i_id){
				continue;
			}
			
			$self_link = $this->CI->word_flow->word_link_exists($i_id,$i_id);
			$latest = $self_link;
			
			if($self_link){
				$loop_id = $i;
			}else{
				$loop_id = 0;
			}
			
			echo "<br />I is : " . $this->CI->words->get_word($this->user_id,$i_id) . "<br /><br />";
			for($j=0;$j<$size;$j++){
				
				if($i===$j || in_array($j, $picked_up)){
					continue;
				}
				
				$j_id = $this->CI->words->get_word_id($this->user_id,$words[$j]);
				
				
				echo "J is : " . $this->CI->words->get_word($this->user_id,$j_id) . "<br />";
				
				if($j_id){
					$foward_link = $this->CI->word_flow->word_link_exists($i_id,$j_id);
				}else{
					continue;
				}
				
				if($foward_link && ($foward_link > $latest)){
					$latest =  $foward_link;
					echo "Linked to : " . $this->CI->words->get_word($this->user_id,$j_id) . "<br />";
					$loop_id = $j_id;
					array_push($picked_up,$j);
				}
			}
			
			if($latest){
			
			$temp = array("word_id"=>$this->CI->words->get_word($this->user_id,$i_id),"linked_word_id"=>$this->CI->words->get_word($this->user_id,$loop_id));
				//$temp = array("word_id"=>$i_id,"linked_word_id"=>$loop_id);
				
				array_push($final_response,$temp);

			}			
		}
		
		echo "<pre>";
		var_dump($final_response);
		echo "<br /><br /><br />";
		
		$size = sizeOf($final_response);
		$final_ids = array();
		$lword = 0;
		if($size > 0){
			array_push($final_ids,$final_response[0]["word_id"]);
			$lword = $this->findLinking($final_response,$final_response[0]["word_id"]);
		}
		
		
		$prevContinue = false;
		
		for($i=0;$i<$size;$i++){
			
			$found = false;
			$link_id = $final_response[$i]["linked_word_id"];

			for($j=$i+1;$j<$size;$j++){
				
				$word_j = $final_response[$j]["word_id"];
				if($link_id === $word_j){
					$found = true;
					$temp = $final_response[$i+1];
					$final_response[$i+1] = $final_response[$j];
					$final_response[$j] = $temp;
					continue;
				}
					
			}
			
			if(!$found && $i!=($size-1)){
			   if($i > 0){
			   					   	
			   		if(!$prevContinue){
			   			$temp = $final_response[$i];
			   			$temp['continues'] = false;
			   			$prevContinue = false;
			   			unset($final_response[$i]);
			   			$final_response[$i]['continues'] = true;
			   			array_push($final_response,$temp);
			   		}else{
			   			$final_response[$i]['continues'] = false;			   			
			   			$prevContinue = false;
			   		}
			   }else{
			   		$final_response[$i]['continues'] = true;
			   }
			  
			}else{
				$final_response[$i]['continues'] = false;
				$prevContinue = true;
			}
			
		}
		
		echo "<pre>";
		var_dump($final_response);
		
		$strike = 0;
		for($i=0;$i<$size;$i++){
			
			if($strike <2){
				$current_word = $final_response[$i]["word_id"];
				$current_linked_word = $final_response[$i]["linked_word_id"];
				$continues = $final_response[$i]['continues'];
				array_push($final_ids,$current_word);
				if(!$continues){
					
					array_push($final_ids,$current_linked_word);
					if($i==($size-1) || $strike==1){
						//array_push($final_ids,-2);
					}else{
						array_push($final_ids,-1);
					}
					
					$strike++;
				}
			}else{
				break;
			}
		}
			
		return $this->fetchWords($final_ids);
	}
	
	
	
	
	private function findLinking($final_array,$word_id){
		
		$size = sizeOf($final_array);
		$orig_array = $final_array;
		$word_id = intval($word_id);

		for($k=0;$k<$size;$k++){
			
			if(in_array($k, $this->done)){
				echo "unsetting....";
				unset($final_array[$k]);
			}
		}
		
		for($k=0;$k<$size;$k++){
			if($final_array[$k]["word_id"] === $word_id){
				array_push($this->done,$this->getOriginalK($orig_array,$word_id));
				return $final_array[$k]["linked_word_id"];
			}
			
		}
		
		return false;
	}
	
	private function getOriginalK($orig_array,$word_id){
		
		$size = sizeOf($orig_array);
		
		for($k=0;$k<$size;$k++){
			if($orig_array[$k]["word_id"] === $word_id){
				return $k;
			}
				
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
		    
			switch($id){
				case -1:
					$text .=  ", ";
					break;
				case  -2:
					//$text .=  ".";
					break;
				default:
					$word_i = $this->CI->words->get_word($this->user_id,$id);
					
					if($word_i){
						$text .=  $word_i . " ";
					}
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