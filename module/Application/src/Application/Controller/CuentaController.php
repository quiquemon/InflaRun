<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use Application\Model\Controller\Cuenta\Handler\DiaHitHandler;
use Application\Model\Controller\Cuenta\Handler\PagosHandler;
use Application\Model\Controller\Cuenta\Handler\InscripcionHandler;
use Application\Model\Controller\Cuenta\Handler\AdminHandler;
use Application\Model\Controller\Cuenta\Handler\UsuarioHandler;
use Application\Model\Dao\EquipoDao;
use Application\Model\Dao\DiaHitDao;
use Application\Model\Dao\UsuarioDao;
use Application\Model\Pojo\Usuario;
use Application\Model\Pojo\DiaHit;

class CuentaController extends AbstractActionController {
	
	private $FILTRO = array(
		"OK" => array(
			"code" => 0,
			"message" => "Tu información se ha guardado correctamente."
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
		"CORREO_VACIO" => array(
			"code" => 7,
			"message" => "El correo es obligatorio."
		),
		"CORREO_INVALIDO" => array(
			"code" => 8,
			"message" => "El formato de correo es inválido."
		),
		"PASS_ACTUAL_VACIA" => array(
			"code" => 9,
			"message" => "Ingresa tu contraseña actual."
		),
		"PASS_ACTUAL_ERRONEA" => array(
			"code" => 10,
			"message" => "Tu contraseña actual es errónea. Intenta de nuevo."
		),
		"PASS_NUEVA_VACIA" => array(
			"code" => 11,
			"message" => "Ingresa tu nueva contraseña."
		),
		"PASS_DISTINTAS" => array(
			"code" => 12,
			"message" => "La nueva contraseña y su confirmación no coinciden."
		),
		"BOLETIN_INVALIDO" => array(
			"code" => 13,
			"message" => "El campo de boletín debe ser 1 o 0."
		),
		"CORREO_EXISTENTE" => array(
			"code" => 14,
			"message" => "Ese correo ya fue registrado. Elige otro."
		),
		"ERROR_BD" => array(
			"code" => 15,
			"message" => "Lo sentimos, ocurrió un error dentro del sistema. Ya estamos arreglándolo."
		)
	);
	
	private $FILTRO_MODALIDAD = array(
		"OK" => array(
			"code" => 1,
			"message" => "Ok"
		),
		"RADIO_BUTTON_INVALIDO" => array(
			"code" => 2,
			"message" => "Elija una opción válida."
		),
		"NOMBRE_EQUIPO_VACIO" => array(
			"code" => 3,
			"message" => "El nombre de tu equipo es obligatorio."
		),
		"NUMERO_INTEGRANTES_VACIO" => array(
			"code" => 4,
			"message" => "El número de integrantes es obligatorio."
		),
		"NUMERO_INTEGRANTES_INVALIDO" => array(
			"code" => 5,
			"message" => "El número de integrantes debe ser un número positivo."
		),
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
	
	private $FILTRO_BLOQUE = array(
		"OK" => array(
			"code" => 1,
			"message" => "Ok"
		),
		"BLOQUE_INSUFICIENTE" => array(
			"code" => 2,
			"message" => "Lo sentimos, este bloque no tiene los lugares suficientes para alojar a tu equipo."
		),
		"LUGARES_AGOTADOS" => array(
			"code" => 3,
			"message" => "Lo sentimos, todos los lugares se han acabado."
		)
	);
	
	private $FILTRO_METODO_PAGO = array(
		"OK" => array(
			"code" => 1,
			"message" => "Ok"
		),
		"METODO_DESCONOCIDO" => array(
			"code" => 2,
			"message" => "Ese método de pago no está disponible."
		),
		"SUCURSAL_DESCONOCIDA" => array(
			"code" => 3,
			"message" => "Esa sucursal no está disponible."
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
	
	public function indexAction() {
		$session = new Container("usuario");
		
		if (!$session -> offsetExists("usuario"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
		
		return new ViewModel();
	}
	
	public function loginAction() {
		if ($this -> getRequest() -> isPost()) {
			try {
				$result = $this -> validarLogin();
				if ($result !== null) {
					(new Container("usuario")) -> offsetSet("usuario", $result);
					return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/index");
				} else
					return new ViewModel(array(
						"Error" => "Tu correo o contraseña están equivocados. Intenta de nuevo."
					));
			} catch (\Exception $ex) {
				return new ViewModel(array(
					"Error" => "Ocurrió un error al iniciar sesión."
				));
			}
		}
		
		return new ViewModel();
	}
	
	public function logoutAction() {
		(new Container("usuario")) -> getManager() -> getStorage() -> clear("usuario");
		$this -> redirect() -> toUrl("/InflaRun/public/application/index");
	}
	
	public function modinfoAction() {
		if (!(new Container("usuario")) -> offsetExists("usuario"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
		
		return new ViewModel();
	}
	
	public function paginaeventoAction() {
		if (!(new Container("usuario")) -> offsetExists("usuario"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
		
		$idDetallesEvento = $this -> params() -> fromQuery("id");
		return new ViewModel(array("idDetallesEvento" => $idDetallesEvento));
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
	
	public function elegirbloqueAction() {
		if (!(new Container("usuario")) -> offsetExists("usuario"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
		
		if ($this -> getRequest() -> isPost()) {
			$params = $this -> obtenerParametrosBloque();
			
			if (!empty($params["dia"]) && !empty($params["hit"])) {
				$session = new Container("usuario");
				$modalidad = $session -> offsetGet("modalidad");
				
				if ($modalidad["rdbModalidad"] === "equipo") {
					$noIntegrantes = $modalidad["noIntegrantes"];
					try {
						$dao = new DiaHitDao();
						$hit = $dao -> buscar((new DiaHit()) -> setIdDiaHit($params["hit"]));
						if ($noIntegrantes > $hit -> getLugaresRestantes())
							return new ViewModel(array("Error" => $this -> FILTRO_BLOQUE["BLOQUE_INSUFICIENTE"]));
					} catch (\Exception $ex) {
						return new ViewModel(array("Error" => $this -> FILTRO["ERROR_BD"]));
					}
				}
				
				try {
					$dao = new DiaHitDao();
					$dia = $dao -> consultaGenerica("SELECT * FROM DiaEvento WHERE idDiaEvento = ?", array($params["dia"]))[0];
					$hit = $dao -> consultaGenerica("SELECT * FROM DiaHit WHERE idDiaHit = ?", array($params["hit"]))[0];
					$session -> offsetSet("diaHit", array(
						"dia" => $dia,
						"hit" => $hit
					));
					
					if ($session -> offsetExists("equipoCodigo"))
						return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/confirmardatos");
					
					return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/metodopago");
				} catch (\Exception $ex) {
					return new ViewModel(array("Error" => $this -> FILTRO["ERROR_BD"]));
				}
			}
			
			return new ViewModel(array("Error" => $this -> FILTRO_BLOQUE["LUGARES_AGOTADOS"]));
		}
		
		return new ViewModel();
	}
	
	public function metodopagoAction() {
		if (!(new Container("usuario")) -> offsetExists("usuario"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
		
		if ($this -> getRequest() -> isPost()) {
			$params = $this -> obtenerParametrosMetodoPago();
			$resultado = $this -> filtrarParametrosMetodoPago($params);
			
			if ($resultado["code"] === $this -> FILTRO_METODO_PAGO["OK"]["code"]) {
				(new Container("usuario")) -> offsetSet("metodoPago", $params);
				if ($params["rdbMetodoPago"] === "tarjeta")
					return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/formulariotarjeta");
				else
					return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/confirmardatos");
			}
			
			return new ViewModel(array("Error" => $resultado));
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
	
	public function confirmardatosAction() {
		if (!(new Container("usuario")) -> offsetExists("usuario"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
		
		if ($this -> getRequest() -> isPost()) {
			$session = new Container("usuario");
			$usuario = $session -> offsetGet("usuario");
			$modalidad = $session -> offsetGet("modalidad");
			$inscripcionDetalles = $session -> offsetGet("inscripcionDetalles");
			$monto = $inscripcionDetalles["detallesEvento"]
				-> getPrecio() * ($modalidad["rdbModalidad"] === "equipo" ? (int)$modalidad["noIntegrantes"] : 1);
			$diaHit = $session -> offsetGet("diaHit");
			
			if ($session -> offsetExists("ERROR_PAGO"))
				$session -> offsetUnset("ERROR_PAGO");
			
			try {
				if ($modalidad["rdbModalidad"] !== "codigo") {
					$numero = ($modalidad["rdbModalidad"] === "individual") ? 1 : $modalidad["noIntegrantes"];
					DiaHitHandler::decrementarLugaresRestantes($diaHit["hit"]["idDiaHit"], $numero);
				}
			} catch (\Exception $ex) {
				return new ViewModel(array(
					"Error" => array(
						"message" => "Lo sentimos, los lugares de este bloque se han agotado."
					)
				));
			}
			
			try {
				if ($modalidad["rdbModalidad"] !== "codigo") {
					$metodoPago = $session -> offsetGet("metodoPago");
					
					if ($metodoPago["rdbMetodoPago"] === "tarjeta") {
						$datosBancarios = $session -> offsetGet("datosBancarios");
						$datosBancarios["monto"] = $monto;
						$resultado = PagosHandler::realizarPagoTarjeta($datosBancarios);

						if ($resultado["WebServices_Transacciones"]["transaccion"]["autorizado"] == 0) {
							DiaHitHandler::incrementarLugaresRestantes($diaHit["hit"]["idDiaHit"], $numero);
							$session -> offsetSet("ERROR_PAGO", "Lo sentimos, ocurrió un error al procesar tu pago."
								. " Asegúrate de haber ingresado correctamente todos tus datos."
								. " Si tu tarjeta no tiene los fondos suficientes o ha sido bloqueada por tu banco no podrás hacer el pago.");
						} else {
							InscripcionHandler::registrarInscripcion($resultado, $metodoPago, $diaHit,
								$modalidad, $monto, $usuario, $inscripcionDetalles);
						}
					} else {
						$orderId = "" . bin2hex(openssl_random_pseudo_bytes(12));
						$resultado = PagosHandler::realizarPagoEfectivo(array(
							"order_id" => $orderId,
							"product" => "inflarun_inscripcion_efectivo",
							"amount" => "$monto",
							"store_code" => "{$metodoPago["rdbSucursal"]}",
							"customer" => "{$usuario -> getNombre()}",
							"email" => "{$usuario -> getCorreo()}"
						));

						if ($resultado["error"] == 1) {
							DiaHitHandler::incrementarLugaresRestantes($diaHit["hit"]["idDiaHit"], $numero);
							$session -> offsetSet("ERROR_PAGO", "Lo sentimos, ocurrió un error al procesar tu pago."
								. " Es posible que la sucursal no esté disponible; puedes elegir otra. Recuerda que todas las sucursales"
								. " tienen un monto máximo en el pago que aceptan.");
						} else {
							InscripcionHandler::registrarInscripcion($resultado, $metodoPago, $diaHit,
								$modalidad, $monto, $usuario, $inscripcionDetalles, $orderId);
						}
					}
					
					$session -> offsetSet("resultado", $resultado);
				} else {
					InscripcionHandler::registrarCodigoInscripcion($modalidad["codigoInscripcion"],
						$usuario, $inscripcionDetalles, $diaHit);
				}
				
				$this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/finalizarinscripcion");
			} catch (\Exception $ex) {
				$this -> FILTRO["ERROR_BD"]["message"] = $ex -> getMessage();
				return new ViewModel(array("Error" => $this -> FILTRO["ERROR_BD"]));
			}
		}
		
		return new ViewModel();
	}
	
	public function finalizarinscripcionAction() {
		if (!(new Container("usuario")) -> offsetExists("usuario"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
		
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
	
	public function adminaceptarpagadasAction() {
		if (!(new Container("admin")) -> offsetExists("admin"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminlogin");
		
		try {
			AdminHandler::aceptarOrdenesPagadas();
		} catch (\Exception $ex) {
			(new Container("admin")) -> offsetSet("message", $this -> FILTRO["ERROR_BD"]["message"]);
		}
		
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminmain");
	}
	
	public function adminrechazarexpiradasAction() {
		if (!(new Container("admin")) -> offsetExists("admin"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminlogin");
		
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminmain");
	}
	
	public function adminlogoutAction() {
		$session = new Container("admin");
		
		if ($session -> offsetExists("admin")) {
			$session -> offsetUnset("admin");
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/adminlogin");
		}
		
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
	}
	
	public function modificarinformacionAction() {
		if (!(new Container("usuario")) -> offsetExists("usuario"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
		
		$params = $this -> obtenerParametros();
		$resultado = $this -> filtrarSuscripcion($params);
		
		if ($resultado["code"] === $this -> FILTRO["OK"]["code"]) {
			$sql = "UPDATE Usuario SET nombre = ?, aPaterno = ?, aMaterno = ?, correo = ?, recibeCorreos = ?"
				. " WHERE idUsuario = ?";
			$usuario = (new Container("usuario")) -> offsetGet("usuario");
			$idUsuario = $usuario -> getIdUsuario();
			$correo = $usuario -> getCorreo();
			$dao = new UsuarioDao();
			try {
				$r = $dao -> consultaGenerica("SELECT * FROM Usuario WHERE correo = ?", array($correo));
				if ($r[0]["idUsuario"] === $idUsuario) {
					$dao -> sentenciaGenerica($sql, array(
						$params["nombre"],
						$params["paterno"],
						$params["materno"],
						$params["correo"],
						$params["boletin"],
						$idUsuario
					));
					$newUsuario = $dao -> buscar((new Usuario()) -> setIdUsuario($idUsuario));
					(new Container("usuario")) -> offsetSet("message", $resultado["message"]);
					(new Container("usuario")) -> offsetSet("usuario", $newUsuario);
					return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/index");
				}
				
				$resultado = $this -> FILTRO["CORREO_EXISTENTE"];
			} catch (\Exception $ex) {
				$resultado = $this -> FILTRO["ERROR_BD"];
			}
		}
		
		(new Container("usuario")) -> offsetSet("personalMessage", $resultado["message"]);
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/modinfo");
	}
	
	public function modpasswordAction() {
		if (!(new Container("usuario")) -> offsetExists("usuario"))
			return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/login");
		
		$params = $this -> obtenerParametros();
		$resultado = $this -> filtrarPassword($params);
		
		if ($resultado["code"] === $this -> FILTRO["OK"]["code"]) {
			$sql = "UPDATE Usuario SET password = ? WHERE idUsuario = ?";
			$idUsuario = (new Container("usuario")) -> offsetGet("usuario") -> getIdUsuario();
			$dao = new UsuarioDao();
			try {
				$dao -> sentenciaGenerica($sql, array(
					password_hash($params["pwdNueva"], PASSWORD_DEFAULT),
					$idUsuario
				));
				$newUsuario = $dao -> buscar((new Usuario()) -> setIdUsuario($idUsuario));
				(new Container("usuario")) -> offsetSet("message", $resultado["message"]);
				(new Container("usuario")) -> offsetSet("usuario", $newUsuario);
				return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/index");
			} catch (\Exception $ex) {
				$resultado = $this -> FILTRO["ERROR_BD"];
			}	
		}
		
		(new Container("usuario")) -> offsetSet("passwordMessage", $resultado["message"]);
		return $this -> redirect() -> toUrl("/InflaRun/public/application/cuenta/modinfo");
	}
	
	
	/**
	 * *************************************************************************************
	 * FUNCIONES DE UTILIDAD
	 * *************************************************************************************
	 */
	
	
	/**
	 * Valida los parámetros de correo y contraseña al iniciar sesión
	 * 
	 * @return (Usuario|null) Si el usuario existe en la base de datos regresa un POJO con sus datos. De lo
	 * contrario regresa null.
	 */
	private function validarLogin() {
		$email = null !== $this -> params() -> fromPost("emailLogin") ? $this -> params() -> fromPost("emailLogin") : "";
		$pwd = null !== $this -> params() -> fromPost("pwdLogin") ? $this -> params() -> fromPost("pwdLogin") : "";
		$dao = new UsuarioDao();
		$result = $dao -> consultaGenerica("SELECT * FROM Usuario WHERE correo = ?", array($email));
		
		if (!empty($result)) {
			$dbHash = $result[0]["password"];
			if (password_verify($pwd, $dbHash)) {
				return (new Usuario())
					-> setIdUsuario($result[0]["idUsuario"])
					-> setNombre($result[0]["nombre"])
					-> setAPaterno($result[0]["aPaterno"])
					-> setAMaterno($result[0]["aMaterno"])
					-> setCorreo($result[0]["correo"])
					-> setPassword($result[0]["password"])
					-> setSexo($result[0]["sexo"])
					-> setFechaNacimiento($result[0]["fechaNacimiento"])
					-> setFechaRegistro($result[0]["fechaRegistro"])
					-> setRecibeCorreos($result[0]["recibeCorreos"])
					-> setIdEstado($result[0]["idEstado"]);
			}
		}
		
		return null;
	}
	
	/**
	 * Obtiene los parámetros POST del formulario de Modalidad.
	 * 
	 * @return Array Un arreglo asociativo con los parámetros POST del formulario.
	 */
	private function obtenerParametrosModalidad() {
		$rdbModalidad = null !== $this -> params() -> fromPost("rdbModalidad") ? $this -> params() -> fromPost("rdbModalidad") : "";
		$nombreEquipo = null !== $this -> params() -> fromPost("nombreEquipo") ? $this -> params() -> fromPost("nombreEquipo") : "";
		$noIntegrantes = null !== $this -> params() -> fromPost("noIntegrantes") ? $this -> params() -> fromPost("noIntegrantes") : "";
		$codigoInscripcion = null !== $this -> params() -> fromPost("codigoInscripcion") ? trim($this -> params() -> fromPost("codigoInscripcion")) : "";
		
		return array(
			"rdbModalidad" => $rdbModalidad,
			"nombreEquipo" => $nombreEquipo,
			"noIntegrantes" => $noIntegrantes,
			"codigoInscripcion" => $codigoInscripcion
		);
	}
	
	/**
	 * Filtra los parámetros del formulario de Modalidad.
	 * 
	 * @param Array $params Arreglo que incluye los parámetros POST del formulario.
	 * @return Array Arreglo que contiene el código y el mensaje del resultado validado, de
	 * acuerdo al arreglo de este Action, $FILTRO_MODALIDAD.
	 */
	private function filtrarParametrosModalidad($params) {
		if (($params["rdbModalidad"] !== "individual") && ($params["rdbModalidad"] !== "equipo") && ($params["rdbModalidad"] !== "codigo"))
			return $this -> FILTRO_MODALIDAD["RADIO_BUTTON_INVALIDO"];
		else if (($params["rdbModalidad"] === "equipo") && empty($params["nombreEquipo"]))
			return $this -> FILTRO_MODALIDAD["NOMBRE_EQUIPO_VACIO"];
		else if (($params["rdbModalidad"] === "equipo") && empty($params["noIntegrantes"]))
			return $this -> FILTRO_MODALIDAD["NUMERO_INTEGRANTES_VACIO"];
		else if (($params["rdbModalidad"] === "equipo") && (!is_numeric($params["noIntegrantes"]) || ((int)$params["noIntegrantes"]) < 1))
			return $this -> FILTRO_MODALIDAD["NUMERO_INTEGRANTES_INVALIDO"];
		else if (($params["rdbModalidad"] === "codigo") && empty($params["codigoInscripcion"]))
			return $this -> FILTRO_MODALIDAD["CODIGO_VACIO"];
		else
			return $this -> FILTRO_MODALIDAD["OK"];
	}
	
	/**
	 * Obtiene los parametros POST del formulario de elegir bloque.
	 * @return Array Un arreglo asociativo con los parámetros POST del formulario.
	 */
	private function obtenerParametrosBloque() {
		$dia = null !== $this -> params() -> fromPost("dia") ? $this -> params() -> fromPost("dia") : "";
		$hit = null !== $this -> params() -> fromPost("bloque") ? $this -> params() -> fromPost("bloque") : "";
		
		return array(
			"dia" => $dia,
			"hit" => $hit
		);
	}
	
	/**
	 * Obtiene los parametros POST del formulario de elegir método de pago.
	 * @return Array Un arreglo asociativo con los parámetros POST del formulario.
	 */
	private function obtenerParametrosMetodoPago() {
		$rdbMetodoPago = null !== $this -> params() -> fromPost("rdbMetodoPago") ? $this -> params() -> fromPost("rdbMetodoPago") : "";
		$rdbSucursal = null !== $this -> params() -> fromPost("rdbSucursal") ? $this -> params() -> fromPost("rdbSucursal") : "";
		return array(
			"rdbMetodoPago" => $rdbMetodoPago,
			"rdbSucursal" => $rdbSucursal
		);
	}
	
	/**
	 * Filtra los parámetros del formulario de Método de Pago
	 * 
	 * @param Array $params Arreglo que incluye los parámetros POST del formulario.
	 * @return Array Arreglo que contiene el código y el mensaje del resultado validado, de
	 * acuerdo al arreglo de este Action, $FILTRO_METODO_PAGO.
	 */
	private function filtrarParametrosMetodoPago($params) {
		if (($params["rdbMetodoPago"] !== "tarjeta") && ($params["rdbMetodoPago"] !== "efectivo"))
			return $this -> FILTRO_METODO_PAGO["METODO_DESCONOCIDO"];
		else if (($params["rdbMetodoPago"] === "efectivo") && ($params["rdbSucursal"] !== "OXXO")
			&& ($params["rdbSucursal"] !== "SEVEN_ELEVEN") && ($params["rdbSucursal"] !== "EXTRA")
			&& ($params["rdbSucursal"] !== "CHEDRAUI") && ($params["rdbSucursal"] !== "FARMACIA_BENAVIDES")
			&& ($params["rdbSucursal"] !== "FARMACIA_ESQUIVAR"))
			return $this -> FILTRO_METODO_PAGO["SUCURSAL_DESCONOCIDA"];
		else
			return $this -> FILTRO_METODO_PAGO["OK"];
	}
	
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
	 * Obtiene los parametros POST del formulario de modificar información.
	 * @return Array Un arreglo asociativo con los parámetros POST del formulario.
	 */
	private function obtenerParametros() {
		$nombre = null !== $this -> params() -> fromPost("nombreModInfo") ? $this -> params() -> fromPost("nombreModInfo") : "";
		$paterno = null !== $this -> params() -> fromPost("paternoModInfo") ? $this -> params() -> fromPost("paternoModInfo") : "";
		$materno = null !== $this -> params() -> fromPost("maternoModInfo") ? $this -> params() -> fromPost("maternoModInfo") : "";
		$correo = null !== $this -> params() -> fromPost("emailModInfo") ? $this -> params() -> fromPost("emailModInfo") : "";
		$pwdActual = null !== $this -> params() -> fromPost("pwdActualModInfo") ? $this -> params() -> fromPost("pwdActualModInfo") : "";
		$pwdNueva = null !== $this -> params() -> fromPost("pwdNuevaModInfo") ? $this -> params() -> fromPost("pwdNuevaModInfo") : "";
		$pwdNuevaConf = null !== $this -> params() -> fromPost("pwdNuevaConfModInfo") ? $this -> params() -> fromPost("pwdNuevaConfModInfo") : "";
		$boletin = null !== $this -> params() -> fromPost("boletinModInfo") ? $this -> params() -> fromPost("boletinModInfo") : 1;
		
		return array(
			"nombre" => $nombre,
			"paterno" => $paterno,
			"materno" => $materno,
			"correo" => $correo,
			"pwdActual" => $pwdActual,
			"pwdNueva" => $pwdNueva,
			"pwdNuevaConf" => $pwdNuevaConf,
			"boletin" => $boletin,
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
		else if (empty($params["correo"]))
			return $this -> FILTRO["CORREO_VACIO"];
		else if (!filter_var($params["correo"], FILTER_VALIDATE_EMAIL))
			return $this -> FILTRO["CORREO_INVALIDO"];
		else if (($params["boletin"] != 1) && ($params["boletin"] != 0))
			return $this -> FILTRO["BOLETIN_INVALIDO"];
		else
			return $this -> FILTRO["OK"];
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