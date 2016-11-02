<?php
namespace Application\Model\Controller\Cuenta\Pagos;

/**
 * Clase que se encarga de realizar los pagos de las inscripciones
 * a las carreras.
 */
abstract class Pagos {
	
	protected $idSucursal = "84a67340ba79c027fb93cb3a4a950e4a073e1a42";
	protected $idUsuario = "42dc22feba0a010ee090bd7d58e69cc946292649";
	
	/**
	 * Realiza el pago utilizando la información indicada.
	 * 
	 * @param Array $params Arreglo asociativo que incluye la información
	 * necesaria para realizar el pago.
	 * @return Array Un arreglo asociativo con la respuesta de la transacción.
	 */
	public abstract function realizarPago($params);
}