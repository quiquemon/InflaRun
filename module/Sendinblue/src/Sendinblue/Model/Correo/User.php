<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sendinblue\Model\Correo;
use Application\Model\Correos\Mailin;

/**
 * Description of User
 *
 * @author qnhama
 */
class User {
    //put your code here
    private $data;
    private $mailin;
    
    
    public function __construct() {
        $this -> mailin  = new Mailin("https://api.sendinblue.com/v2.0","NQGOIrgFEVKYxp51");
    }
    
    public function getAtributes($email) {
	$this -> data = array( 
            "email" => $email
        );
	return $this->mailin-> get_user($this->data);
    }
    
    public function createUser($email, $attributes, $listid) {
	$this -> data = array( 
            "email" => $email,
            "attributes" => $attributes,
            "listid" => array($listid)
        );
	return $this->mailin-> create_update_user($this->data);
    }
    
    
    
}
