<?php
namespace Application\Model\Pojo;

class Estado {
	private $idEstado;
	private $nombre;
	
	public function setIdEstado($idEstado) {
		$this -> idEstado = $idEstado;
		return $this;
	}
	
	public function getIdEstado() {
		return $this -> idEstado;
	}
	
	public function setNombre($nombre) {
		$this -> nombre = $nombre;
		return $this;
	}
	
	public function getNombre() {
		return $this -> nombre;
	}
}