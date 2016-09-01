<?php
namespace Application\Model\Controller\Cuenta\Handler;

use Application\Model\Controller\Cuenta\Pagos\PagoTarjeta;
use Application\Model\Controller\Cuenta\Pagos\PagoEfectivo;

/**
 * Clase que maneja la realización de los pagos de las carreras.
 * La plataforma utilizada es PagoFácil.
 */
class PagosHandler {
	
	private static $MENSAJES = array(
		"ERROR_PAGO" => array(
			"TARJETA" => "Lo sentimos, ocurrió un error al procesar tu pago."
				. " Asegúrate de haber ingresado correctamente todos tus datos.",
			"EFECTIVO" => "Lo sentimos, ocurrió un error al procesar tu pago."
				. " Es posible que esta sucursal no esté disponible. Recuerda que"
				. " algunas tiendas tienen un monto máximo para pagar."
		)
	);
	
	/**
	 * Regresa el arreglo asociativo de mensajes asociado a esta clase.
	 * 
	 * @return Array
	 */
	public static function getMensajes() {
		return self::$MENSAJES;
	}
	
	/**
	 * Realiza el pago con los datos bancarios indicados.
	 * 
	 * @param Array $datosBancarios Arreglo asociativo que incluye los datos bancarios del usuario.
	 * @return Array Arreglo asociativo que incluye la respuesta de la API de PagoFácil.
	 * @throws Exception
	 */
	public static function realizarPagoTarjeta($datosBancarios) {
		$result = (new PagoTarjeta()) -> realizarPago($datosBancarios);
		return $result;
	}
	
	/**
	 * Realiza el pago con los datos bancarios indicados.
	 * 
	 * @param Array $datosBancarios Areglo asociativo que incluye los datos bancarios del usuario.
	 * @return Array Arreglo asociativo que incluye la respuesta de la API de PagoFácil.
	 * @throws Exception
	 */
	public static function realizarPagoEfectivo($datosBancarios) {
		$result = (new PagoEfectivo()) -> realizarPago($datosBancarios);
		return $result;
	}
}