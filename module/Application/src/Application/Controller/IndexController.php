<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Model\Pojo\Usuario;
use Application\Model\Dao\UsuarioDao;

class IndexController extends AbstractActionController {
	
	private $FILTRO = array(
		"OK" => array(
			"code" => 0,
			"message" => "¡Listo, te has registrado con éxito en InflaRun!"
				. " Accede a la pestaña Inscripciones con tu email y contraseña"
				. " para empezar tu inscripción a la carrera."
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
		"CORREO_VACIO" => array(
			"code" => 7,
			"message" => "Tu correo es obligatorio."
		),
		"CORREO_INVALIDO" => array(
			"code" => 8,
			"message" => "El formato de correo es inválido."
		),
		"PASS_VACIA" => array(
			"code" => 9,
			"message" => "La contraseña es obligatoria."
		),
		"PASS_DISTINTAS" => array(
			"code" => 10,
			"message" => "Las contraseñas no coinciden. Escríbelas de nuevo."
		),
		"BOLETIN_INVALIDO" => array(
			"code" => 11,
			"message" => "El campo de boletín debe ser 1 o 0."
		),
		"ESTADO_INVALIDO" => array(
			"code" => 12,
			"message" => "El estado es inválido."
		),
		"CORREO_EXISTENTE" => array(
			"code" => 13,
			"message" => "Ese correo ya fue registrado. Elige otro."
		),
		"ERROR_BD" => array(
			"code" => 14,
			"message" => "Lo sentimos, ocurrió un error dentro del sistema. Ya estamos arreglándolo."
		),
		"FECHA_NACIMIENTO_VACIA" => array(
			"code" => 15,
			"message" => "Tu fecha de nacimiento es obligatoria."
		),
		"FECHA_NACIMIENTO_INVALIDA" => array(
			"code" => 16,
			"message" => "El formato de la fecha de nacimiento es inválido."
		),
		"FECHA_NACIMIENTO_MENOR" => array(
			"code" => 17,
			"message" => "Debes ser mayor de 18 años para registrarte."
		)
	);
    
	public function indexAction() {
        return new ViewModel();
    }
	
	public function queesinflarunAction() {
		return new ViewModel();
	}
	
	public function fundacionAction() {
		return new ViewModel();
	}
	
	public function preguntasfrecuentesAction() {
		return new ViewModel();
	}
	
	public function rutaAction() {
		return new ViewModel();
	}
	
	public function convocatoriaAction() {
		return new ViewModel();
	}
	
	public function suscribirseAction() {
		$params = $this -> obtenerParametros();
		$resultado = $this -> filtrarSuscripcion($params);
		
		if ($resultado["code"] === $this -> FILTRO["OK"]["code"]) {
			$dao = new UsuarioDao();
			try {
				$result = $dao -> consultaGenerica("SELECT * FROM Usuario WHERE correo = ?", array($params["correo"]));
				if (empty($result)) {
					$dao -> insertar((new Usuario())
						-> setNombre($params["nombre"])
						-> setAPaterno($params["paterno"])
						-> setAMaterno($params["materno"])
						-> setSexo($params["sexo"])
						-> setFechaNacimiento($params["fechaNacimiento"])
						-> setFechaRegistro(date("y-m-d"))
						-> setCorreo($params["correo"])
						-> setPassword(password_hash($params["pwd"], PASSWORD_DEFAULT))
						-> setRecibeCorreos($params["boletin"])
						-> setIdEstado($params["idEstado"]));
				} else
					$resultado = $this -> FILTRO["CORREO_EXISTENTE"];
			} catch (\Exception $ex) {
				$resultado = $this -> FILTRO["ERROR_BD"];
			}
		}
		
		return new JsonModel(array(
			"code" => $resultado["code"],
			"message" => $resultado["message"]
		));
	}
	
	/**
	 * Obtiene los parametros POST del formulario de inscripción.
	 * @return Array Un arreglo asociativo con los parámetros POST del formulario.
	 */
	private function obtenerParametros() {
		$nombre = null !== $this -> params() -> fromPost("nombre") ? $this -> params() -> fromPost("nombre") : "";
		$paterno = null !== $this -> params() -> fromPost("paterno") ? $this -> params() -> fromPost("paterno") : "";
		$materno = null !== $this -> params() -> fromPost("materno") ? $this -> params() -> fromPost("materno") : "";
		$sexo = null !== $this -> params() -> fromPost("sexo") ? $this -> params() -> fromPost("sexo") : "H";
		$fechaNacimiento = null !== $this -> params() -> fromPost("fechaNacimiento") ? $this -> params() -> fromPost("fechaNacimiento") : "";
		$correo = null !== $this -> params() -> fromPost("email") ? $this -> params() -> fromPost("email") : "";
		$pwd = null !== $this -> params() -> fromPost("pwd") ? $this -> params() -> fromPost("pwd") : "";
		$pwdRepetida = null !== $this -> params() -> fromPost("pwdRepetida") ? $this -> params() -> fromPost("pwdRepetida") : "";
		$boletin = null !== $this -> params() -> fromPost("boletin") ? $this -> params() -> fromPost("boletin") : 1;
		$idEstado = null !== $this -> params() -> fromPost("estado") ? $this -> params() -> fromPost("estado") : 1;
		
		return array(
			"nombre" => $nombre,
			"paterno" => $paterno,
			"materno" => $materno,
			"sexo" => $sexo,
			"fechaNacimiento" => $fechaNacimiento,
			"correo" => $correo,
			"pwd" => $pwd,
			"pwdRepetida" => $pwdRepetida,
			"boletin" => $boletin,
			"idEstado" => $idEstado
		);
	}
	
	/**
	 * Filtra la información sobre el usuario al momento del registro en la base de datos.
	 * 
	 * @param Array $params Arreglo que incluye los parámetros POST del formulario.
	 * @return Array Arreglo que contiene el código y el mensaje del resultado validado, de
	 * acuerdo al arreglo de este Action, $FILTRO.
	 */
	private function filtrarSuscripcion($params) {
		if (empty($params["nombre"]))
			return $this -> FILTRO["NOMBRE_VACIO"];
		else if (strcspn($params["nombre"], '0123456789') != strlen($params["nombre"]))
			return $this -> FILTRO["NOMBRE_CON_NUMEROS"];
		else if (empty($params["paterno"]))
			return $this -> FILTRO["PATERNO_VACIO"];
		else if (strcspn($params["paterno"], '0123456789') != strlen($params["paterno"]))
			return $this -> FILTRO["PATERNO_CON_NUMEROS"];
		else if (strcspn($params["materno"], '0123456789') != strlen($params["materno"]))
			return $this -> FILTRO["MATERNO_CON_NUMEROS"];
		else if (($params["sexo"] != "H") && ($params["sexo"] != "M"))
			return $this -> FILTRO["SEXO_INVALIDO"];
		else if (empty($params["fechaNacimiento"]))
			return $this -> FILTRO["FECHA_NACIMIENTO_VACIA"];
		else if (!$this -> validarFecha($params["fechaNacimiento"]))
			return $this -> FILTRO["FECHA_NACIMIENTO_INVALIDA"];
		else if (time() < strtotime("+18 years", strtotime($params["fechaNacimiento"])))
			return $this -> FILTRO["FECHA_NACIMIENTO_MENOR"];
		else if (empty($params["correo"]))
			return $this -> FILTRO["CORREO_VACIO"];
		else if (!filter_var($params["correo"], FILTER_VALIDATE_EMAIL))
			return $this -> FILTRO["CORREO_INVALIDO"];
		else if (empty($params["pwd"]))
			return $this -> FILTRO["PASS_VACIA"];
		else if ($params["pwd"] != $params["pwdRepetida"])
			return $this -> FILTRO["PASS_DISTINTAS"];
		else if (($params["boletin"] != 1) && ($params["boletin"] != 0))
			return $this -> FILTRO["BOLETIN_INVALIDO"];
		else if (($params["idEstado"] < 1) || ($params["idEstado"] > 32))
			return $this -> FILTRO["ESTADO_INVALIDO"];
		else
			return $this -> FILTRO["OK"];
	}
	
	/**
	 * Valida que la fecha ingresada sea válida (en formato YYYY/MM/DD y mayor a 1990).
	 * @param string $fecha La fecha a validar.
	 * @return bool TRUE si la fecha es válida. De lo contrario, regresa FALSE.
	 */
	private function validarFecha($fecha) {
		$arr = explode("/", $fecha);
		
		if (count($arr) === 3) {
			if (is_numeric($arr[0]) && is_numeric($arr[1]) && is_numeric($arr[2]) && ((int)$arr[0]) >= 1900)
				return checkdate($arr[1], $arr[2], $arr[0]);
		}
		
		return false;
	}
}