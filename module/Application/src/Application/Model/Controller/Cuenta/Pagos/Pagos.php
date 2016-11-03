<?php
namespace Application\Model\Controller\Cuenta\Pagos;

/**
 * Clase que se encarga de realizar los pagos de las inscripciones
 * a las carreras.
 */
abstract class Pagos {
	
	protected $llaves = array(
		"pruebas" => array(
			"usuario" => "ae04ac26148f20a8f3379c527095bd6344ea2c2b",
			"sucursal" => "430e1dab88003fd4cf06621c3e315e4fe33d8b53"
		),
		"produccion" => array(
			"usuario" => "41d8cbab7337c4b3c038e8dc26462394fe9dbd16",
			"sucursal" => "0ea688a37ffafb3324c89d68dd3635f5fe4d0ed8"
		)
	);
	
	/**
	 * Realiza el pago utilizando la información indicada.
	 * 
	 * @param Array $params Arreglo asociativo que incluye la información
	 * necesaria para realizar el pago.
	 * @return Array Un arreglo asociativo con la respuesta de la transacción.
	 */
	public abstract function realizarPago($params);
}