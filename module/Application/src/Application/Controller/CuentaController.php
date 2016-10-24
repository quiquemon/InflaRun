<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use Application\Model\Controller\Cuenta\Handler\InscripcionHandler;
use Application\Model\Controller\Cuenta\Handler\InscripcionesHandler;
use Application\Model\Controller\Cuenta\Handler\InscripcionesInfoPersonalHandler;
use Application\Model\Controller\Cuenta\Handler\EquiposHandler;
use Application\Model\Controller\Cuenta\Handler\AdminHandler;
use Application\Model\Controller\Cuenta\Handler\UsuarioHandler;
use Application\Model\Dao\ConexionDao;
use Application\Model\Dao\EquipoDao;

class CuentaController extends AbstractActionController {
	
	private $FILTRO_MODALIDAD = array(
		"CODIGO_VACIO" => array(
			"code" => 6,
			"message" => "El código de inscripción es obligatorio."
		),
		"CODIGO_INVALIDO" => array(
			"code" => 7,
			"message" => "El código de inscripción es inválido."
		),
		"CODIGO_UTILIZADO" => array(
			"code" => 8,
			"message"=> "Lo sentimos, ese código ya ha sido utilizado."
		),
		"CODIGO_EN_ESPERA" => array(
			"code" => 9,
			"message" => "Estamos a la espera de la confirmación del pago de tu equipo."
				. " Cuando la tengamos, te habilitaremos este código."
		)
	);
	
	private $FILTRO_DATOS_BANCARIOS = array(
		"OK" => array(
			"code" => 1,
			"message" => "Ok"
		),
		"NOMBRE_VACIO" => array(
			"code" => 2,
			"message" => "Tu nombre es obligatorio."
		),
		"APELLIDOS_VACIOS" => array(
			"code" => 3,
			"message" => "Tus apellidos son obligatorios."
		),
		"CORREO_VACIO" => array(
			"code" => 4,
			"message" => "Tu correo es obligatorio."
		),
		"CORREO_INVALIDO" => array(
			"code" => 5,
			"message" => "El formato de correo es inválido."
		),
		"TELEFONO_VACIO" => array(
			"code" => 6,
			"message" => "Tu número telefónico es obligatorio."
		),
		"CELULAR_VACIO" => array(
			"code" => 7,
			"message" => "Tu número de celular es obligatorio."
		),
		"NUMERO_INVALIDO" => array(
			"code" => 8,
			"message" => "El número debe tener 10 dígitos."
		),
		"NUMERO_TARJETA_VACIA" => array(
			"code" => 9,
			"message" => "El número de tarjeta es obligatorio."
		),
		"FECHA_EXPIRACION_INVALIDA" => array(
			"code" => 10,
			"message" => "La fecha de expiración de tu tarjeta es inválida."
		),
		"CVT_VACIO" => array(
			"code" => 11,
			"message" => "El código Cvv2 es obligatorio."
		),
		"CVT_INVALIDO" => array(
			"code" => 12,
			"message" => "El código Cvv2 debe estar compuesto de 3 o 4 dígitos."
		),
		"CALLE_VACIA" => array(
			"code" => 13,
			"message" => "Tu calle y número son obligatorios."
		),
		"COLONIA_VACIA" => array(
			"code" => 14,
			"message" => "Tu colonia es obligatoria."
		),
		"MUNICIPIO_VACIO" => array(
			"code" => 15,
			"message" => "Tu delegación o municipio es obligatorio."
		),
		"ESTADO_VACIO" => array(
			"code" => 16,
			"message" => "Tu estado es obligatorio."
		),
		"PAIS_VACIO" => array(
			"code" => 17,
			"message" => "Tu país es obligatorio."
		),
		"CP_VACIO" => array(
			"code" => 18,
			"message" => "Tu código postal es obligatorio."
		),
		"CP_INVALIDO" => array(
			"code" => 19,
			"message" => "Tu código postal debe constar de 5 dígitos."
		)
	);
	
	/***************************************************************
	 * FUNCIONES DE LA VERSIÓN 1.1 DE INFLARUN
	 * 
	 * Estas funciones serán con las que se trabajará en las
	 * siguientes versiones de la aplicación. Se pondrán en
	 * funcionamiento a partir de la carrera de León a finales del
	 * 2016.
	 ***************************************************************/
	
