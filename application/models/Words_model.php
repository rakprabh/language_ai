<?php
class Words_model extends CI_Model {

       public $word;

       public function __construct()
        {
             // Call the CI_Model constructor
             parent::__construct();
			 $this->load->database();
			 $this->load->library('session');
			 
        }

        public function word_exists($userId,$word)
        {
        	$limit  = 1;
			$offset = 0;
			$word  = trim($word);	
            $query = $this->db->get_where('words', array('user_id'=>$userId,'word' => $word), $limit, $offset);

            if ($query->num_rows() > 0)
			{
				return true;
			}else{
				return false;
			}
        }

        public function insert_word($userId,$word)
        {
        	$word = trim($word);
			$data = array('user_id'=>$userId,'word' => $word);
			$this->db->trans_start();
            $insert = $this->db->insert('words', $data);
			$this->db->trans_complete();
				
			if($insert){
				return $this->db->insert_id();
			}
        }

        public function get_word_id($userId,$word)
        {
        	$limit  = 1;
			$offset = 0;
        	$word = trim($word);
			$query = $this->db->get_where('words', array('user_id'=>$userId,'word' => $word), $limit, $offset);

			$row = $query->result();
			if(is_array($row) & (sizeOf($row) > 0)){				
				return $row[0]->id;
			}else{
				return false;
			}
        }
		
		public function get_word($userId,$id){
			
			$limit  = 1;
			$offset = 0;
			$query = $this->db->get_where('words', array('user_id'=>$userId,'id' => $id), $limit, $offset);
			$row = $query->result();
			
			if(is_array($row) & sizeof($row) > 0){
				$row = $row[0];
			}else{
				return false;
			}
			
			if(is_object($row)){
				return $row->word;
			}else{
				return false;
			}
			
		}
		
		public function get_ids($text){
			
			$ids = array();
			$i = 0;
			$user_id = $this->session->user_id;
			$words = explode(" ",$text);
	
			foreach($words as $index=>$word){
				$ids[$i++] = $this->get_word_id($user_id,$word);
			}
	
			return $ids;
			
		}
		
		public function get_words($userId){			
			
			$limit  = 0;
			$offset = 0;
			
			$query = $this->db->get_where('words', array('user_id' => $userId), $limit, $offset);

			$rows = $query->result();
			
			if(!(is_array($rows) & sizeOf($rows) > 0)){
				return false;
			}
			
			return $rows;
		}
		
		public function get_word_count($userId){
			
			$this->db->like('user_id', $userId);
			$this->db->from('words');
			return $this->db->count_all_results();
		}
}