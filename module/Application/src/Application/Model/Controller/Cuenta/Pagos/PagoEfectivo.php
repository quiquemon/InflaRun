<?php
namespace Application\Model\Controller\Cuenta\Pagos;

use Application\Model\Controller\Cuenta\Pagos\Pagos;

/**
 * Clase que permite realizar pagos en efectivo por medio del
 * servicio REST de PagoFácil. El proceso se realiza de acuerdo
 * a la documentación de la API v.1.4.3 de PagoFácil.
 */
class PagoEfectivo extends Pagos {
	
	public function realizarPago($params) {
		$params["branch_key"] = $this -> idSucursal;
		$params["user_key"] = $this -> idUsuario;
		$params["customer"] = urlencode($params["customer"]);
		
		$url = "https://www.pagofacil.net/ws/public/cash/charge";
		$options = array(
			"http" => array(
				"header" => "Content-type: application/x-www-form-urlencoded\r\n",
				"method" => "POST",
				"content" => http_build_query($params)
			)
		);
		
		$context = stream_context_create($options);
		$json = file_get_contents($url, false, $context);
		$respuesta = json_decode($json, true);
		return $respuesta;
	}
	
	/**
	 * Consulta la información sobre una orden en específico.
	 * 
	 * @param string $referencia La referencia de la orden a consultar.
	 * @return Array Un arreglo asociativo con el resultado de la consulta.
	 */
	public function consultarOrden($referencia) {
		$params["branch_key"] = $this -> idSucursal;
		$params["user_key"] = $this -> idUsuario;
		$params["reference"] = $referencia;
		
		$url = "https://www.pagofacil.net/ws/public/cash/charge"
			. "?branch_key={$params["branch_key"]}"
			. "&user_key={$params["user_key"]}"
			. "&reference={$params["reference"]}";
		
		$json = file_get_contents($url);
		$respuesta = json_decode($json, true);
		return $respuesta;
	}
}