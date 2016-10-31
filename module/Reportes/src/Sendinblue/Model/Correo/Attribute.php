<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sendinblue\Model\Correo;
use Application\Model\Correos\Mailin;

/**
 * Description of Attribute
 *
 * @author qnhama
 */
class Attribute {
    //put your code here
    private $data;
    private $mailin;
    
    
    public function __construct() {
        $this -> mailin  = new Mailin("https://api.sendinblue.com/v2.0","NQGOIrgFEVKYxp51");
    }
    
    public function listAlAttributes() {
	return $this->mailin-> get_attributes();
    }
}
