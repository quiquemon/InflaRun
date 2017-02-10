<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use Application\Model\Controller\Cuenta\Handler\InscripcionHandler;
use Application\Model\Controller\Cuenta\Handler\InscripcionesHandler;
use Application\Model\Controller\Cuenta\Handler\InscripcionesInfoPersonalHandler;
use Application\Model\Controller\Cuenta\Handler\TarjetaHandler;
use Application\Model\Controller\Cuenta\Handler\EquiposHandler;
use Application\Model\Controller\Cuenta\Handler\TaquillasHandler;
use Application\Model\Controller\Cuenta\Handler\AdminHandler;
use Application\Model\Controller\Cuenta\Handler\UsuarioHandler;
use Application\Model\Dao\ConexionDao;

class CuentaController extends AbstractActionController {
	
	public function indexAction() {
		return $this -> redirect() -> toUrl("/application/cuenta/inscripciones");
	}
	
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
				return $this -> redirect() -> toUrl("/application/cuenta/inscripciones");
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
			return $this -> redirect() -> toUrl("/application/cuenta/inscripciones");
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
			return $this -> redirect() -> toUrl("/application/cuenta/inscripciones");
		}
		
		return new ViewModel();
	}
	
	public function inscripcionesvalidartarjetaAction() {
		$datos = TarjetaHandler::obtenerDatosPost($this -> params());
		$resultado = TarjetaHandler::validarDatos($datos);
		
		if ($resultado["code"] === 0) {
			$session = new Container("user");
			$session -> offsetGet("user")["datosBancarios"] = $datos;
		}
		
		return new JsonModel(array(
			"estatus" => $resultado["code"],
			"message" => $resultado["message"]
		));
	}
	
	public function inscripcionesconfirmardatosAction() {
		if (!(new Container("user")) -> offsetExists("user")) {
			return $this -> redirect() -> toUrl("/application/cuenta/inscripciones");
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
			return $this -> redirect() -> toUrl("/application/cuenta/inscripciones");
		
		try {
			$inscripcion = EquiposHandler::obtenerDatosEquipo($codigoCanje);
			return new ViewModel(array("inscripcion" => $inscripcion));
		} catch (\Exception $ex) {
			return $this -> redirect() -> toUrl("/application/cuenta/inscripciones");
		}
	}
	
	public function inscripcionesequiposfinalizarAction() {
		$datos = InscripcionesInfoPersonalHandler::obtenerDatosEquipoPost($this -> params());
		$resultado = InscripcionesInfoPersonalHandler::validarDatosEquipo($datos);
		
		if ($resultado["code"] === 0) {
			$r = InscripcionesHandler::integrarUsuarioAEquipo($datos["usuario"]);
			return new JsonModel($r);
		}
		
		return new JsonModel(array(
			"estatus" => $resultado["code"],
			"message" => $resultado["message"]
		));
	}
	
	public function taquillasAction() {
		try {
			$dao = new ConexionDao();
			$eventos = $dao -> consultaGenerica("SELECT * FROM DetallesEvento WHERE realizado = 0");
			return new ViewModel(array("eventos" => $eventos));
		} catch (\Exception $ex) {
			return $this -> redirect() -> toUrl("/application/cuenta/inscripciones");
		}
	}
	
	public function taquillasvalidarcodigoAction() {
		$codigoCanje = trim($this -> params() -> fromPost("codigoCanje", ""));
		$idDetallesEvento = $this -> params() -> fromPost("idDetallesEvento", 0);
		$resultado = TaquillasHandler::validarCodigo($codigoCanje, $idDetallesEvento);
		
		if ($resultado["estatus"] === 0) {
			try {
				$datos = TaquillasHandler::obtenerDatosEvento($codigoCanje, $idDetallesEvento);
				$session = new Container("user");
				$session -> offsetSet("user", $datos);
			} catch (\Exception $ex) {
				return new JsonModel(array(
					"estatus" => 1,
					"message" => $ex -> getMessage()
				));
			}
		}
		
		return new JsonModel($resultado);
	}
	
	public function taquillasdatosAction() {
		return (!(new Container("user")) -> offsetExists("user"))
			? $this -> redirect() -> toUrl("/application/cuenta/taquillas")
			: new ViewModel();
	}
	
	public function taquillasfinalizarAction() {
		$datos = InscripcionesInfoPersonalHandler::obtenerDatosTaquillaPost($this -> params());
		$resultado = InscripcionesInfoPersonalHandler::validarDatosTaquillas($datos);
		
		if ($resultado["code"] === 0) {
			$r = InscripcionesHandler::inscribirUsuarioPorTaquilla($datos["usuario"]);
			if ($r["estatus"] === 0) {
				(new Container("user")) -> getManager() -> getStorage() -> clear();
			}
			
			return new JsonModel($r);
		}
		
		return new JsonModel(array(
			"estatus" => $resultado["code"],
			"message" => $resultado["message"]
		));
	}
	
	public function cancelarinscripcionAction() {
		(new Container("user")) -> getManager() -> getStorage() -> clear();
		return $this -> redirect() -> toUrl("/application/cuenta/inscripciones");
	}
	
	/********************************************************************************
	 * FUNCIONES DEL ADMINISTRADOR
	 ********************************************************************************/
	
	public function adminloginAction() {
		if ($this -> getRequest() -> isPost()) {
			$pwdLogin = $this -> params() ->fromPost("pwdLogin", "");
			
			if ($pwdLogin === "InflaRun2016AdminPa$$") {
				(new Container("admin")) -> offsetSet("admin", "logged in");
				return $this -> redirect() -> toUrl("/application/cuenta/adminmain");
			} else {
				return new ViewModel(array("Error" => "La contraseña es incorrecta."));
			}
		}
		
		return new ViewModel();
	}
	
	public function adminmainAction() {
		if (!(new Container("admin")) -> offsetExists("admin"))
			return $this -> redirect() -> toUrl("/application/cuenta/adminlogin");
		
		$pagos = InscripcionesHandler::obtenerPagosPendientes();
		return new ViewModel(array("pagos" => $pagos));
	}
	
	public function adminaceptarpagoAction() {
		if (!(new Container("admin")) -> offsetExists("admin"))
			return $this -> redirect() -> toUrl("/application/cuenta/adminlogin");
		
		try {
			InscripcionesHandler::aceptarPagoEfectivo($this -> params() -> fromQuery("idPago"));
		} catch (\Exception $ex) {
			echo "<div class='alert alert-danger fade in'>"
				. "  <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>"
				. "  Error de sistema: {$ex -> getMessage()}"
				. "</div>";
		}
		
		return $this -> redirect() -> toUrl("/application/cuenta/adminmain");
	}
	
	public function adminrechazarpagoAction() {
		if (!(new Container("admin")) -> offsetExists("admin"))
			return $this -> redirect() -> toUrl("/application/cuenta/adminlogin");
		
		try {
			InscripcionesHandler::rechazarPagoEfectivo($this -> params() -> fromQuery("idPago"));
		} catch (\Exception $ex) {
			echo "<div class='alert alert-danger fade in'>"
				. "  <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>"
				. "  Error de sistema: {$ex -> getMessage()}"
				. "</div>";
		}
		
		return $this -> redirect() -> toUrl("/application/cuenta/adminmain");
	}
	
	/********************************************************************************************
	 *  MODIFICAR LOS SIGUIENTES ACTIONS PARA QUE FUNCIONEN CON LOS NUEVOS CAMBIOS DE LA
	 * APLICACIÓN.
	 ********************************************************************************************/
	
	public function adminusuariosAction(){
		if (!(new Container("admin")) -> offsetExists("admin"))
			return $this -> redirect() -> toUrl("/application/cuenta/adminlogin");
		
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
		return $this -> redirect() -> toUrl("/application/cuenta/adminusuarios");
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
			return $this -> redirect() -> toUrl("/application/cuenta/adminlogin");
		
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
		
		return $this -> redirect() -> toUrl("/application/cuenta/adminmoduser");
	}
	
	public function adminmodpassAction() {
		$pass = $this -> params() -> fromPost("password", "pass");
		$idUsuario = $this -> params() -> fromPost("idUsuarioPass", 0);
		$r = UsuarioHandler::modificarPassword($idUsuario, $pass);
		if ($r === 0)
			(new Container("admin")) -> offsetSet("message", "La contraseña se ha modificado exitosamente.");
		else
			(new Container("admin")) -> offsetSet("message", $r);
		
		return $this -> redirect() -> toUrl("/application/cuenta/adminmoduser");
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
		
		return $this -> redirect() -> toUrl("/application/cuenta/admincambiarhorario");
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
			return $this -> redirect() -> toUrl("/application/cuenta/adminlogin");
		}
		
		return $this -> redirect() -> toUrl("/application/cuenta/login");
	}
}
