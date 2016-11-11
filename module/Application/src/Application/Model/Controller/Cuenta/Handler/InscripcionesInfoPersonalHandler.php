<?php
namespace Application\Model\Controller\Cuenta\Handler;

use Application\Model\Dao\ConexionDao;

/**
 * Valida los datos del usuario en la primera parte de la inscripción,
 * donde se obtiene su información personal, modalidad, día y bloque, playera
 * y metodo de pago.
 *
 */
class InscripcionesInfoPersonalHandler {
	
	private static $FILTRO = array(
		"OK" => array(
			"code" => 0,
			"message" => "¡Datos validados correctamente!  Redirigiendo..."
		),
		"OK_EQUIPO" => array(
			"code" => 0,
			"message" => ""
		),
		"NOMBRE_VACIO" => array(
			"code" => 1,
			"message" => "Tu nombre es obligatorio."
		),
		"NOMBRE_CON_NUMEROS" => array(
			"code" => 2,
			"message" => "Tu nombre no puede contener números."
		),
		"PATERNO_VACIO" => array(
			"code" => 3,
			"message" => "Tu apellido paterno es obligatorio."
		),
		"PATERNO_CON_NUMEROS" => array(
			"code" => 4,
			"message" => "Tu apellido paterno no puede contener números."
		),
		"MATERNO_CON_NUMEROS" => array(
			"code" => 5,
			"message" => "Tu apellido materno no puede contener números."
		),
		"SEXO_INVALIDO" => array(
			"code" => 6,
			"message" => "Solo se acepta sexo masculino y femenino."
		),
		"FECHA_NACIMIENTO_VACIA" => array(
			"code" => 7,
			"message" => "Tu fecha de nacimiento es obligatoria."
		),
		"FECHA_NACIMIENTO_INVALIDA" => array(
			"code" => 8,
			"message" => "El formato de la fecha de nacimiento es inválido."
		),
		"FECHA_NACIMIENTO_MENOR" => array(
			"code" => 9,
			"message" => "Debes ser mayor de 18 años para registrarte."
		),
		"CORREO_VACIO" => array(
			"code" => 10,
			"message" => "Tu correo es obligatorio."
		),
		"CORREO_INVALIDO" => array(
			"code" => 11,
			"message" => "El formato de correo es inválido."
		),
		"BOLETIN_INVALIDO" => array(
			"code" => 12,
			"message" => "El campo de boletín debe ser 1 o 0."
		),
		"ESTADO_INVALIDO" => array(
			"code" => 13,
			"message" => "El estado debe ser parte de la república mexicana."
		),
		"RADIO_BUTTON_MODALIDAD_INVALIDO" => array(
			"code" => 14,
			"message" => "Solo están las modalidades Individual y Equipo."
		),
		"NOMBRE_EQUIPO_VACIO" => array(
			"code" => 15,
			"message" => "El nombre de tu equipo es obligatorio."
		),
		"NUMERO_INTEGRANTES_VACIO" => array(
			"code" => 16,
			"message" => "El número de integrantes es obligatorio."
		),
		"NUMERO_INTEGRANTES_INVALIDO" => array(
			"code" => 17,
			"message" => "El número de integrantes debe ser un número positivo."
		),
		"BLOQUE_INSUFICIENTE" => array(
			"code" => 18,
			"message" => "Este bloque no tiene los lugares suficientes para alojar a tu equipo."
		),
		"TALLA_INVALIDA" => array(
			"code" => 19,
			"message" => "Las playeras solo vienen en tallas Extra Chica, Chica, Mediana, Grande y Extra Grande."
		),
		"COLOR_INVALIDO" => array(
			"code" => 20,
			"message" => "Las playeras solo vienen en color azul, rojo, rosa y morado."
		),
		"METODO_DESCONOCIDO" => array(
			"code" => 21,
			"message" => "Ese método de pago no está disponible."
		),
		"SUCURSAL_DESCONOCIDA" => array(
			"code" => 22,
			"message" => "Esa sucursal no está disponible."
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
		$paterno = $params -> fromPost("paterno", "");
		$materno = $params -> fromPost("materno", "");
		$sexo = $params -> fromPost("sexo", "H");
		$fechaNacimiento = $params -> fromPost("fechaNac", "");
		$correo = $params -> fromPost("correo", "");
		$idEstado = $params -> fromPost("estado", 7);
		$boletin = $params -> fromPost("boletin", 0);
		
		$modalidad = $params -> fromPost("rdbModalidad", "individual");
		$nombreEquipo = $params -> fromPost("nombreEquipo", "");
		$noIntegrantes = $params -> fromPost("noIntegrantes", 1);
		
		$dia = $params -> fromPost("dia", "");
		$hit = $params -> fromPost("bloque", "");
		
		$tamanyo = $params -> fromPost("tamanyo", "1|S");
		$color = $params -> fromPost("color", "1|Azul");
		
		$metodoPago = $params -> fromPost("rdbMetodoPago", "tarjeta");
		$sucursal = $params -> fromPost("rdbSucursal", "");
		
		$idDetallesEvento = $params -> fromPost("idDetallesEvento", 0);
		$evento = (new ConexionDao())
				-> consultaGenerica("SELECT * FROM DetallesEvento WHERE idDetallesEvento = ?",
					array($idDetallesEvento))[0];
		
		return array(
			"usuario" => array(
				"nombre" => trim($nombre),
				"paterno" => trim($paterno),
				"materno" => trim($materno),
				"sexo" => $sexo,
				"fechaNacimiento" => $fechaNacimiento,
				"correo" => trim($correo),
				"idEstado" => $idEstado,
				"boletin" => $boletin
			),
			"equipo" => array(
				"modalidad" => $modalidad,
				"nombre" => trim($nombreEquipo),
				"noIntegrantes" => $noIntegrantes
			),
			"diaHit" => array(
				"dia" => array(
					"id" => explode("|", $dia)[0],
					"fecha" => explode("|", $dia)[1]
				),
				"hit" => array(
					"id" => explode("|", $hit)[0],
					"horario" => explode("|", $hit)[1],
					"lugaresRestantes" => explode("|", $hit)[2]
				)
			),
			"playera" => array(
				"tamanyo" => array(
					"id" => explode("|", $tamanyo)[0],
					"talla" => explode("|", $tamanyo)[1]
				),
				"color" => array(
					"id" => explode("|", $color)[0],
					"color" => explode("|", $color)[1]
				)
			),
			"metodoPago" => array(
				"metodo" => $metodoPago,
				"sucursal" => $sucursal,
				"precio" => ($modalidad === "individual")
					? $evento["precio"]
					: $evento["precio"] * $noIntegrantes
			),
			"evento" => $evento
		);
	}
	
	/**
	 * Recibe los parámetros POST de la página de InscripcionesEquipos y los agrega a un arreglo
	 * asociativo.
	 * 
	 * @param \Zend\Mvc\Controller\Plugin\Params $params Los parámetros POST.
	 * @return Array Arreglo asociativo con los parámetros POST del cliente.
	 */
	public static function obtenerDatosEquipoPost($params) {
		$nombre = $params -> fromPost("nombre", "");
		$paterno = $params -> fromPost("paterno", "");
		$materno = $params -> fromPost("materno", "");
		$sexo = $params -> fromPost("sexo", "H");
		$fechaNacimiento = $params -> fromPost("fechaNacimiento", "");
		$correo = $params -> fromPost("correo", "");
		$idEstado = $params -> fromPost("estado", 7);
		$boletin = $params -> fromPost("boletin", 0);
		$idEquipo = $params -> fromPost("idEquipo", 0);
		$idDetallesEvento = $params -> fromPost("idDetallesEvento", 0);
		$tamanyo = $params -> fromPost("tamanyo", 1);
		
		return array(
			"usuario" => array(
				"nombre" => trim($nombre),
				"paterno" => trim($paterno),
				"materno" => trim($materno),
				"sexo" => $sexo,
				"fechaNacimiento" => $fechaNacimiento,
				"correo" => trim($correo),
				"idEstado" => $idEstado,
				"boletin" => $boletin,
				"idEquipo" => $idEquipo,
				"idDetallesEvento" => $idDetallesEvento,
				"tamanyo" => $tamanyo
			)
		);
	}
	
	/**
	 * Recibe los parámetros POST de la página de TaquillasDatos y los agrega a un arreglo
	 * asociativo.
	 * 
	 * @param \Zend\Mvc\Controller\Plugin\Params $params Los parámetros POST.
	 * @return Array Arreglo asociativo con los parámetros POST del cliente.
	 */
	public static function obtenerDatosTaquillaPost($params) {
		$nombre = $params -> fromPost("nombre", "");
		$paterno = $params -> fromPost("paterno", "");
		$materno = $params -> fromPost("materno", "");
		$sexo = $params -> fromPost("sexo", "H");
		$fechaNacimiento = $params -> fromPost("fechaNacimiento", "");
		$correo = $params -> fromPost("correo", "");
		$idEstado = $params -> fromPost("estado", 7);
		$boletin = $params -> fromPost("boletin", 0);
		$dia = $params -> fromPost("dia", 0);
		$bloque = $params -> fromPost("bloque", 0);
		$idEquipo = $params -> fromPost("idEquipo", 0);
		$idDetallesEvento = $params -> fromPost("idDetallesEvento", 0);
		$tamanyo = $params -> fromPost("tamanyo", 1);
		
		return array(
			"usuario" => array(
				"nombre" => trim($nombre),
				"paterno" => trim($paterno),
				"materno" => trim($materno),
				"sexo" => $sexo,
				"fechaNacimiento" => $fechaNacimiento,
				"correo" => trim($correo),
				"idEstado" => $idEstado,
				"boletin" => $boletin,
				"dia" => $dia,
				"bloque" => $bloque,
				"tamanyo" => $tamanyo,
				"idEquipo" => $idEquipo,
				"idDetallesEvento" => $idDetallesEvento
			)
		);
	}
	
	/**
	 * Valida los datos personales del usuario.
	 * 
	 * @param Array $params Los datos obtenidos por medio de POST del usuario.
	 * @return Array Arreglo asociativo de acuerdo al resultado del filtro estático
	 * de esta clase, $FILTRO.
	 */
	public static function validarDatos($params) {
		$usuario = $params["usuario"];
		$equipo  = $params["equipo"];
		$diaHit = $params["diaHit"];
		$playera = $params["playera"];
		$metodoPago = $params["metodoPago"];
		
		if (empty($usuario["nombre"]))
			return self::$FILTRO["NOMBRE_VACIO"];
		else if (strcspn($usuario["nombre"], '0123456789') != strlen($usuario["nombre"]))
			return self::$FILTRO["NOMBRE_CON_NUMEROS"];
		else if (empty($usuario["paterno"]))
			return self::$FILTRO["PATERNO_VACIO"];
		else if (strcspn($usuario["paterno"], '0123456789') != strlen($usuario["paterno"]))
			return self::$FILTRO["PATERNO_CON_NUMEROS"];
		else if (strcspn($usuario["materno"], '0123456789') != strlen($usuario["materno"]))
			return self::$FILTRO["MATERNO_CON_NUMEROS"];
		else if (($usuario["sexo"] != "H") && ($usuario["sexo"] != "M"))
			return self::$FILTRO["SEXO_INVALIDO"];
		else if (empty($usuario["fechaNacimiento"]))
			return self::$FILTRO["FECHA_NACIMIENTO_VACIA"];
		else if (!self::validarFecha($usuario["fechaNacimiento"]))
			return self::$FILTRO["FECHA_NACIMIENTO_INVALIDA"];
		else if (time() < strtotime("+18 years", strtotime($usuario["fechaNacimiento"])))
			return self::$FILTRO["FECHA_NACIMIENTO_MENOR"];
		else if (empty($usuario["correo"]))
			return self::$FILTRO["CORREO_VACIO"];
		else if (!filter_var($usuario["correo"], FILTER_VALIDATE_EMAIL))
			return self::$FILTRO["CORREO_INVALIDO"];
		else if (($usuario["boletin"] != 1) && ($usuario["boletin"] != 0))
			return self::$FILTRO["BOLETIN_INVALIDO"];
		else if (($usuario["idEstado"] < 1) || ($usuario["idEstado"] > 32))
			return self::$FILTRO["ESTADO_INVALIDO"];
		else if (($equipo["modalidad"] !== "individual") && ($equipo["modalidad"] !== "equipo"))
			return self::$FILTRO["RADIO_BUTTON_MODALIDAD_INVALIDO"];
		else if (($equipo["modalidad"] === "equipo") && empty($equipo["nombre"]))
			return self::$FILTRO["NOMBRE_EQUIPO_VACIO"];
		else if (($equipo["modalidad"] === "equipo") && empty($equipo["noIntegrantes"]))
			return self::$FILTRO["NUMERO_INTEGRANTES_VACIO"];
		else if (($equipo["modalidad"] === "equipo") && (!is_numeric($equipo["noIntegrantes"]) || ((int)$equipo["noIntegrantes"]) < 1))
			return self::$FILTRO["NUMERO_INTEGRANTES_INVALIDO"];
		else if (($equipo["modalidad"] === "equipo") && ($equipo["noIntegrantes"] > $diaHit["hit"]["lugaresRestantes"]))
			return self::$FILTRO["BLOQUE_INSUFICIENTE"];
		else if (($playera["tamanyo"]["id"] < 1) || ($playera["tamanyo"]["id"] > 5))
			return self::$FILTRO["TALLA_INVALIDA"];
		else if (($playera["color"]["id"] < 1) || ($playera["color"]["id"] > 4))
			return self::$FILTRO["COLOR_INVALIDO"];
		else if (($metodoPago["metodo"] !== "tarjeta") && ($metodoPago["metodo"] !== "efectivo"))
			return self::$FILTRO["METODO_DESCONOCIDO"];
		else if (($metodoPago["metodo"] === "efectivo") && ($metodoPago["sucursal"] !== "OXXO")
			&& ($metodoPago["sucursal"] !== "SEVEN_ELEVEN") && ($metodoPago["sucursal"] !== "EXTRA")
			&& ($metodoPago["sucursal"] !== "ELEKTRA") && ($metodoPago["sucursal"] !== "COPPEL")
			&& ($metodoPago["sucursal"] !== "CHEDRAUI") && ($metodoPago["sucursal"] !== "FARMACIA_BENAVIDES")
			&& ($metodoPago["sucursal"] !== "FARMACIA_ESQUIVAR"))
			return self::$FILTRO["SUCURSAL_DESCONOCIDA"];
		else
			return self::$FILTRO["OK"];
	}
	
	/**
	 * Valida los datos personales del usuario en la página InscripcionesEquipos.
	 * 
	 * @param Array $params Los datos obtenidos por medio de POST del usuario.
	 * @return Array Arreglo asociativo de acuerdo al resultado del filtro estático
	 * de esta clase, $FILTRO.
	 */
	public static function validarDatosEquipo($params) {
		$usuario = $params["usuario"];
		
		if (empty($usuario["nombre"]))
			return self::$FILTRO["NOMBRE_VACIO"];
		else if (strcspn($usuario["nombre"], '0123456789') != strlen($usuario["nombre"]))
			return self::$FILTRO["NOMBRE_CON_NUMEROS"];
		else if (empty($usuario["paterno"]))
			return self::$FILTRO["PATERNO_VACIO"];
		else if (strcspn($usuario["paterno"], '0123456789') != strlen($usuario["paterno"]))
			return self::$FILTRO["PATERNO_CON_NUMEROS"];
		else if (strcspn($usuario["materno"], '0123456789') != strlen($usuario["materno"]))
			return self::$FILTRO["MATERNO_CON_NUMEROS"];
		else if (($usuario["sexo"] != "H") && ($usuario["sexo"] != "M"))
			return self::$FILTRO["SEXO_INVALIDO"];
		else if (empty($usuario["fechaNacimiento"]))
			return self::$FILTRO["FECHA_NACIMIENTO_VACIA"];
		else if (!self::validarFecha($usuario["fechaNacimiento"]))
			return self::$FILTRO["FECHA_NACIMIENTO_INVALIDA"];
		else if (time() < strtotime("+18 years", strtotime($usuario["fechaNacimiento"])))
			return self::$FILTRO["FECHA_NACIMIENTO_MENOR"];
		else if (empty($usuario["correo"]))
			return self::$FILTRO["CORREO_VACIO"];
		else if (!filter_var($usuario["correo"], FILTER_VALIDATE_EMAIL))
			return self::$FILTRO["CORREO_INVALIDO"];
		else if (($usuario["boletin"] != 1) && ($usuario["boletin"] != 0))
			return self::$FILTRO["BOLETIN_INVALIDO"];
		else if (($usuario["idEstado"] < 1) || ($usuario["idEstado"] > 32))
			return self::$FILTRO["ESTADO_INVALIDO"];
		else if (($usuario["tamanyo"] < 1) || ($usuario["tamanyo"] > 5))
			return self::$FILTRO["TALLA_INVALIDA"];
		else
			return self::$FILTRO["OK_EQUIPO"];
	}
	
	/**
	 * Valida los datos personales del usuario en la página TaquillasDatos.
	 * 
	 * @param Array $params Los datos obtenidos por medio de POST del usuario.
	 * @return Array Arreglo asociativo de acuerdo al resultado del filtro estático
	 * de esta clase, $FILTRO.
	 */
	public static function validarDatosTaquillas($params) {
		$usuario = $params["usuario"];
		
		if (empty($usuario["nombre"]))
			return self::$FILTRO["NOMBRE_VACIO"];
		else if (strcspn($usuario["nombre"], '0123456789') != strlen($usuario["nombre"]))
			return self::$FILTRO["NOMBRE_CON_NUMEROS"];
		else if (empty($usuario["paterno"]))
			return self::$FILTRO["PATERNO_VACIO"];
		else if (strcspn($usuario["paterno"], '0123456789') != strlen($usuario["paterno"]))
			return self::$FILTRO["PATERNO_CON_NUMEROS"];
		else if (strcspn($usuario["materno"], '0123456789') != strlen($usuario["materno"]))
			return self::$FILTRO["MATERNO_CON_NUMEROS"];
		else if (($usuario["sexo"] != "H") && ($usuario["sexo"] != "M"))
			return self::$FILTRO["SEXO_INVALIDO"];
		else if (empty($usuario["fechaNacimiento"]))
			return self::$FILTRO["FECHA_NACIMIENTO_VACIA"];
		else if (!self::validarFecha($usuario["fechaNacimiento"]))
			return self::$FILTRO["FECHA_NACIMIENTO_INVALIDA"];
		else if (time() < strtotime("+18 years", strtotime($usuario["fechaNacimiento"])))
			return self::$FILTRO["FECHA_NACIMIENTO_MENOR"];
		else if (empty($usuario["correo"]))
			return self::$FILTRO["CORREO_VACIO"];
		else if (!filter_var($usuario["correo"], FILTER_VALIDATE_EMAIL))
			return self::$FILTRO["CORREO_INVALIDO"];
		else if (($usuario["boletin"] != 1) && ($usuario["boletin"] != 0))
			return self::$FILTRO["BOLETIN_INVALIDO"];
		else if (($usuario["idEstado"] < 1) || ($usuario["idEstado"] > 32))
			return self::$FILTRO["ESTADO_INVALIDO"];
		else if (($usuario["tamanyo"] < 1) || ($usuario["tamanyo"] > 5))
			return self::$FILTRO["TALLA_INVALIDA"];
		else
			return self::$FILTRO["OK_EQUIPO"];
	}
	
	/**
	 * Valida que la fecha ingresada sea válida (en formato YYYY/MM/DD y mayor a 1990).
	 * @param string $fecha La fecha a validar.
	 * @return bool TRUE si la fecha es válida. De lo contrario, regresa FALSE.
	 */
	private static function validarFecha($fecha) {
		$arr = explode("-", $fecha);
		
		if (count($arr) === 3) {
			if (is_numeric($arr[0]) && is_numeric($arr[1]) && is_numeric($arr[2]) && ((int)$arr[0]) >= 1900)
				return checkdate($arr[1], $arr[2], $arr[0]);
		}
		
		return false;
	}
}
