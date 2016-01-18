<?php 
class Word_flow_followup_model extends CI_Model {

	public function __construct()
	{
		// Call the CI_Model constructor
		parent::__construct();
	}
	
	
 	public function word_flow_link_exists($id1,$id2)
    {
            $limit  = 1;
			$offset = 0;
				
            $query = $this->db->get_where('word_flow_followup', array('word_flow_id1' => $id1,'word_flow_id2'=>$id2), $limit, $offset);
			$result = $query->result();
			
			if ($query->num_rows() > 0)
			{
				$row = $result[0];
				return $row->strength;
			}else{
				return false;
			}

     }
     
     
     public function link_words_flow($id1,$id2)
     {
     	 
     	$limit  = 1;
     	$offset = 0;
     		
     	$existing = false;
     	$existing_id = 0;
     		
     	$query = $this->db->get_where('word_flow_followup', array('word_flow_id1' => $id1,'word_flow_id2'=>$id2), $limit, $offset);
     	$row = $query->result();
     
     	if(is_array($row) & sizeOf($row) > 0){
     		$existing = true;
     		$row = $row[0];
     		$existing_id = $row->id;
     	}
     		
     		
     	if(!$existing){
     		$data = array('word_flow_id1' => $id1,'word_flow_id2'=>$id2,'strength' => 1);
     		$this->db->trans_start();
     		$insert = $this->db->insert('word_flow_followup', $data);
     		$this->db->trans_complete();
     			
     		if($insert){
     			return $this->db->insert_id();
     		}
     	}else{
     
     		$this->db->trans_start();
     		$this->db->where('id', $existing_id);
     		$this->db->set('strength', 'strength+1', FALSE);
     		$response = $this->db->update('word_flow_followup');
     		$this->db->trans_complete();
     		return $response;
     	}
     }
	
}