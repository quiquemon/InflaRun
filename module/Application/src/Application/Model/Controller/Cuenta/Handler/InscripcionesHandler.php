<?php
namespace Application\Model\Controller\Cuenta\Handler;

use Application\Model\Dao\ConexionDao;
use Application\Model\Controller\Cuenta\Pagos\PagoComproPago;
use Application\Model\Controller\Cuenta\Handler\DiaHitHandler;
use Application\Model\Controller\Cuenta\Handler\FechasHandler;
use Application\Model\Correos\CorreoInscripcion;
use Sendinblue\Model\Correo\User;

/**
 * Clase que maneja las inscripciones de los usuarios a las carreras.
 *
 */
class InscripcionesHandler {
	private static $FILTRO = array(
		"OK_TARJETA" => array(
			"estatus" => 0,
			"message" => "¡Listo! ¡Has quedado inscrito en InflaRun! En breve te enviaremos un correo con"
				. " los detalles de tu inscripción y tu número de corredor. Si el correo no aparece en tu bandeja"
				. " principal, chécalo en la de spam. ¡Prepárate!"
		),
		"OK_EFECTIVO" => array(
			"estatus" => 0,
			"message" => "¡Listo! Se ha generado tu orden de pago. Verifica tu correo, se te ha enviado un formato con la información"
				. " para hacer el pago. Si el correo no aparece en tu bandeja principal, chécalo en la de spam."
		),
		"BLOQUE_INSUFICIENTE" => array(
			"estatus" => 1,
			"message" => "Lo sentimos, se han agotado los lugares de este bloque."
		),
		"TIENDA_NO_DISPONIBLE" => array(
			"estatus" => 2,
			"message" => "La tienda especificada no se encuentra disponible. Por favor, intente con otro establecimiento."
		),
		"MONTO_MAXIMO_SUPERADO" => array(
			"estatus" => 3,
			"message" => "El monto a pagar supera el máximo permitido por este establecimiento."
		),
		"EQUIPO_COMPLETO" => array(
			"estatus" => 4,
			"message" => "Este equipo ya está completo. No se pueden aceptar más integrantes."
		)
	);
	
