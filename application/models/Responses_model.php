<?php
class Responses_model extends CI_Model {

       public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
        }
		
		
		public function get_responses($user_id,$keyword,$startDate,$endDate){			
			
			$limit  = 0;
			$offset = 0;
			
			$user_id = trim($user_id);
			$keyword = trim($keyword);
			$startDate = trim($startDate);
			$endDate = trim($endDate);
		
     		$queryArray = array('user_id' => $user_id);		
			 
			if($startDate){
				$queryArray['ts >='] = $startDate;
			}

			if($endDate){
				$queryArray['ts <='] = $endDate;
			}
			
			if($keyword){
				$this->db->group_start();
				$this->db->like('query', $keyword);
				$this->db->or_like('response', $keyword);
				$this->db->group_end();
			}
			
			$query = $this->db->get_where('responses',$queryArray, $limit, $offset);
			
			
			
			$rows = $query->result();
			
			if(!(is_array($rows) & sizeOf($rows) > 0)){
				return false;
			}
			
			return $rows;
		}
		
		public function insert_response($uid,$query,$response){
			
			$query = trim($query);
			$response = trim($response);			
			$data = array('query' => $query ,'response' => $response ,'user_id'=> $uid);
			
			$this->db->trans_start();			
			$insert = $this->db->insert('responses', $data);
			$this->db->trans_complete();
				
			if($insert){
				return $this->db->insert_id();
			}
			
		}
}