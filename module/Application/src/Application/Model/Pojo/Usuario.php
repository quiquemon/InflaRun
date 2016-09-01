<?php
namespace Application\Model\Pojo;

class Usuario {
	private $idUsuario;
	private $nombre;
	private $aPaterno;
	private $aMaterno;
	private $correo;
	private $password;
	private $sexo;
	private $fechaNacimiento;
	private $fechaRegistro;
	private $recibeCorreos;
	private $idEstado;
	
	public function setIdUsuario($idUsuario) {
		$this -> idUsuario = $idUsuario;
		return $this;
	}
	
	public function getIdUsuario() {
		return $this -> idUsuario;
	}
	
	public function setNombre($nombre) {
		$this -> nombre = $nombre;
		return $this;
	}
	
	public function getNombre() {
		return $this -> nombre;
	}
	
	public function setAPaterno($aPaterno) {
		$this -> aPaterno = $aPaterno;
		return $this;
	}
	
	public function getAPaterno() {
		return $this -> aPaterno;
	}
	
	public function setAMaterno($aMaterno) {
		$this -> aMaterno = $aMaterno;
		return $this;
	}
	
	public function getAMaterno() {
		return $this -> aMaterno;
	}
	
	public function setCorreo($correo) {
		$this -> correo = $correo;
		return $this;
	}
	
	public function getCorreo() {
		return $this -> correo;
	}
	
	public function setPassword($password) {
		$this -> password = $password;
		return $this;
	}
	
	public function getPassword() {
		return $this -> password;
	}
	
	public function setSexo($sexo){
		$this -> sexo = $sexo;
		return $this;
	}
	
	public function getSexo() {
		return $this -> sexo;
	}
	
	public function setFechaNacimiento($fechaNacimiento) {
		$this -> fechaNacimiento = $fechaNacimiento;
		return $this;
	}
	
	public function getFechaNacimiento() {
		return $this -> fechaNacimiento;
	}
	
	public function setFechaRegistro($fechaRegistro) {
		$this -> fechaRegistro = $fechaRegistro;
		return $this;
	}
	
	public function getFechaRegistro() {
		return $this -> fechaRegistro;
	}
	
	public function setRecibeCorreos($recibeCorreos) {
		$this -> recibeCorreos = $recibeCorreos;
		return $this;
	}
	
	public function getRecibeCorreos() {
		return $this -> recibeCorreos;
	}
	
	public function setIdEstado($idEstado) {
		$this -> idEstado = $idEstado;
		return $this;
	}
	
	public function getIdEstado() {
		return $this -> idEstado;
	}
}