	/**
	 * Obtiene los datos de la inscripción por medio de la sesión e inscribe al usuario
	 * a la carrera indicada, además de enviarle sus respectivos comprobantes de inscripción
	 * a su correo.
	 * 
	 * @param Array $user El objeto de la sesión con los datos de inscripción del usuario.
	 * @return Array Arreglo asociativo que contiene la respuesta de la operación de acuerdo a la
	 * variable estática de esta clase, $FILTRO.
	 */
	public static function inscribirUsuario($user) {
		$usuario = $user["usuario"];
		$equipo = $user["equipo"];
		$diaHit = $user["diaHit"];
		$playera = $user["playera"];
		$metodoPago = $user["metodoPago"];
		$dao = new ConexionDao();
		
		try {
			$numero = ($equipo["modalidad"] === "individual") ? 1 : $equipo["noIntegrantes"];
			DiaHitHandler::decrementarLugaresRestantes($diaHit["hit"]["id"], $numero);
		} catch (\Exception $ex) {
			return self::$FILTRO["BLOQUE_INSUFICIENTE"];
		}
		
		$orden = self::realizarPago($metodoPago, $usuario);
		if ($orden["error"]) {
			DiaHitHandler::incrementarLugaresRestantes($diaHit["hit"]["id"], $numero);
			if ($metodoPago["metodo"] === "tarjeta") {
				# Implementar PayPal
				return array("estatus" => 1, "message" => "Aún no implementado");
			} else {
				return ($orden["code"] == 5003)
					? self::$FILTRO["MONTO_MAXIMO_SUPERADO"]
					: self::$FILTRO["TIENDA_NO_DISPONIBLE"];
			}
		}
		
		try {
			$dao -> beginTransaction();
			
			$correo = $dao -> consultaGenerica("SELECT * FROM Correo WHERE correo = ?", array($usuario["correo"]));
			if (empty($correo)) {
				$dao -> sentenciaGenerica("INSERT INTO Correo(correo) VALUES (?)", array($usuario["correo"]));
				$correo = $dao -> consultaGenerica("SELECT * FROM Correo WHERE idCorreo = LAST_INSERT_ID()");
			}
			
			$dao -> sentenciaGenerica("INSERT INTO Usuario(nombre, aPaterno, aMaterno, sexo, fechaNacimiento, fechaRegistro,"
					. " recibeCorreos, idEstado, idCorreo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", array(
				$usuario["nombre"],
				$usuario["paterno"],
				$usuario["materno"],
				$usuario["sexo"],
				$usuario["fechaNacimiento"],
				date("Y-m-d"),
				$usuario["boletin"],
				$usuario["idEstado"],
				$correo[0]["idCorreo"]
			));
                        
			$idUsuario = $dao -> consultaGenerica("SELECT idUsuario FROM Usuario WHERE idUsuario = LAST_INSERT_ID()")[0]["idUsuario"];
			
			if ($equipo["modalidad"] === "individual") {
				$dao -> sentenciaGenerica("INSERT INTO Equipo(noIntegrantes, esCortesia, idDiaHit) VALUES (?, ?, ?)", array(
					1, 0, $diaHit["hit"]["id"])
				);
			} else {
				$codigoCanje = self::generarCodigoCanje();
				$dao -> sentenciaGenerica("INSERT INTO Equipo(nombre, codigoCanje, noIntegrantes, esCortesia, idDiaHit)"
						. " VALUES (?, ?, ?, ?, ?)", array(
					$equipo["nombre"], $codigoCanje, $equipo["noIntegrantes"], 0, $diaHit["hit"]["id"]	
				));
			}
			
			$idEquipo = $dao -> consultaGenerica("SELECT idEquipo FROM Equipo WHERE idEquipo = LAST_INSERT_ID()")[0]["idEquipo"];
			
			if ($metodoPago["metodo"] === "tarjeta") {
				# Implementar PayPal
				$folio = 23;
			} else {
				$dao -> sentenciaGenerica("INSERT INTO Pago(monto, estatus, transaccion, orderId, sucursal, fechaExpiracion, idEquipo)"
						. " VALUES (?, ?, ?, ?, ?, ?, ?)", array(
					$metodoPago["precio"],
					0,
					$orden["order_info"]["order_id"],
					$orden["short_id"],
					$metodoPago["sucursal"],
					date("Y-m-d", $orden["exp_date"]),
					$idEquipo
				));
				$folio = null;
			}
			
			$dao -> sentenciaGenerica("INSERT INTO NumeroCorredor VALUES ()");
			$noCorredor = $dao
				-> consultaGenerica("SELECT * FROM NumeroCorredor WHERE idNumeroCorredor = LAST_INSERT_ID()")[0]["idNumeroCorredor"];
			
			$dao -> sentenciaGenerica("INSERT INTO UsuarioEquipo VALUES (?, ?, ?, ?, ?, ?, ?, ?)", array(
				$idUsuario,
				$idEquipo,
				1,
				0,
				$folio,
				$noCorredor,
				$playera["tamanyo"]["id"],
				$playera["color"]["id"]
			));
			
			$dao -> commit();
			// --------- Agregar a Sendinblue -------------
			if ($usuario["boletin"]) {
				$User = new User();
				$User -> IDLista = 43; // <-----  ID lista en sendinblue donde se agregaran los nuevos contactos
				$attributes= array(
					"NOMBRE" => $usuario['nombre'],
					"SURNAME" => $usuario['paterno']
				);
				$User ->createUser($correo['correo'], $attributes, $User -> IDLista);
			}
			  // --------- /Agregar a Sendinblue -------------    
			return ($metodoPago["metodo"] === "tarjeta")
				? self::$FILTRO["OK_TARJETA"]
				: self::$FILTRO["OK_EFECTIVO"];
		} catch (\Exception $ex) {
			$dao -> rollback();
			return array(
				"estatus" => 10,
				"message" => $ex -> getMessage()
			);
		}
	}
	
