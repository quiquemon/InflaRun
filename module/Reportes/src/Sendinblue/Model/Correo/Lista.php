<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sendinblue\Model\Correo;
use Application\Model\Correos\Mailin;


/**
 * Description of Lista
 *
 * @author qnhama
 */
class Lista {
    private $data;
    private $mailin;
    public $IDLista; 
    
    
    public function __construct() {
        $this -> mailin  = new Mailin("https://api.sendinblue.com/v2.0","NQGOIrgFEVKYxp51");
    }
    
    public function getAllLists($id) {
	$this -> data = array( 
            "list_parent" => $id,
            "page" => 1,
            "page_limit" => 50
        );
	return $this->mailin-> get_lists($this->data);
    }
    
    public function  getDetails($id){
        $this -> data = array( 
            $data = array( "id" => $id )
        );
	return $this->mailin-> get_folders($this->data);
    }
    
    public function  deleteList($id){
        $this -> data = array( 
            $data = array( "id" => $id )
        );
	return $this->mailin->delete_list($data);
    }
    
    
     public function  addList($name,$id){
        $this -> data = array( 
            $data = array( 
                "list_name" =>$name,
                "list_parent" => $id 
            )
        );
	return $this->mailin->create_list($data);
    }
    
     public function  updateList($name,$id,$idL){
        $this-> lista = $idL;
        $this -> data = array( 
            $data = array( "id" => $idL,
                "list_name" => $name,
                "list_parent" => $id
	    )
        );
	return $this->mailin->update_list($data);
    }
    
    public function  displayListUsers($id,$pagina){
        $this -> data = array( 
            $data = array( 
            //"listids" => array($idL),
            "listids" => array($id),
            "page" => $pagina,
            "page_limit" => 50
	    )
        );
	return $this->mailin->display_list_users($data);
    }
    
    public function  particularList($id){
        $this -> data = array( 
            $data = array(
                "id"=>$id
	    )
        );
	return $this->mailin->get_list($data);
    }
    
    
}
