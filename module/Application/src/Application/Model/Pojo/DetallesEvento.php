<?php
namespace Application\Model\Pojo;

class DetallesEvento {
	private $idDetallesEvento;
	private $nombre;
	private $direccion;
	private $correoContacto;
	private $precio;
	private $realizado;
	private $idEstado;
	private $idEvento;
	
	public function setIdDetallesEvento($idDetallesEvento) {
		$this -> idDetallesEvento = $idDetallesEvento;
		return $this;
	}
	
	public function getIdDetallesEvento() {
		return $this -> idDetallesEvento;
	}
	
	public function setNombre($nombre) {
		$this -> nombre = $nombre;
		return $this;
	}
	
	public function getNombre() {
		return $this -> nombre;
	}
	
	public function setDireccion($direccion) {
		$this -> direccion = $direccion;
		return $this;
	}
	
	public function getDireccion() {
		return $this -> direccion;
	}
	
	public function setCorreoContacto($correoContacto) {
		$this -> correoContacto = $correoContacto;
		return $this;
	}
	
	public function getCorreoContacto() {
		return $this -> correoContacto;
	}
	
	public function setPrecio($precio) {
		$this -> precio = $precio;
		return $this;
	}
	
	public function getPrecio() {
		return $this -> precio;
	}
	
	public function setRealizado($realizado) {
		$this -> realizado = $realizado;
		return $this;
	}
	
	public function getRealizado() {
		return $this -> realizado;
	}
	
	public function setIdEstado($idEstado) {
		$this -> idEstado = $idEstado;
		return $this;
	}
	
	public function getIdEstado() {
		return $this -> idEstado;
	}
	
	public function setIdEvento($idEvento) {
		$this -> idEvento = $idEvento;
		return $this;
	}
	
	public function getIdEvento() {
		return $this -> idEvento;
	}
}