	/**
	 * Inscribe al usuario dado al equipo indicado y le envía su comprobante de inscripción
	 * a su correo.
	 * 
	 * @param Array $usuario Contiene los datos personales del usuario y el ID de su equipo.
	 * @return Array Arreglo asociativo que contiene la respuesta de la operación de acuerdo a la
	 * variable estática de esta clase, $FILTRO.
	 */
    public static function integrarUsuarioAEquipo($usuario) {
		$dao = new ConexionDao();
		
		try {
			$dao -> beginTransaction();
			
			$correo = $dao -> consultaGenerica("SELECT * FROM Correo WHERE correo = ?", array($usuario["correo"]));
			if (empty($correo)) {
				$dao -> sentenciaGenerica("INSERT INTO Correo(correo) VALUES (?)", array($usuario["correo"]));
				$correo = $dao -> consultaGenerica("SELECT * FROM Correo WHERE idCorreo = LAST_INSERT_ID()");
			}
			
			$dao -> sentenciaGenerica("INSERT INTO Usuario(nombre, aPaterno, aMaterno, sexo, fechaNacimiento, fechaRegistro,"
					. " recibeCorreos, idEstado, idCorreo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", array(
				$usuario["nombre"],
				$usuario["paterno"],
				$usuario["materno"],
				$usuario["sexo"],
				$usuario["fechaNacimiento"],
				date("Y-m-d"),
				$usuario["boletin"],
				$usuario["idEstado"],
				$correo[0]["idCorreo"]
			));
                        
			$idUsuario = $dao -> consultaGenerica("SELECT idUsuario FROM Usuario WHERE idUsuario = LAST_INSERT_ID()")[0]["idUsuario"];
			$folio = "$idUsuario-{$usuario["idEquipo"]}-{$usuario["idDetallesEvento"]}";
			
			$dao -> sentenciaGenerica("INSERT INTO NumeroCorredor VALUES ()");
			$noCorredor = $dao
				-> consultaGenerica("SELECT * FROM NumeroCorredor WHERE idNumeroCorredor = LAST_INSERT_ID()")[0]["idNumeroCorredor"];
			
			$integrantes = $dao -> consultaGenerica("SELECT COUNT(*) AS numero FROM UsuarioEquipo WHERE idEquipo = ?",
				array($usuario["idEquipo"]))[0]["numero"];
			$noInt = $dao -> consultaGenerica("SELECT noIntegrantes FROM Equipo WHERE idEquipo = ?",
				array($usuario["idEquipo"]))[0]["noIntegrantes"];
			
			if ($integrantes === $noInt) {
				$dao -> rollback();
				return self::$FILTRO["EQUIPO_COMPLETO"];
			}
			
			$dao -> sentenciaGenerica("INSERT INTO UsuarioEquipo VALUES (?, ?, ?, ?, ?, ?, ?, ?)", array(
				$idUsuario,
				$usuario["idEquipo"],
				0,
				0,
				$folio,
				$noCorredor,
				$usuario["tamanyo"],
				1
			));
			
			$dao -> commit();
			self::enviarComprobanteInscripcion($idUsuario);
			return self::$FILTRO["OK_TARJETA"];
		} catch (\Exception $ex) {
			$dao -> rollback();
			return array(
				"estatus" => 10,
				"message" => $ex -> getMessage()
			);
		}
	}    
        
