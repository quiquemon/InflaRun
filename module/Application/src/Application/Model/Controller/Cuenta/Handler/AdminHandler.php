<?php
namespace Application\Model\Controller\Cuenta\Handler;

use Application\Model\Correos\CorreoInscripcion;
use Application\Model\Dao\EquipoDao;
use Application\Model\Controller\Cuenta\Handler\DiaHitHandler;
use Application\Model\Controller\Cuenta\Pagos\PagoEfectivo;

/**
 * Clase que maneja las operaciones que el administrador lleva a cabo.
 */
class AdminHandler {
	
	/**
	 * Acepta todas las órdenes de pago que hayan sido aceptadas de acuerdo
	 * a la documentación de la API de PagoFácil, realiza los cambios necesarios
	 * a la base de datos y envía su correo de confirmación.
	 * 
	 * @throws Exception
	 */
	public static function aceptarOrdenesPagadas() {
		$dao = new EquipoDao("216.104.39.14");
		try {
			$dao -> beginTransaction();
			$pagos = self::obtenerPagosPendientes();
			$pagosRealizados = self::filtrarPagos($pagos);
			$r = self::aceptarPagos($pagosRealizados, $dao);
			$dao -> commit();
			return $pagos;
		} catch (\Exception $ex) {
			$dao -> rollback();
			throw new \Exception($ex -> getMessage());
		}
	}
	
	/**
	 * Rechaza todas las órdenes de pago que hayan expirado de acuerdo a la API
	 * de PagoFácil y realiza los cambios necesarios a la base de datos.
	 * 
	 * @throws Exception
	 */
	public static function rechazarOrdenesPagadas() {
		$dao = new EquipoDao();
		try {
			$dao -> beginTransaction();
			$dao -> commit();
		} catch (\Exception $ex) {
			$dao -> rollback();
			throw new \Exception($ex -> getMessage());
		}
	}
	
	/**
	 * Obtiene todos los pagos pendientes hasta el día actual.
	 * @return Array Una lista de arreglos asociativos con la información de los pagos.
	 */
	private static function obtenerPagosPendientes() {
		return ((new EquipoDao("216.104.39.14")) -> consultaGenerica("SELECT * FROM Pago"));
	}
	
	/**
	 * Filtra los pagos pendientes de acuerdo a la respuesta que PagoFácil tenga sobre ellos.
	 * 
	 * @param Array $pagos Lista de arreglos asociativos con la información de los pagos.
	 * @return Array Arreglo asociativo con únicamente aquellos pagos que ya hayan sido realizados.
	 */
	private static function filtrarPagos($pagos) {
		$pagosRealizados = array();
		
		foreach($pagos as $pago) {
			$result = self::consultarOrden($pago["transaccion"]);
			if ($result["charge"]["status"] == 4) {
				$pagosRealizados[] = $pago;
			}
		}
		
		return $pagosRealizados;
	}
	
	/**
	 * Realiza los cambios correspondientes en la base de datos y envía un correo a los
	 * usuarios cuyos pagos ya hayan sido realizados.
	 * 
	 * @param Array Arreglo asociativo con únicamente aquellos pagos que ya hayan sido realizados.
	 * @param ConexionDao Objeto con una conexión activa a la base de datos.
	 */
	private static function aceptarPagos($pagos, $dao) {
		foreach ($pagos as $pago) {
			
		}
		
		return $pagos;
	}
	
	/**
	 * Consulta el estatus de la orden indicada
	 * 
	 * @param string $referencia La referencia de la orden a consultar.
	 */
	private static function consultarOrden($referencia) {
		return (new PagoEfectivo()) -> consultarOrden($referencia);
	}
}