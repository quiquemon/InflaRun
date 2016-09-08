<?php
namespace Application\Model\Controller\Cuenta\Handler;

use Application\Model\Dao\UsuarioDao;

/**
 * Clase con métodos utilitario para la manipulación de la información
 * de los usuarios.
 */
class UsuarioHandler {
	
	private static $FILTRO = array(
		"OK" => array(
			"code" => 0,
			"message" => "OK"
		),
		"NOMBRE_VACIO" => array(
			"code" => 1,
			"message" => "El nombre es obligatorio."
		),
		"NOMBRE_CON_NUMEROS" => array(
			"code" => 2,
			"message" => "El nombre no puede contener números."
		),
		"PATERNO_VACIO" => array(
			"code" => 3,
			"message" => "El apellido paterno es obligatorio."
		),
		"PATERNO_CON_NUMEROS" => array(
			"code" => 4,
			"message" => "El apellido paterno no puede contener números."
		),
		"MATERNO_CON_NUMEROS" => array(
			"code" => 5,
			"message" => "El apellido materno no puede contener números."
		),
		"SEXO_INVALIDO" => array(
			"code" => 6,
			"message" => "Solo se acepta sexo masculino y femenino."
		),
		"CORREO_VACIO" => array(
			"code" => 7,
			"message" => "El correo es obligatorio."
		),
		"CORREO_INVALIDO" => array(
			"code" => 8,
			"message" => "El formato de correo es inválido."
		),
		"PASS_VACIA" => array(
			"code" => 9,
			"message" => "La contraseña es obligatoria."
		),
		"BOLETIN_INVALIDO" => array(
			"code" => 10,
			"message" => "El campo de boletín debe ser 1 o 0."
		),
		"ESTADO_INVALIDO" => array(
			"code" => 11,
			"message" => "El estado es inválido."
		),
		"CORREO_EXISTENTE" => array(
			"code" => 12,
			"message" => "Ese correo ya fue registrado. Elige otro."
		),
		"ERROR_BD" => array(
			"code" => 13,
			"message" => "Lo sentimos, ocurrió un error dentro del sistema. Ya estamos arreglándolo."
		),
		"FECHA_NACIMIENTO_VACIA" => array(
			"code" => 14,
			"message" => "La fecha de nacimiento es obligatoria."
		),
		"FECHA_NACIMIENTO_INVALIDA" => array(
			"code" => 15,
			"message" => "El formato de la fecha de nacimiento es inválido."
		),
		"FECHA_NACIMIENTO_MENOR" => array(
			"code" => 16,
			"message" => "Debes ser mayor de 18 años para registrarte."
		)
	);
	
	/**
	 * Obtiene los parámetros GET con la información
	 * a registrar o modificar del usuario.
	 * 
	 * @param Zend\Mvc\Controller\AbstractController $params Los parámetros que se obtienen del formulario.
	 * @return Array Arreglo asociativo con los parámetros de la petición.
	 */
	public static function obtenerParametrosGet($params) {
		return self::obtenerParametros($params, "get");
	}
	
	/**
	 * Obtiene los parámetros POST con la información
	 * a registrar o modificar del usuario.
	 * 
	 * @param Zend\Mvc\Controller\AbstractController $params Los parámetros que se obtienen del formulario.
	 * @return Array Arreglo asociativo con los parámetros de la petición.
	 */
	public static function obtenerParametrosPost($params) {
		return self::obtenerParametros($params, "post");
	}
	
	/**
	 * Filtra la información del formulario al momento de registrar o
	 * modificar la información del usuario.
	 * 
	 * @param Array $params Arreglo asociativo con los parámetros de la petición.
	 * @return Array Arreglo asociativo con el código y mensaje del resultado del filtro.
	 */
	public static function filtrarParametros($params) {
		if (empty($params["nombre"]))
			return self::$FILTRO["NOMBRE_VACIO"];
		else if (strcspn($params["nombre"], '0123456789') != strlen($params["nombre"]))
			return self::$FILTRO["NOMBRE_CON_NUMEROS"];
		else if (empty($params["paterno"]))
			return self::$FILTRO["PATERNO_VACIO"];
		else if (strcspn($params["paterno"], '0123456789') != strlen($params["paterno"]))
			return self::$FILTRO["PATERNO_CON_NUMEROS"];
		else if (strcspn($params["materno"], '0123456789') != strlen($params["materno"]))
			return self::$FILTRO["MATERNO_CON_NUMEROS"];
		else if (($params["sexo"] != "H") && ($params["sexo"] != "M"))
			return self::$FILTRO["SEXO_INVALIDO"];
		else if (empty($params["fechaNacimiento"]))
			return self::$FILTRO["FECHA_NACIMIENTO_VACIA"];
		else if (!self::validarFecha($params["fechaNacimiento"]))
			return self::$FILTRO["FECHA_NACIMIENTO_INVALIDA"];
		else if (time() < strtotime("+18 years", strtotime($params["fechaNacimiento"])))
			return self::$FILTRO["FECHA_NACIMIENTO_MENOR"];
		else if (empty($params["email"]))
			return self::$FILTRO["CORREO_VACIO"];
		else if (!filter_var($params["email"], FILTER_VALIDATE_EMAIL))
			return self::$FILTRO["CORREO_INVALIDO"];
		else if (($params["boletin"] != 1) && ($params["boletin"] != 0))
			return self::$FILTRO["BOLETIN_INVALIDO"];
		else if (($params["idEstado"] < 1) || ($params["idEstado"] > 32))
			return self::$FILTRO["ESTADO_INVALIDO"];
		else
			return self::$FILTRO["OK"];
	}
	
