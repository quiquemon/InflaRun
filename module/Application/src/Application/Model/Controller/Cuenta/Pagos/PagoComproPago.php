<?php
namespace Application\Model\Controller\Cuenta\Pagos;

/**
 * Esta clase permite realizar los pagos en efectivo con
 * el proveedor ComproPago por medio de su servicio Web REST.
 * La versión de su API utilizada en esta clase es 16/Jun/2015.
 * <br>
 * La documentación de la API se encuentra en esta página:
 * https://www.compropago.com/documentacion/api
 */
class PagoComproPago {
	
	private static $llaves = array(
		"pruebas" => array(
			"publica" => "pk_test_80d95c311694798ad7",
			"privada" => "sk_test_71e1952169954d568c"
		),
		"produccion" => array(
			"publica" => "pk_live_7b9344d1698045ec90",
			"privada" => "sk_live_64d860169977149c9d"
		)
	);
	
	/**
	 * Genera una nueva orden de pago y regresa la respuesta de la operación.
	 * Los campos requeridos para generar la orden son: el ID de la orden (único),
	 * el nombre de la orden, su precio, el nombre del cliente, su correo y la
	 * sucursal donde se hará el pago.
	 * 
	 * @param Array $datos Los datos requeridos para generar la orden de pago.
	 * @return Array Un arreglo asociativo con los resultados de la operación.
	 */
	public static function generarOrden($datos) {
		$url = "https://" . self::$llaves["produccion"]["publica"] . ":@api.compropago.com/v1/charges";
		$options = array(
			"http" => array(
				"header" => "Content-type: application/x-www-form-urlencoded\r\n",
				"method" => "POST",
				"content" => http_build_query($datos)
			)
		);
		
		$context = stream_context_create($options);
		$json = file_get_contents($url, false, $context);
		$respuesta = json_decode($json, true);
		return $respuesta;
	}
}