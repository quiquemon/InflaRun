<?php

namespace Sendinblue\Model\Correo;


use Application\Model\Correos\Mailin;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sendinblue
 *
 * @author qnhama
 */
class Folder {
    //put your code here
    
    
    
    private $data;
    private $mailin;
    
    
    public function __construct() {
        $this -> mailin  = new Mailin("https://api.sendinblue.com/v2.0","NQGOIrgFEVKYxp51");
    }
    
    public function getListAllFolder() {
	$this -> data = array( 
            "page" => 1,
            "page_limit" => 50
        );
	return $this->mailin-> get_folders($this->data);
    }
    
    public function  getDetails($id){
        $this -> data = array( 
            $data = array( "id" => $id )
        );
	return $this->mailin->get_folder($data);
    }
    
    public function  deleteFolder($id){
        $this -> data = array( 
            $data = array( "id" => $id )
        );
	return $this->mailin->delete_folder($data);
    }
    
    
     public function  addFolder($name){
        $this -> data = array( 
            $data = array( "name"=> $name )
        );
	return $this->mailin->create_folder($data);
    }
    
     public function  updateFolder($name,$id){
        $this -> data = array( 
            $data = array( "id" => $id,
	       "name" => $name
	    )
        );
	return $this->mailin->update_folder($data);
    }
    
    
}