	/**
	 * Modifica la información del usuario con los parámetros dados.
	 * 
	 * @param Array $params Arreglo asociativo con los parámetros de la petición.
	 * @return int|string Regresa 0 si el registro fue modificado con éxito. De lo
	 * contrario, regresa el mensaje de error.
	 */
	public static function modificarUsuario($params) {
		$dao = new UsuarioDao();
		$sql = "UPDATE Usuario SET nombre = ?, aPaterno = ?, aMaterno = ?, correo = ?,"
			. " sexo = ?, fechaNacimiento = ?, recibeCorreos = ?, idEstado = ?"
			. " WHERE idUsuario = ?";
		
		try {
			$dao -> beginTransaction();
			$dao -> sentenciaGenerica($sql, array(
				$params["nombre"],
				$params["paterno"],
				$params["materno"],
				$params["email"],
				$params["sexo"],
				$params["fechaNacimiento"],
				$params["boletin"],
				$params["idEstado"],
				$params["idUsuario"]
			));
			$dao -> commit();
			return 0;
		} catch (\Exception $ex) {
			$dao -> rollback();
			return $ex -> getMessage();
		}
	}
	
	/**
	 * Obtiene los parámetros GET y POST con la información
	 * a registrar o modificar del usuario.
	 * 
	 * @param Zend\Mvc\Controller\AbstractController $params Los parámetros que se obtienen del formulario.
	 * @param string $method El nómbre del método ("get", "post").
	 * @return Array Arreglo asociativo con los parámetros de la petición.
	 */
	private static function obtenerParametros($params, $method) {
		if ($method == "get") {
			return array(
				"idUsuario" => $params -> fromQuery("idUsuario", ""),
				"nombre" => $params -> fromQuery("nombre", ""),
				"paterno" => $params -> fromQuery("paterno", ""),
				"materno" => $params -> fromQuery("materno", ""),
				"sexo" => $params -> fromQuery("sexo", "H"),
				"fechaNacimiento" => $params -> fromQuery("fechaNac", ""),
				"email" => $params -> fromQuery("email", ""),
				"idEstado" => $params -> fromQuery("estado", 1),
				"boletin" => $params -> fromQuery("boletin", 1)
			);
		} else {
			return array(
				"idUsuario" => $params -> fromPost("idUsuario", ""),
				"nombre" => $params -> fromPost("nombre", ""),
				"paterno" => $params -> fromPost("paterno", ""),
				"materno" => $params -> fromPost("materno", ""),
				"sexo" => $params -> fromPost("sexo", "H"),
				"fechaNacimiento" => $params -> fromPost("fechaNac", ""),
				"email" => $params -> fromPost("email", ""),
				"idEstado" => $params -> fromPost("estado", 1),
				"boletin" => $params -> fromPost("boletin", 1)
			);
		}
	}
	
	/**
	 * Valida que la fecha ingresada sea válida (en formato YYYY/MM/DD y mayor a 1990).
	 * @param string $fecha La fecha a validar.
	 * @return bool TRUE si la fecha es válida. De lo contrario, regresa FALSE.
	 */
	private static function validarFecha($fecha) {
		$arr = explode("/", $fecha);
		
		if (count($arr) === 3) {
			if (is_numeric($arr[0]) && is_numeric($arr[1]) && is_numeric($arr[2]) && ((int)$arr[0]) >= 1900)
				return checkdate($arr[1], $arr[2], $arr[0]);
		}
		
		return false;
	}
}