	public function inscripcionesAction() {
		try {
			$dao = new ConexionDao();
			$eventos = $dao -> consultaGenerica("SELECT * FROM DetallesEvento WHERE realizado = 0 ORDER BY idDetallesEvento DESC");
			for ($i = 0; $i < count($eventos); $i++) {
				$sql = "SELECT DISTINCT de.* FROM DiaEvento de, DiaHit dh WHERE de.idDiaEvento = dh.idDiaEvento AND"
					. " de.idDetallesEvento = ? AND dh.lugaresRestantes > 0";
				$dias = $dao -> consultaGenerica($sql, array($eventos[$i]["idDetallesEvento"]));
				$eventos[$i]["dias"] = empty($dias) ? array() : $dias[0];
				$eventos[$i]["estado"] = $dao -> consultaGenerica("SELECT * FROM Estado WHERE idEstado = ?",
					array($eventos[$i]["idEstado"]))[0];
			}
			
			return new ViewModel(array("eventos" => $eventos));
		} catch (\Exception $ex) {
			$eventos = array();
			return new ViewModel(array(
				"Alert" => array(
					"code" => 1,
					"message" => $ex -> getMessage()
				)
			));
		}
	}
	
	public function inscripcionesdatosAction() {
		try {
			$idDetallesEvento = $this -> params() -> fromQuery("idDetallesEvento", 0);
			$dao = new ConexionDao();
			$evento = $dao -> consultaGenerica("SELECT * FROM DetallesEvento WHERE idDetallesEvento = ?",
				array($idDetallesEvento));
			
			if (empty($evento)) {
				return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/inscripciones");
			} else {
				$sql = "SELECT DISTINCT de.* FROM DiaEvento de, DiaHit dh WHERE de.idDiaEvento = dh.idDiaEvento AND"
					. " de.idDetallesEvento = ? AND dh.lugaresRestantes > 0";
				$dias = $dao -> consultaGenerica($sql, array($idDetallesEvento));
				
				if (!empty($dias)) {
					$dias = $dias[0];
					$dias["hits"] = $dao -> consultaGenerica("SELECT * FROM DiaHit WHERE idDiaEvento = ?", array(
						$dias["idDiaEvento"]
					));
				}
				
				$evento[0]["dias"] = $dias;
				$playeras = array();
				$playeras["tamanyo"] = $dao -> consultaGenerica("SELECT * FROM TamPlayera");
			}
		} catch (\Exception $ex) {
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/inscripciones");
		}
		
		return new ViewModel(array("evento" => $evento[0], "playeras" => $playeras));
	}
	
	public function inscripcionesdatosinfopersonalAction() {
		$datos = InscripcionesInfoPersonalHandler::obtenerDatosPost($this -> params());
		$resultado = InscripcionesInfoPersonalHandler::validarDatos($datos);
		
		if ($resultado["code"] === 0) {
			$session = new Container("user");
			$session -> getManager() -> getStorage() -> clear();
			$session -> offsetSet("user", $datos);
		}
		
		return new JsonModel(array(
			"estatus" => $resultado["code"],
			"message" => $resultado["message"]
		));
	}
	
	public function inscripcionesformtarjetaAction() {
		if (!(new Container("user")) -> offsetExists("user")) {
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/inscripciones");
		}
		
		return new ViewModel();
	}
	
	public function inscripcionesconfirmardatosAction() {
		if (!(new Container("user")) -> offsetExists("user")) {
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/inscripciones");
		}
		
		return new ViewModel();
	}
	
	public function inscripcionesfinalizarAction() {
		$session = new Container("user");
		if (!$session -> offsetExists("user")) {
			return new JsonModel(array(
				"estatus" => 1,
				"message" => "Tu sesión ha expirado. Por favor, intenta inscribirte de nuevo."
			));
		}
		
		$resultado = InscripcionesHandler::inscribirUsuario($session -> offsetGet("user"));
		
		if ($resultado["estatus"] === 0) {
			$session -> getManager() -> getStorage() -> clear();
		}
		
		return new JsonModel($resultado);
	}
	
	public function inscripcionesequiposAction() {
		$codigoCanje = $this -> params() -> fromQuery("codigoCanje", "");
		
		if (empty($codigoCanje))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/inscripciones");
		
		try {
			$inscripcion = EquiposHandler::obtenerDatosEquipo($codigoCanje);
			return new ViewModel(array("inscripcion" => $inscripcion));
		} catch (\Exception $ex) {
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/inscripciones");
		}
	}
	
	public function inscripcionesequiposfinalizarAction() {
		
		return new JsonModel();
	}
	