    /**
	 * Realiza el pago indicado (tarjeta o efectivo).
	 * 
	 * @param Array $metodoPago Arreglo que incluye la información
	 * sobre el método de pago.
	 * @param Array $usuario Arreglo que contiene la información del
	 * usuario que realiza el pago.
	 * @return bool|Array Regresa TRUE si el pago se realizó exitosamente,
	 * de lo contrario regresa un arreglo con los detalles del error.
	 */
	private static function realizarPago($metodoPago, $usuario) {
		if ($metodoPago["metodo"] === "tarjeta") {
			
		} else {
			$orden = PagoComproPago::generarOrden(array(
				"order_id" => "" . bin2hex(openssl_random_pseudo_bytes(12)),
				"order_name" => "inflarun_inscripcion_efectivo",
				"order_price" => $metodoPago["precio"],
				"customer_name" => "{$usuario["nombre"]} {$usuario["paterno"]} {$usuario["materno"]}",
				"customer_email" => $usuario["correo"],
				"payment_type" => $metodoPago["sucursal"]
			));
			
			$orden["error"] = array_key_exists("type", $orden);	
			return $orden;
		}
	}
	
	/**
	 * Genera un código de canje único.
	 * @return string Un nuevo código de canje.
	 */
	private static function generarCodigoCanje() {
		return bin2hex(openssl_random_pseudo_bytes(25));
	}
	
	/**
	 * Envía el comprobante de inscripción al usuario dado.
	 * 
	 * @param string $idUsuario El ID del usuario.
	 */
	private static function enviarComprobanteInscripcion($idUsuario) {
		$sql = "SELECT u.idUsuario, u.nombre, u.aPaterno AS paterno, u.aMaterno AS materno,"
			. " u.sexo, u.fechaNacimiento, c.correo, ue.folio, ue.idNumeroCorredor, e.nombre AS equipo,"
			. " e.noIntegrantes, p.sucursal, p.monto, dh.horario, de.fechaRealizacion, det.direccion,"
			. " det.nombre AS detallesNombre, ev.nombre AS evento FROM Usuario u, Correo c, UsuarioEquipo ue,"
			. " Equipo e, Pago p, DiaHit dh, DiaEvento de, DetallesEvento det, Evento ev WHERE"
			. " u.idUsuario = ue.idUsuario AND u.idCorreo = c.idCorreo AND ue.idEquipo = e.idEquipo"
			. " AND e.idEquipo = p.idEquipo AND e.idDiaHit = dh.idDiaHit AND dh.idDiaEvento = de.idDiaEvento AND"
			. " de.idDetallesEvento = det.idDetallesEvento AND det.idEvento = ev.idEvento AND u.idUsuario = ?";
		$dao = new ConexionDao();
		
		$user = $dao -> consultaGenerica($sql, array($idUsuario))[0];
		$correo = new CorreoInscripcion(array(
			"nombre" => $user["nombre"],
			"paterno" => $user["paterno"],
			"materno" => $user["materno"],
			"sexo" => ($user["sexo"] === "H" ? "Hombre" : "Mujer"),
			"fechaNacimiento" => (new FechasHandler()) -> traducirFecha($user["fechaNacimiento"]),
			"noCorredor" => $user["idNumeroCorredor"],
			"carrera" => "{$user["evento"]} - {$user["detallesNombre"]}",
			"fecha" => (new FechasHandler()) -> traducirFecha($user["fechaRealizacion"]),
			"hit" => $user["horario"],
			"direccion" => $user["direccion"],
			"uuid" => $user["idUsuario"],
			"folio" => $user["folio"],
			"tipoPago" => (isset($user["sucursal"]) ? "Efectivo" : "Tarjeta de crédito o débito"),
			"precio" => $user["monto"],
			"equipo" => ($user["noIntegrantes"] === 1
				? "Individual"
				: "{$user["equipo"]} | Número de integrantes: {$user["noIntegrantes"]}")
		));
		$correo -> enviarSendinblue($user["correo"], "{$user["nombre"]} {$user["paterno"]}");
	}
}
