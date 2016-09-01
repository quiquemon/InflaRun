<?php
namespace Application\Model\Pojo;

class DiaHit {
	private $idDiaHit;
	private $horario;
	private $noLugares;
	private $lugaresRestantes;
	private $idDiaEvento;
	
	public function setIdDiaHit($idDiaHit) {
		$this -> idDiaHit = $idDiaHit;
		return $this;
	}
	
	public function getIdDiaHit() {
		return $this -> idDiaHit;
	}
	
	public function setHorario($horario) {
		$this -> horario = $horario;
		return $this;
	}
	
	public function getHorario() {
		return $this -> horario;
	}
	
	public function setNoLugares($noLugares) {
		$this -> noLugares = $noLugares;
		return $this;
	}
	
	public function getNoLugares() {
		return $this -> noLugares;
	}
	
	public function setLugaresRestantes($lugaresRestantes) {
		$this -> lugaresRestantes = $lugaresRestantes;
		return $this;
	}
	
	public function getLugaresRestantes() {
		return $this -> lugaresRestantes;
	}
	
	public function setIdDiaEvento($idDiaEvento) {
		$this -> idDiaEvento = $idDiaEvento;
		return $this;
	}
	
	public function getIdDiaEvento() {
		return $this -> idDiaEvento;
	}
}