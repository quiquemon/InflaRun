<?php
namespace Application\Model\Controller\Cuenta\Handler;

/**
 * Funciones de utilidad para manejar las fechas.
 */
class FechasHandler {
	
	private $meses = array(
		"01" => "Enero",
		"02" => "Febrero",
		"03" => "Marzo",
		"04" => "Abril",
		"05" => "Mayo",
		"06" => "Junio",
		"07" => "Julio",
		"08" => "Agosto",
		"09" => "Septiembre",
		"10" => "Octubre",
		"11" => "Noviembre",
		"12" => "Diciembre"
	);
	
	/**
	 * Recibe una fecha en formato 'yyyy-mm-dd' y la traduce al español.
	 * 
	 * @param sring $fecha La fecha a traducir.
	 * @return string Una representación en español de la fecha. Si
	 * la fecha no tiene el formato requerido, regresa FALSE.
	 */
	public function traducirFecha($fecha) {
		if (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $fecha)) {
			$arr = explode("-", $fecha);
			return "{$arr[2]} de {$this -> meses[$arr[1]]} de {$arr[0]}";
		}
		
		return false;
	}
}