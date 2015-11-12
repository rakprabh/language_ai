<?php
class Current_input_model extends CI_Model {

       public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
        }

        public function get_current_input()
        {
                $query = $this->db->get('current_input',1);
				
                $row =  $query->result();
				if(is_array($row) & sizeOf($row) > 0){
					$row = $row[0];
				}

				if(is_object($row)){
					return $row->input;
				}else{
					return false;
				}
				
        }
		
		public function truncate_current_input(){
			return $this->db->truncate('current_input');
			
		}
		
		public function add_current_input($text){
				
			$text = trim($text);
			$data = array('input'=>$text);
			
			$this->db->trans_start();
            $insert = $this->db->insert('current_input', $data);
			$this->db->trans_complete();
				
			if($insert){
				return $this->db->insert_id();
			}	
		}
}