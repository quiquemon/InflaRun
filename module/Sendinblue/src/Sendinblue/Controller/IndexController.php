<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Sendinblue\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Sendinblue\Model\Correo\Mailin;


class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return array();
    }

   public function listsAction() {
        $mailin = new Mailin("https://api.sendinblue.com/v2.0","ghHMXvqERUftLc75");
        $data = array( "page" => 1,
        "page_limit" => 2
      );
 
      $resultado = $mailin->get_folders($data);
      
       
        return new ViewModel(array("texto"=>"hola mundo desde Zend, soy C&eacute;sar Cancino<br>id=".$resultado["data"]["folders"][0]["id"]));
   }
    
}
