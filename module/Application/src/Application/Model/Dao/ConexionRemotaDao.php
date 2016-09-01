<?php
namespace Application\Model\Dao;

class ConexionRemotaDao extends ConexionDao {
	
	public function __construct() {
		parent::__construct("216.104.39.14");
	}
	
	public function actualizar($obj, $nuevo) {}

	public function buscar($obj) {}

	public function buscarTodos() {}

	public function eliminar($obj) {}

	public function insertar($obj) {}
}