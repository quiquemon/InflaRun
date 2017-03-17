<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {
    
	public function indexAction() {
        return new ViewModel();
    }
	
	public function queesinflarunAction() {
		return new ViewModel();
	}
	
	public function prensaAction() {
		return new ViewModel();
	}
	
	public function preguntasfrecuentesAction() {
		return new ViewModel();
	}
	
	public function rutaAction() {
		return new ViewModel();
	}
	
	public function convocatoriaAction() {
		return new ViewModel();
	}
}