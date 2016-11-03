<?php
namespace Application\Model\Controller\Cuenta\Pagos;

/**
 * Esta clase permite realizar los pagos de tarjeta con
 * el proveedor PayPal por medio de su servicio Web REST.
 * La versión de su API utilizada en esta clase es 
 * <br>
 * La documentación de la API se encuentra en esta página:
 * http://paypal.github.io/PayPal-PHP-SDK/ y https://github.com/paypal/PayPal-PHP-SDK
 */
class PagoPayPal {
	
	private static $endpoints = array(
		"pruebas" => "https://api.sandbox.paypal.com/v1",
		"produccion" => "https://api.paypal.com/v1"
	);
	
	private static $llaves = array(
		"pruebas" => array(
			"clientId" => "AQxHSZamossFvMYuVUJR1p341JqkLsDpnGqFO-GWtta-US4BsMXFCYnSKSLAcVFaSYo-ZkX6DeVX4JiG",
			"secret" => "EPhp8BYsId4vHFE7yungq8KGctcxJjyhEijKXF8rzmINVae_d-WOm1KK8e2XjSdr1dnQrBif-tCSvC6w"
		),
		"produccion" => array(
			"clientId" => "AWIqw7fdGMAj872Xv0qq4pUGIgMKIUub_uhDYd705LwIJgPGVLHghOqq6h6xRJRN4EtV-QJORJmta9rl",
			"secret" => "ECXJsDSuSMbyUx5gIpRWKTv2iqeYryMVBBqNNwd-cKfJlYRbzXmte-cp1DH8_VL7I7pM0DMgc9FtFeDc"
		)
	);
	
	/**
	 * Realiza el pago de la carrera y regresa el estado de la operación.
	 * 
	 * @param Array $datos La información de la tarjeta de crédito o débito del usuario.
	 * @return Array Un arreglo asociativo con los resultados de la operación.
	 */
	public static function realizarPago($datos) {
		$token = self::obtenerToken();
		$respuesta = self::realizarCargo($token, $datos);
		return $respuesta;
	}
	
	/**
	 * Obtiene un token de acceso para poder acceder a la API REST.
	 * @return Array Un arreglo asociativo con el resultado de la operación. Si ocurrió
	 * un error al ejecutar la consulta, regresa un arreglo con los detalles del error.
	 */
	private static function obtenerToken() {
		$client = self::$llaves["pruebas"]["clientId"];
		$secret = self::$llaves["pruebas"]["secret"];
		$base = self::$endpoints["pruebas"];
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, "$base/oauth2/token");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_USERPWD, "$client:$secret");
		curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
		
		$result = curl_exec($ch);
		return empty($result) ? curl_error($ch) : json_decode($result, true);
	}
	
	/**
	 * Realiza el cargo a la tarjeta de crédito o débito del usuario y regresa
	 * el resultado de la operación.
	 * 
	 * @param Array $token Arreglo asociativo con el token de seguridad de PayPal.
	 * @param Array $datos La información de la tarjeta de crédito o débito del usuario.
	 * @return Array Arreglo asociativo con el resultado de la operación.
	 */
	private static function realizarCargo($token, $datos) {
		#TODO: Implementación final con los $datos reales del usuario
		$base = self::$endpoints["pruebas"];
		$ch = curl_init();
		$json = array(
			"intent" => "sale",
			"payer" => array(
				"payment_method" => "credit_card",
				"funding_instruments" => array(
					array(
						"credit_card" => array(
							"number" => $datos["tarjeta"],
							"type" => $datos["tipoTarjeta"],
							"expire_month" => $datos["mes"],
							"expire_year" => $datos["anyo"],
							"cvv2" => $datos["cvv"],
							"first_name" => datos["nombre"],
							"last_name" => $datos["paterno"],
						)
					)
				)
			),
			"transactions" => array(
				array(
					"amount" => array(
						"total" => "0.05",#$datos["precio"]
						"currency" => "USD"
					),
					"description" => "inflarun_inscripcion_tarjeta"
				)
			)
		);

		
		curl_setopt($ch, CURLOPT_URL, "$base/payments/payment");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-type: application/json",
			"Authorization: {$token["token_type"]} {$token["access_token"]}"
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
		
		$result = curl_exec($ch);
		return empty($result) ? curl_error($ch) : json_decode($result, true);
	}
}