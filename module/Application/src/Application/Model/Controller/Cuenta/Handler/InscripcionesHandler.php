<?php
namespace Application\Model\Controller\Cuenta\Handler;

use Zend\Session\Container;
use Application\Model\Dao\ConexionDao;
use Application\Model\Controller\Cuenta\Pagos\PagoComproPago;
use Application\Model\Controller\Cuenta\Pagos\PagoPayPal;
use Application\Model\Controller\Cuenta\Handler\DiaHitHandler;
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
		)
	);
	
	/**
	 * Obtiene los datos de la inscripción por medio de la sesión e inscribe al usuario
	 * a la carrera indicada, además de enviarle sus respectivos comprobantes de inscripción
	 * a su correo.
	 * 
	 * @param \Zend\Session\Container $user El objeto de la sesión con los datos de inscripción del usuario.
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
                        $User = new User();
                        $User -> IDLista = 43; // <-----  ID lista en sendinblue donde se agregaran los nuevos contactos
                        $attributes= array(
                            "NOMBRE"=>    $Usuario['nombre'], 
                            "SURNAME"=> $Usuario['paterno']
                         );
                        $User ->createUser($correo['correo'], $attributes, $User -> IDLista);
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
	 * Genera un número de corredor único.
	 * @return int Un nuevo número de corredor.
	 */
	private static function generarNumeroCorredor() {
		return hexdec(bin2hex(openssl_random_pseudo_bytes(2)));
	}
	
	/**
	 * Genera un código de canje único.
	 * @return string Un nuevo código de canje.
	 */
	private static function generarCodigoCanje() {
		return bin2hex(openssl_random_pseudo_bytes(25));
	}
        
        /**
	 * Genera un nuevo usuario en sendinblue.
	 * @return Array  
	 * @attributes 
         * Array (
         *  "NAME"=>"name", 
         *  "SURNAME"=>"surname"       
         * )
         * @43 idd la lista InflaRun 2016
	 */
        
}
