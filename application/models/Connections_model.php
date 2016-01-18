<?php
class Connections_model extends CI_Model {

       public function __construct()
       {
            // Call the CI_Model constructor
            parent::__construct();
       }

        public function get_connection($id1,$id2)
        {
            $limit  = 1;
			$offset = 0;

			$query = $this->db->get_where('connections', array('start' => $id1,'dest'=>$id2), $limit, $offset);

			$row = $query->result();
			
						
			if(is_array($row) & sizeOf($row) > 0){
				$row = $row[0];
			}else{
				$row = false;
			}
			
			if($row){
				return $row->id;
			}else{						
				
				$query = $this->db->get_where('connections', array('start' => $id2,'dest'=>$id1), $limit, $offset);
				$row = $query->result();
				
				if(is_array($row) & sizeOf($row) > 0){
					$row = $row[0];
				}else{
					return false;
				}
				
				if($row){
		
					return $row->id;
				}else{
					return false;
				}
			}
        }
		
		public function get_connections($id)
        {
			$rset = array();
			$i = 0;
			
			$this->db->select('*');
			$this->db->from('connections');
			$this->db->where('start =', $id);
			$this->db->or_where('dest =', $id);
			$this->db->order_by('strength desc');
			$this->db->limit(41);
			
			$query = $this->db->get();
			$results = $query->result();
			
			if(!(is_array($results) & sizeof($results) > 0)){
				return false;
			}else{				
					
				foreach($results as $row){
					
					if($row->start == $id){
						$rset[$i++] = $row->dest;
					}else{
						$rset[$i++] = $row->start;
						
					}
				}
				
				return $rset;			
			}
        }
		
		public function match_connections($id,$ids){
	
			foreach($ids as $index=>$value){
				
				if(!$this->get_connection($id,$value)){

					return false;
				}
			}
	
			return true;
		}

        public function set_connection($id1,$id2)
        {
			$data = array('start' => $id1,'dest'=>$id2,'strength'=>1);
			$this->db->trans_start();
            $insert = $this->db->insert('connections', $data);
			$this->db->trans_complete();
				
			if($insert){
				return $this->db->insert_id();
			}
        }
		
		public function increment_strength($id){

			$this->db->trans_start();			
			$this->db->where('id', $id);
			$this->db->set('strength', 'strength+1', FALSE);
			$response = $this->db->update('connections');
			$this->db->trans_complete();
			
			return $response;
		}


}