<?php
class Questions_model extends CI_Model {

       public function __construct()
       {
                // Call the CI_Model constructor
                parent::__construct();
	   }		
		
	   public function get_questions($user_id,$keyword,$startDate,$endDate){
			
			$keyword = trim($keyword);
			$startDate = trim($startDate);
			$endDate = trim($endDate);
			
			$limit  = 0;
			$offset = 0;

			$queryArray = array('user_id' => $user_id);
			
			if($startDate){
				$queryArray['ts >='] = $startDate;
			}

			if($endDate){
				$queryArray['ts <='] = $endDate;
			}

			if($keyword){
				$this->db->like('question', $keyword);
			}
			
			$query = $this->db->get_where('questions',$queryArray , $limit, $offset);
			
			
			
			$rows = $query->result();

			if(!(is_array($rows) & sizeOf($rows) > 0)){
				return false;
			}
			
			return $rows;
		}
		
		public function insert_question($user_id,$question){
			
			$question = trim($question);
			
			$data = array('question' => $question ,'user_id'=> $user_id);
			
			$this->db->trans_start();			
			$insert = $this->db->insert('questions', $data);
			$this->db->trans_complete();
				
			if($insert){
				return $this->db->insert_id();
			}
			
		}
}