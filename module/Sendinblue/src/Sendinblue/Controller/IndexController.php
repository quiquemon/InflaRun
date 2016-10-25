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
use Zend\View\Model\JsonModel;


use Sendinblue\Model\Correo\Folder;
use Sendinblue\Model\Correo\Lista;
use Sendinblue\Model\Correo\User;
use Sendinblue\Model\Correo\Attribute;


class IndexController extends AbstractActionController
{
    public function indexAction()
    {   
        //$url=$this->getRequest()->getBaseUrl();
        $Folder = new Folder();
        $ListAllFolder = $Folder ->getListAllFolder();
        /* @var $key type */
        foreach ($ListAllFolder['data']['folders'] as $key => $value){  
            $idFolders[] = $value['id'];                                     
        }  
       
        return  new ViewModel(array('idFolder'=>$idFolders[0]));
    }

   public function listsAction() {
        //Resibe parametros de la URL
        $id =  $this->params()->fromRoute("id", null);
        // $url=$this->getRequest()->getBaseUrl();
        $Folder = new Folder();
        $Lista = new Lista();
        $ListAllFolder = $Folder -> getListAllFolder();
        $FolderDetails = $Folder ->getDetails($id);
        $allLists = $Lista ->getAllLists($id);
        
        return  new ViewModel(
                array(
                    'ListAllFolder'=>$ListAllFolder, 
                    'FolderDetails'=>$FolderDetails,
                    'allLists'=>$allLists,
                    'id' => $id
                )
        ); 
        
        
   }
   
   
   
    public function deleteFolderAction(){
        $Folder = new Folder();
        $id =  $this->params()->fromRoute("id", null);
        $response = $Folder ->deleteFolder($id);
        $ListAllFolder = $Folder ->getListAllFolder();
        foreach ($ListAllFolder['data']['folders'] as $key => $value){  
            $idFolders[] = $value['id'];                                     
        }  
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/sendinblue/lists/'.$idFolders[0]);
    }
    
    public function addFolderAction(){
        $Folder = new Folder();
        $name =  $this->params()->fromRoute("name", null);
        $response = $Folder ->addFolder($name);
        $ListAllFolder = $Folder ->getListAllFolder();
        foreach ($ListAllFolder['data']['folders'] as $key => $value){  
            $idFolders[] = $value['id'];                                     
        }  
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/sendinblue/lists/'.$idFolders[0]);
    }
    
    public function updateFolderAction(){
        $Folder = new Folder();
        $id =  $this->params()->fromRoute("id", null);
        $name =  $this->params()->fromRoute("name", null);
        $response = $Folder ->updateFolder($name,$id);
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/sendinblue/lists/'.$id);
    }
    
    public function addListAction(){
        $List = new Lista();
        $name =  $this->params()->fromRoute("name", null);
        $id =  $this->params()->fromRoute("id", null);
        $List ->addList($name,$id );
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/sendinblue/lists/'.$id);
    }
    
    public function updateListAction(){
        $List = new Lista();
        $name =  $this->params()->fromRoute("name", null);
        $id =  $this->params()->fromRoute("id", null);
        $idL =  $this->params()->fromRoute("idL", null);
        $List ->updateList($name,$id,$idL);
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/sendinblue/lists/'.$id);
        //return  new ViewModel( array('name'=>$name,'id'=>$id,'idL'=>$idL)); 
    }
   
   public function deleteListaAction(){
        $List = new Lista();
        $id =  $this->params()->fromRoute("id", null);
        $idL =  $this->params()->fromRoute("idL", null);
        $response = $List ->deleteList($idL);
        
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/sendinblue/lists/'.$id);
    }
    
    public function usersAction(){
        $List = new Lista();
        $id =  $this->params()->fromRoute("id", null);
        return  new ViewModel(array('id'=>$id));
    }
    
    public function displayListUsersAction(){
        
        $List = new Lista();
        $User = new User();
        $id =  $this->params()->fromRoute("id", null);
        $pagina = $this -> params() -> fromQuery("pagina", "");
        $correos = $List ->displayListUsers($id,$pagina);
        
        /*$array = array();
        $array["users"] = array();
        
       
        foreach ($correos['data']['data'] as $key => $value)  
        {  
            //echo $key ."|". $value . "<br />";
            
            $array["users"][$key] = array();
            $array["users"][$key]['id'] = $value['id'];
            $array["users"][$key]['correo'] = $value['email'];
            $atributos = $User ->getAtributes($value['email']);
            $array["users"][$key]['nombre'] = $atributos['data']['attributes']['NOMBRE'];
            $array["users"][$key]['apellido'] =$atributos['data']['attributes']['SURNAME'];
            $array["users"][$key]['ultimaModificacion'] = $value['last_modified'];
            
        } 
        */
        
        return new JsonModel($correos);
        //return new JsonModel($array['users']);
         /*return  new ViewModel(array(
             'array'=>$array['users']
           ));*/
    }
    
    public function particularListAction(){
        $List = new Lista();
        $id =  $this->params()->fromRoute("id", null);
        $response = $List ->particularList($id);
        return new JsonModel($response);
    }
    
     public function createUserAction(){
        $User = new User();
        $id =  $this->params()->fromRoute("id", null);
        $attributes= array(
                "NAME"=>"Mauricio", 
                "SURNAME"=>"Cunjama"
                );
        $response = $User ->createUser("qnhama@gmail.com", $attributes, $id);
        return new JsonModel($response);
    }
    
}
