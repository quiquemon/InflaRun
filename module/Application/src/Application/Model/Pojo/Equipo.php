<?php
namespace Application\Model\Pojo;

class Equipo {
	private $idEquipo;
	private $nombre;
	private $codigoCanje;
	private $noIntegrantes;
	private $idDiaHit;
	
	public function setIdEquipo($idEquipo) {
		$this -> idEquipo = $idEquipo;
		return $this;
	}
	
	public function getIdEquipo() {
		return $this -> idEquipo;
	}
	
	public function setNombre($nombre) {
		$this -> nombre = $nombre;
		return $this;
	}
	
	public function getNombre() {
		return $this -> nombre;
	}
	
	public function setCodigoCanje($codigoCanje) {
		$this -> codigoCanje = $codigoCanje;
		return $this;
	}
	
	public function getCodigoCanje() {
		return $this -> codigoCanje;
	}
	
	public function setNoIntegrantes($noIntegrantes) {
		$this -> noIntegrantes = $noIntegrantes;
		return $this;
	}
	
	public function getNoIntegrantes() {
		return $this -> noIntegrantes;
	}
	
	public function setIdDiaHit($idDiaHit) {
		$this -> idDiaHit = $idDiaHit;
		return $this;
	}
	
	public function getIdDiaHit() {
		return $this -> idDiaHit;
	}
}