<?php
namespace Application\Model\Controller\Cuenta\Handler;

/**
 * Valida los datos de la tarjeta de crédito o débito del usuario.
 */
class TarjetaHandler {
	private static $FILTRO = array(
		"OK" => array(
			"code" => 0,
			"message" => "¡Datos validados correctamente! Redirigiendo..."
		),
		"NOMBRE_VACIO" => array(
			"code" => 1,
			"message" => "Tu nombre es obligatorio."
		),
		"APELLIDOS_VACIOS" => array(
			"code" => 2,
			"message" => "Tus apellidos son obligatorios."
		),
		"CORREO_VACIO" => array(
			"code" => 3,
			"message" => "Tu correo es obligatorio."
		),
		"CORREO_INVALIDO" => array(
			"code" => 4,
			"message" => "El formato de correo es inválido."
		),
		"TELEFONO_VACIO" => array(
			"code" => 5,
			"message" => "Tu número telefónico es obligatorio."
		),
		"CELULAR_VACIO" => array(
			"code" => 6,
			"message" => "Tu número de celular es obligatorio."
		),
		"NUMERO_INVALIDO" => array(
			"code" => 7,
			"message" => "El número debe tener 10 dígitos."
		),
		"NUMERO_TARJETA_VACIA" => array(
			"code" => 8,
			"message" => "El número de tarjeta es obligatorio."
		),
		"FECHA_EXPIRACION_INVALIDA" => array(
			"code" => 9,
			"message" => "La fecha de expiración de tu tarjeta es inválida."
		),
		"CVT_VACIO" => array(
			"code" => 10,
			"message" => "El código Cvv2 es obligatorio."
		),
		"CVT_INVALIDO" => array(
			"code" => 11,
			"message" => "El código Cvv2 debe estar compuesto de 3 o 4 dígitos."
		),
		"CALLE_VACIA" => array(
			"code" => 12,
			"message" => "Tu calle y número son obligatorios."
		),
		"COLONIA_VACIA" => array(
			"code" => 13,
			"message" => "Tu colonia es obligatoria."
		),
		"MUNICIPIO_VACIO" => array(
			"code" => 14,
			"message" => "Tu delegación o municipio es obligatorio."
		),
		"ESTADO_VACIO" => array(
			"code" => 15,
			"message" => "Tu estado es obligatorio."
		),
		"PAIS_VACIO" => array(
			"code" => 16,
			"message" => "Tu país es obligatorio."
		),
		"CP_VACIO" => array(
			"code" => 17,
			"message" => "Tu código postal es obligatorio."
		),
		"CP_INVALIDO" => array(
			"code" => 18,
			"message" => "Tu código postal debe constar de 5 dígitos."
		)
	);
	
	/**
	 * Recibe los parámetros POST del cliente y los agrega a un arreglo
	 * asociativo.
	 * 
	 * @param \Zend\Mvc\Controller\Plugin\Params $params Los parámetros POST.
	 * @return Array Arreglo asociativo con los parámetros POST del cliente.
	 */
	public static function obtenerDatosPost($params) {
		$nombre = $params -> fromPost("nombre", "");
		$apellidos = $params -> fromPost("apellidos", "");
		$correo = $params -> fromPost("correo", "");
		$telefono = $params -> fromPost("telefono", "");
		$celular = $params -> fromPost("celular", "");
		$numeroTarjeta = $params -> fromPost("numeroTarjeta", "");
		$mesExpiracion = $params -> fromPost("mesExpiracion", "");
		$anyoExpiracion = $params -> fromPost("anyoExpiracion", "");
		$cvt = $params -> fromPost("cvt", "");
		$calleyNumero = $params -> fromPost("calleyNumero", "");
		$colonia = $params -> fromPost("colonia", "");
		$municipio = $params -> fromPost("municipio", "");
		$estado = $params -> fromPost("estado", "");
		$pais = $params -> fromPost("pais", "");
		$cp = $params -> fromPost("cp", "");
		
		return array(
			"nombre" => $nombre,
			"apellidos" => $apellidos,
			"email" => $correo,
			"telefono" => $telefono,
			"celular" => $celular,
			"numeroTarjeta" => $numeroTarjeta,
			"mesExpiracion" => $mesExpiracion,
			"anyoExpiracion" => $anyoExpiracion,
			"cvt" => $cvt,
			"calleyNumero" => $calleyNumero,
			"colonia" => $colonia,
			"municipio" => $municipio,
			"estado" => $estado,
			"pais" => $pais,
			"cp" => $cp
		);
	}
	
	/**
	 * Valida los datos de la tarjeta del usuario.
	 * 
	 * @param Array $params Los datos obtenidos por medio de POST del usuario.
	 * @return Array Arreglo asociativo de acuerdo al resultado del filtro estático
	 * de esta clase, $FILTRO.
	 */
	public static function validarDatos($params) {
		if (empty($params["nombre"]))
			return self::$FILTRO["NOMBRE_VACIO"];
		else if (empty($params["apellidos"]))
			return self::$FILTRO["APELLIDOS_VACIOS"];
		else if (empty($params["email"]))
			return self::$FILTRO["CORREO_VACIO"];
		else if (!filter_var($params["email"], FILTER_VALIDATE_EMAIL))
			return self::$FILTRO["CORREO_INVALIDO"];
		else if (empty($params["telefono"]))
			return self::$FILTRO["TELEFONO_VACIO"];
		else if (empty($params["celular"]))
			return self::$FILTRO["CELULAR_VACIO"];
		else if (!preg_match("/^[0-9]{10}$/", $params["telefono"]) || !preg_match("/^[0-9]{10}$/", $params["celular"]))
			return self::$FILTRO["NUMERO_INVALIDO"];
		else if (empty($params["numeroTarjeta"]))
			return self::$FILTRO["NUMERO_TARJETA_VACIA"];
		else if (((int)$params["mesExpiracion"] < 1 || (int)$params["mesExpiracion"] > 12)
			|| ((int)$params["anyoExpiracion"] < (int)explode("-", date("y-m-d"))[0]))
			return self::$FILTRO["FECHA_EXPIRACION_INVALIDA"];
		else if (empty($params["cvt"]))
			return self::$FILTRO["CVT_VACIO"];
		else if (!preg_match("/^([0-9]{3})|([0-9]{4})$/", $params["cvt"]))
			return self::$FILTRO["CVT_INVALIDO"];
		else if (empty($params["calleyNumero"]))
			return self::$FILTRO["CALLE_VACIA"];
		else if (empty($params["colonia"]))
			return self::$FILTRO["COLONIA_VACIA"];
		else if (empty($params["municipio"]))
			return self::$FILTRO["MUNICIPIO_VACIO"];
		else if (empty($params["estado"]))
			return self::$FILTRO["ESTADO_VACIO"];
		else if (empty($params["pais"]))
			return self::$FILTRO["PAIS_VACIO"];
		else if (empty($params["cp"]))
			return self::$FILTRO["CP_VACIO"];
		else if (!preg_match("/^[0-9]{5}$/", $params["cp"]))
			return self::$FILTRO["CP_INVALIDO"];
		else
			return self::$FILTRO["OK"];
	}
}