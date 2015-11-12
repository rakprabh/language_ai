<?php
class Users_model extends CI_Model {

       public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
        }

        public function id_exists($id)
        {
        	$limit  = 1;
			$offset = 0;
			$id  = trim($id);	
            $query = $this->db->get_where('users', array('id' => $id), $limit, $offset);

            if ($query->num_rows() > 0)
			{
				return true;
			}else{
				return false;
			}
        }
		
		
		public function email_exists($email)
        {
        	$limit  = 1;
			$offset = 0;
			$email = trim($email);	
            $query = $this->db->get_where('users', array('email' => $email), $limit, $offset);

            if ($query->num_rows() > 0)
			{
				return true;
			}else{
				return false;
			}
        }
        
		
        public function insert_user($name,$email)
        {
			$name = trim($name);
			$email = trim($email);
			
			$data = array('name'=>$name,'email'=>$email);
			$this->db->trans_start();
            $insert = $this->db->insert('users', $data);
			$this->db->trans_complete();
				
			if($insert){
				return $this->db->insert_id();
			}
        }
		
		public function get_id_from_email($email){
			
			$limit  = 1;
			$offset = 0;
        	$email = trim($email);
			$query = $this->db->get_where('users', array('email' => $email), $limit, $offset);

			$row = $query->result();
			if(is_array($row) & (sizeOf($row) > 0)){				
				return $row[0]->id;
			}else{
				return false;
			}
			
		}

}