	public function cancelarinscripcionAction() {
		(new Container("user")) -> getManager() -> getStorage() -> clear();
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/inscripciones");
	}
	
	
	/***************************************************************
	 * FUNCIONES DE LA VERSIÓN 1 DE INFLARUN
	 * 
	 * Eventualmente estas funciones serán reemplazadas por las
	 * que se escribirán arriba de este aviso.
	 ***************************************************************/
	public function indexAction() {
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/inscripciones");
	}
	
	public function elegirmodalidadAction() {
		if (!(new Container("usuario")) -> offsetExists("usuario"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
		
		if ($this -> getRequest() -> isPost()) {
			$params = $this -> obtenerParametrosModalidad();
			$resultado = $this -> filtrarParametrosModalidad($params);
			
			if ($resultado["code"] === $this -> FILTRO_MODALIDAD["OK"]["code"]) {
				$session = new Container("usuario");
				$session -> offsetSet("modalidad", $params);
				
				if ($params["rdbModalidad"] === "codigo") {
					try {
						$dao = new EquipoDao();
						$equipo = $dao
							-> consultaGenerica("SELECT * FROM Equipo WHERE codigoCanje = ?", array($params["codigoInscripcion"]));
						if (empty($equipo)) {
							$session -> offsetUnset("modalidad");
							return new ViewModel(array("Error" => $this -> FILTRO_MODALIDAD["CODIGO_INVALIDO"]["message"]));
						} else {
							$idEquipo = $equipo[0]["idEquipo"];
							$noIntegrantes = $equipo[0]["noIntegrantes"];
							$integrantes = $dao
								-> consultaGenerica("SELECT * FROM UsuarioEquipo WHERE idEquipo = ?", array($idEquipo));
							
							if (!empty($integrantes)) {
								if ($noIntegrantes <= count($integrantes)) {
									return new ViewModel(array("Error" => $this -> FILTRO_MODALIDAD["CODIGO_UTILIZADO"]["message"]));
								} else {
									$pago = $dao -> consultaGenerica("SELECT * FROM Pago WHERE idEquipo = ?", array($idEquipo))[0];
									if ($pago["estatus"] == 0) {
										return new ViewModel(array("Error" => $this -> FILTRO_MODALIDAD["CODIGO_EN_ESPERA"]["message"]));
									}
								}
							}
							
							$session -> offsetSet("equipoCodigo", $equipo[0]);
							if (!$equipo[0]["esCortesia"]) {
								$diaHit["hit"] = $dao -> consultaGenerica("SELECT * FROM DiaHit WHERE idDiaHit = ?", array(
									$equipo[0]["idDiaHit"])
								)[0];
								$diaHit["dia"] = $dao -> consultaGenerica("SELECT * FROM DiaEvento WHERE idDiaEvento = ?", array(
									$diaHit["hit"]["idDiaEvento"])
								)[0];
								$session -> offsetSet("diaHit", $diaHit);
								return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/confirmardatos");
							}
						}
					} catch (\Exception $ex) {
						$session -> offsetUnset("modalidad");
						return new ViewModel(array("Error" => $this -> FILTRO["ERROR_BD"]["message"]));
					}
				}
				
				return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/elegirbloque");
			}
			
			return new ViewModel(array("Error" => $resultado["message"]));
		}
		
		return new ViewModel();
	}
	
	public function formulariotarjetaAction() {
		if (!(new Container("usuario")) -> offsetExists("usuario"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
		
		if ($this -> getRequest() -> isPost()) {
			$params = $this -> obtenerParametrosBancarios();
			$resultado = $this -> filtrarParametrosBancarios($params);
			
			if ($resultado["code"] === $this -> FILTRO_DATOS_BANCARIOS["OK"]["code"]) {
				(new Container("usuario")) -> offsetSet("datosBancarios", $params);
				return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/confirmardatos");
			}
			
			return new ViewModel(array("Error" => $resultado));
		}
		
		return new ViewModel();
	}
	
	/********************************************************************************
	 * FUNCIONES DEL ADMINISTRADOR
	 ********************************************************************************/
	
	public function adminloginAction() {
		if ($this -> getRequest() -> isPost()) {
			$pwdLogin = $this -> params() ->fromPost("pwdLogin", "");
			
			if ($pwdLogin === "InflaRun2016AdminPa$$") {
				(new Container("admin")) -> offsetSet("admin", "logged in");
				return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminmain");
			} else {
				return new ViewModel(array("Error" => "La contraseña es incorrecta."));
			}
		}
		
		return new ViewModel();
	}
	
	public function adminmainAction() {
		if (!(new Container("admin")) -> offsetExists("admin"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminlogin");
		
		return new ViewModel();
	}
	
	public function adminaceptarpagoAction() {
		if (!(new Container("admin")) -> offsetExists("admin"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminlogin");
		
		try {
			InscripcionHandler::aceptarPagoEfectivo($this -> params() -> fromQuery("idPago"));
		} catch (\Exception $ex) {
			(new Container("admin")) -> offsetSet("message", $this -> FILTRO["ERROR_BD"]["message"]);
		}
		
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminmain");
	}
	
	public function adminrechazarpagoAction() {
		if (!(new Container("admin")) -> offsetExists("admin"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminlogin");
		
		try {
			InscripcionHandler::rechazarPagoEfectivo($this -> params() -> fromQuery("idPago"));
		} catch (\Exception $ex) {
			(new Container("admin")) -> offsetSet("message", $this -> FILTRO["ERROR_BD"]["message"]);
		}
		
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminmain");
	}
	
	public function adminusuariosAction(){
		if (!(new Container("admin")) -> offsetExists("admin"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminlogin");
		
		return new ViewModel();
	}
	
	public function adminusuariosgetusuarioinfoAction() {
		$correo = trim($this -> params() -> fromQuery("correo", ""));
		$idDetallesEvento = $this -> params() -> fromQuery("idDetallesEvento", 1);
		$usuario = AdminHandler::obtenerInformacionUsuario($correo, $idDetallesEvento);
		return new JsonModel($usuario);
	}
	
	public function adminusuariosreenviarcorreoAction() {
		$correo = $this -> params() -> fromQuery("correo", "");
		$idDetallesEvento = $this -> params() -> fromQuery("idDetallesEvento", 1);
		$usuario = AdminHandler::obtenerInformacionUsuario($correo, $idDetallesEvento);
		AdminHandler::reenviarCorreo($usuario);
		(new Container("admin")) -> offsetSet("message", "El correo se ha enviado exitosamente.");
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminusuarios");
	}
	
	public function adminusuariosreenviarcorreoajaxAction() {
		$correo = $this -> params() -> fromQuery("correo", "");
		$idDetallesEvento = $this -> params() -> fromQuery("idDetallesEvento", 1);
		$usuario = AdminHandler::obtenerInformacionUsuario($correo, $idDetallesEvento);
		$r = AdminHandler::reenviarCorreo($usuario);
		return new JsonModel(array(
			"estatus" => ($r === true) ? 0 : 1,
			"message" => ($r === true) ? "El mensaje se envió exitosamente." : $r
		));
	}
	
	public function adminmoduserAction() {
		if (!(new Container("admin")) -> offsetExists("admin"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminlogin");
		
		return new ViewModel();
	}
	
	public function adminmodusergetinfoAction() {
		$correo = $this -> params() -> fromQuery("correo", "");
		$usuario = AdminHandler::obtenerInfoPersonalUsuario($correo);
		return new JsonModel($usuario);
	}
	
	public function adminmodinfoAction() {
		$params = UsuarioHandler::obtenerParametrosPost($this -> params());
		$result = UsuarioHandler::filtrarParametros($params);
		if ($result["code"] === 0) {
			$r = UsuarioHandler::modificarUsuario($params);
			if ($r === 0)
				(new Container("admin")) -> offsetSet("message", "La información se ha modificado exitosamente.");
			else
				(new Container("admin")) -> offsetSet("message", $r);
		} else {
			(new Container("admin")) -> offsetSet("message", $result["message"]);
		}
		
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminmoduser");
	}
	
	public function adminmodpassAction() {
		$pass = $this -> params() -> fromPost("password", "pass");
		$idUsuario = $this -> params() -> fromPost("idUsuarioPass", 0);
		$r = UsuarioHandler::modificarPassword($idUsuario, $pass);
		if ($r === 0)
			(new Container("admin")) -> offsetSet("message", "La contraseña se ha modificado exitosamente.");
		else
			(new Container("admin")) -> offsetSet("message", $r);
		
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminmoduser");
	}
	
	public function admincambiarhorarioAction() {
		return new ViewModel();
	}
	
	public function admincambiarhorariogetinfoAction() {
		$correo = $this -> params() -> fromQuery("correo", "");
		$idDetallesEvento = $this -> params() -> fromQuery("idDetallesEvento", "");
		$info = UsuarioHandler::obtenerInformacionHorario($correo, $idDetallesEvento);
		return new JsonModel($info);
	}
	
	public function admincambiarhorariopostAction() {
		$idEquipo = $this -> params() -> fromPost("idEquipo");
		$hit = $this -> params() -> fromPost("hit");
		$r = UsuarioHandler::cambiarHorario($idEquipo, $hit);
		if ($r === 0)
			(new Container("admin")) -> offsetSet("message",
				"El equipo se ha cambiado de horario con éxito. No olvides reenviar el comprobante a los demás integrantes.");
		else
			(new Container("admin")) -> offsetSet("message", $r);
		
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/admincambiarhorario");
	}
	
	public function adminregistromanualAction() {
		return new ViewModel();
	}
	
	public function adminregistrogetinfoAction() {
		$folio = $this -> params() -> fromQuery("folio", "");
		$info = InscripcionHandler::obtenerInfoGrupoRegistroManual($folio);
		return new JsonModel($info);
	}
	
	public function adminregistromanualcorreoAction() {
		$correo = $this -> params() -> fromPost("correo", "");
		$folio = $this -> params() -> fromPost("folio", "");
		$r = InscripcionHandler::agregarUsuarioAGrupo($correo, $folio);
		return new JsonModel($r);
	}
	
	public function adminlogoutAction() {
		$session = new Container("admin");
		
		if ($session -> offsetExists("admin")) {
			$session -> offsetUnset("admin");
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminlogin");
		}
		
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
	}
	
	/**
	 * *************************************************************************************************
	 * TERMINAN FUNCIONES DEL ADMINISTRADOR
	 * *************************************************************************************************
	 */
	
	/**
	 * *************************************************************************************
	 * FUNCIONES DE UTILIDAD
	 * 
	 * Eventualmente estas funciones serán reemplazadas por las clases de utilidad
	 * que se creen para la versión 1.1 de la aplicación.
	 * *************************************************************************************/
	
	/**
	 * Obtiene los parametros POST del formulario de datos bancarios.
	 * @return Array Un arreglo asociativo con los parámetros POST del formulario.
	 */
	private function obtenerParametrosBancarios() {
		$nombre = null !== $this -> params() -> fromPost("nombre") ? $this -> params() -> fromPost("nombre") : "";
		$apellidos = null !== $this -> params() -> fromPost("apellidos") ? $this -> params() -> fromPost("apellidos") : "";
		$email = null !== $this -> params() -> fromPost("email") ? $this -> params() -> fromPost("email") : "";
		$telefono = null !== $this -> params() -> fromPost("telefono") ? $this -> params() -> fromPost("telefono") : "";
		$celular = null !== $this -> params() -> fromPost("celular") ? $this -> params() -> fromPost("celular") : "";
		$numeroTarjeta = null !== $this -> params() -> fromPost("numeroTarjeta") ? $this -> params() -> fromPost("numeroTarjeta") : "";
		$mesExpiracion = null !== $this -> params() -> fromPost("mesExpiracion") ? $this -> params() -> fromPost("mesExpiracion") : "";
		$anyoExpiracion = null !== $this -> params() -> fromPost("anyoExpiracion") ? $this -> params() -> fromPost("anyoExpiracion") : "";
		$cvt = null !== $this -> params() -> fromPost("cvt") ? $this -> params() -> fromPost("cvt") : "";
		$calleyNumero = null !== $this -> params() -> fromPost("calleyNumero") ? $this -> params() -> fromPost("calleyNumero") : "";
		$colonia = null !== $this -> params() -> fromPost("colonia") ? $this -> params() -> fromPost("colonia") : "";
		$municipio = null !== $this -> params() -> fromPost("municipio") ? $this -> params() -> fromPost("municipio") : "";
		$estado = null !== $this -> params() -> fromPost("estado") ? $this -> params() -> fromPost("estado") : "";
		$pais = null !== $this -> params() -> fromPost("pais") ? $this -> params() -> fromPost("pais") : "";
		$cp = null !== $this -> params() -> fromPost("cp") ? $this -> params() -> fromPost("cp") : "";
		
		return array(
			"nombre" => $nombre,
			"apellidos" => $apellidos,
			"email" => $email,
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
	 * Filtra los parámetros del formulario de Datos Bancarios.
	 * 
	 * @param Array $params Arreglo que incluye los parámetros POST del formulario.
	 * @return Array Arreglo que contiene el código y el mensaje del resultado validado, de
	 * acuerdo al arreglo de este Action, $FILTRO_DATOS_BANCARIOS.
	 */
	private function filtrarParametrosBancarios($params) {
		if (empty($params["nombre"]))
			return $this -> FILTRO_DATOS_BANCARIOS["NOMBRE_VACIO"];
		else if (empty($params["apellidos"]))
			return $this -> FILTRO_DATOS_BANCARIOS["APELLIDOS_VACIOS"];
		else if (empty($params["email"]))
			return $this -> FILTRO_DATOS_BANCARIOS["CORREO_VACIO"];
		else if (!filter_var($params["email"], FILTER_VALIDATE_EMAIL))
			return $this -> FILTRO_DATOS_BANCARIOS["CORREO_INVALIDO"];
		else if (empty($params["telefono"]))
			return $this -> FILTRO_DATOS_BANCARIOS["TELEFONO_VACIO"];
		else if (empty($params["celular"]))
			return $this -> FILTRO_DATOS_BANCARIOS["CELULAR_VACIO"];
		else if (!preg_match("/^[0-9]{10}$/", $params["telefono"]) || !preg_match("/^[0-9]{10}$/", $params["celular"]))
			return $this -> FILTRO_DATOS_BANCARIOS["NUMERO_INVALIDO"];
		else if (empty($params["numeroTarjeta"]))
			return $this -> FILTRO_DATOS_BANCARIOS["NUMERO_TARJETA_VACIA"];
		else if (((int)$params["mesExpiracion"] < 1 || (int)$params["mesExpiracion"] > 12)
			|| ((int)$params["anyoExpiracion"] < (int)explode("-", date("y-m-d"))[0]))
			return $this -> FILTRO_DATOS_BANCARIOS["FECHA_EXPIRACION_INVALIDA"];
		else if (empty($params["cvt"]))
			return $this -> FILTRO_DATOS_BANCARIOS["CVT_VACIO"];
		else if (!preg_match("/^([0-9]{3})|([0-9]{4})$/", $params["cvt"]))
			return $this -> FILTRO_DATOS_BANCARIOS["CVT_INVALIDO"];
		else if (empty($params["calleyNumero"]))
			return $this -> FILTRO_DATOS_BANCARIOS["CALLE_VACIA"];
		else if (empty($params["colonia"]))
			return $this -> FILTRO_DATOS_BANCARIOS["COLONIA_VACIA"];
		else if (empty($params["municipio"]))
			return $this -> FILTRO_DATOS_BANCARIOS["MUNICIPIO_VACIO"];
		else if (empty($params["estado"]))
			return $this -> FILTRO_DATOS_BANCARIOS["ESTADO_VACIO"];
		else if (empty($params["pais"]))
			return $this -> FILTRO_DATOS_BANCARIOS["PAIS_VACIO"];
		else if (empty($params["cp"]))
			return $this -> FILTRO_DATOS_BANCARIOS["CP_VACIO"];
		else if (!preg_match("/^[0-9]{5}$/", $params["cp"]))
			return $this -> FILTRO_DATOS_BANCARIOS["CP_INVALIDO"];
		else
			return $this -> FILTRO_DATOS_BANCARIOS["OK"];
	}
	
	/**
	 * Filtra las contraseñas del usuario al momento del registro en la base de datos.
	 * 
	 * @param Array $params Arreglo que incluye los parámetros POST del formulario.
	 * @return Array Arreglo que contiene el código y el mensaje del resultado validado, de
	 * acuerdo al arreglo de este Action, $FILTRO.
	 */
	private function filtrarPassword($params) {
		$pwdHash = (new Container("usuario")) -> offsetGet("usuario") -> getPassword();
		
		if (empty($params["pwdActual"]))
			return $this -> FILTRO["PASS_ACTUAL_VACIA"];
		else if (!password_verify($params["pwdActual"], $pwdHash))
			return $this -> FILTRO["PASS_ACTUAL_ERRONEA"];
		else if (empty($params["pwdNueva"]))
			return $this -> FILTRO["PASS_NUEVA_VACIA"];
		else if ($params["pwdNueva"] != $params["pwdNuevaConf"])
			return $this -> FILTRO["PASS_DISTINTAS"];
		else
			return $this -> FILTRO["OK"];
	}
	
	public function ejemploAction() {
		return new ViewModel();
	}
}