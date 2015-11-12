<?php
class Word_flow_model extends CI_Model {

       public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
        }

        public function word_link_exists($id1,$id2)
        {
            $limit  = 1;
			$offset = 0;
				
            $query = $this->db->get_where('word_flow', array('start' => $id1,'end'=>$id2), $limit, $offset);
			$result = $query->result();
			
			if ($query->num_rows() > 0)
			{
				$row = $result[0];
				return $row->strength;
			}else{
				return false;
			}

        }

        public function link_words($id1,$id2)
        {
        	
			$limit  = 1;
			$offset = 0;
			
        	$existing = false;
			$existing_id = 0;
			
			$query = $this->db->get_where('word_flow', array('start' => $id1,'end'=>$id2), $limit, $offset);
			$row = $query->result();
				
			if(is_array($row) & sizeOf($row) > 0){
				$existing = true;
				$row = $row[0];
				$existing_id = $row->id;
			}
			
			
			if(!$existing){
				$data = array('start' => $id1,'end'=>$id2,'strength' => 1);
				$this->db->trans_start();
	            $insert = $this->db->insert('word_flow', $data);
				$this->db->trans_complete();
					
				if($insert){
					return $this->db->insert_id();
				}
			}else{
								
				$this->db->trans_start();			
				$this->db->where('id', $existing_id);
				$this->db->set('strength', 'strength+1', FALSE);
				$response = $this->db->update('word_flow');
				$this->db->trans_complete();			
				return $response;
			}
        }

}