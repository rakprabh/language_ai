<?php
class Current_output_model extends CI_Model {

       public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
        }

         public function get_current_output()
        {
                $query = $this->db->get('current_output',1);
                $row =  $query->result();
				
				if(is_array($row) & sizeOf($row) > 0){
					$row = $row[0];
				}else{
					return false;
				}
				
				return $row->output;
        }
		
		public function truncate_current_output(){
			return $this->db->truncate('current_output');
			
		}
		
		public function add_current_output($text){
				
			$data = array('output'=>$text);
			
			$this->db->trans_start();
            $insert = $this->db->insert('current_output', $data);
			$this->db->trans_complete();
				
			if($insert){
				return $this->db->insert_id();
			}	
		}
}