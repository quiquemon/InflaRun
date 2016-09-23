<?php
namespace Application\Model\Controller\Cuenta\Handler;

use Application\Model\Dao\EquipoDao;
use Application\Model\Correos\CorreoInscripcion;
use Application\Model\Controller\Cuenta\Handler\DiaHitHandler;

/**
 * Clase que maneja las inscripciones de los usuarios y su
 * registro en la base de datos.
 */
class InscripcionHandler {
	
	/**
	 * Registra la inscripción de un usuario en la base de datos y le genera su número de corredor. Al finalizar
	 * envía un correo al usuario con los datos de su inscripción.
	 * 
	 * @param Array $resultadoPago Arreglo asociativo con el resultado de la transacción (tarjeta o efectivo)
	 * @param Array $metodoPago Arreglo asociativo que incluye el método de pago (y la sucursal, si es en efectivo)
	 * @param Array $diaHit Arreglo asociativo que contiene la información del día y el hit.
	 * @param Array $modalidad Arreglo asociativo que incluye la información sobre la modalidad de inscripción.
	 * @param float $monto El monto total pagado por la inscripción.
	 * @param int $usuario El objeto con los datos del usuario.
	 * @param Array $inscripcionDetalles Array asociativo con los datos obtenidos de la sesión
	 * @param string $orderId El ID de la orden generada (si fue pago en efectivo).
	 * @throws Exception
	 */
	public static function registrarInscripcion($resultadoPago, $metodoPago, $diaHit, $modalidad, $monto, $usuario, $inscripcionDetalles, $orderId = null) {
		$dao = new EquipoDao();
		
		try {
			$dao -> beginTransaction();
			
			if ($modalidad["rdbModalidad"] === "individual") {
				$dao -> sentenciaGenerica("INSERT INTO Equipo(noIntegrantes, idDiaHit) VALUES (?, ?)", array(1, $diaHit["hit"]["idDiaHit"]));
			} else {
				$codigoCanje = self::generarCodigoCanje();
				$dao -> sentenciaGenerica("INSERT INTO Equipo(nombre, codigoCanje, noIntegrantes, idDiaHit) VALUES (?, ?, ?, ?)", array(
					$modalidad["nombreEquipo"],
					$codigoCanje,
					(int)$modalidad["noIntegrantes"],
					$diaHit["hit"]["idDiaHit"]
				));
			}
			
			$equipo = $dao -> consultaGenerica("SELECT * FROM Equipo WHERE idEquipo = LAST_INSERT_ID()")[0];
			
			if ($metodoPago["rdbMetodoPago"] === "tarjeta") {
				$folio = "{$usuario -> getIdUsuario()}-{$equipo["idEquipo"]}-{$inscripcionDetalles["detallesEvento"] -> getIdDetallesEvento()}";
				$noCorredor = self::generarNumeroCorredor();
			} else {
				$folio = null;
				$noCorredor = null;
			}
			
			$dao -> sentenciaGenerica("INSERT INTO UsuarioEquipo VALUES (?, ?, ?, ?)", array(
				$usuario -> getIdUsuario(),
				$equipo["idEquipo"],
				$noCorredor,
				$folio
			));

			if ($metodoPago["rdbMetodoPago"] === "tarjeta") {
				$dao -> sentenciaGenerica("INSERT INTO Pago(monto, estatus, transaccion, transIni, transFin, idEquipo) VALUES (?, ?, ?, ?, ?, ?)", array(
					$monto,
					1,
					$resultadoPago["WebServices_Transacciones"]["transaccion"]["transaccion"],
					$resultadoPago["WebServices_Transacciones"]["transaccion"]["TransIni"],
					$resultadoPago["WebServices_Transacciones"]["transaccion"]["TransFin"],
					$equipo["idEquipo"]
				));
			} else {
				$dao -> sentenciaGenerica("INSERT INTO Pago(monto, estatus, transaccion, orderId, sucursal, fechaExpiracion, idEquipo) VALUES (?, ?, ?, ?, ?, ?, ?)", array(
					$monto,
					0,
					$resultadoPago["charge"]["reference"],
					$orderId,
					$metodoPago["rdbSucursal"],
					$resultadoPago["charge"]["expiration_date"],
					$equipo["idEquipo"]
				));
			}
			
			$dao -> commit();
		} catch (\Exception $ex) {
			$dao -> rollback();
			throw new \Exception($ex -> getMessage());
		}
		
		if ($metodoPago["rdbMetodoPago"] === "tarjeta") {
			(new CorreoInscripcion(array(
				"nombre" => $usuario -> getNombre(),
				"paterno" => $usuario -> getAPaterno(),
				"materno" => $usuario -> getAMaterno(),
				"sexo" => ($usuario -> getSexo() == "H") ? "Masculino" : "Femenino",
				"fechaNacimiento" => $usuario -> getFechaNacimiento(),
				"noCorredor" => "$noCorredor",
				"carrera" => "{$inscripcionDetalles["evento"]["nombre"]} - {$inscripcionDetalles["detallesEvento"] -> getNombre()}",
				"fecha" => $diaHit["dia"]["fechaRealizacion"],
				"hit" => $diaHit["hit"]["horario"],
				"direccion" => $inscripcionDetalles["detallesEvento"] -> getDireccion(),
				"uuid" => "{$usuario -> getIdUsuario()}",
				"folio" => $folio,
				"tipoPago" => ($metodoPago["rdbMetodoPago"] === "tarjeta") ? "Tarjeta de crédito o débito" : "Efectivo",
				"precio" => "\$$monto",
				"equipo" => ($modalidad["rdbModalidad"] === "individual")
					? "Individual"
					: $modalidad["nombreEquipo"] . " | Número de integrantes: {$modalidad["noIntegrantes"]} | Código de Canje: $codigoCanje"
			))) -> enviarCorreo($usuario -> getCorreo(), "{$usuario -> getNombre()} {$usuario -> getAPaterno()}");
		}
	}
	
	/**
	 * Registra la inscripción de un usuario por medio de su código de canje y le genera su número de corredor.
	 * Al finalizar envía un correo al usuario con los datos de su inscripción.
	 * 
	 * @param string $codigoCanje El código de canje del equipo al cual pertenece el usuario.
	 * @param int $usuario El objeto con los datos de los usuarios.
	 * @param Array $inscripcionDetalles Array asociativo con los datos obtenidos de la sesión
	 * @param Array $diaHit Arreglo asociativo que contiene la información del día y el hit.
	 * @throws Exception
	 */
	public static function registrarCodigoInscripcion($codigoCanje, $usuario, $inscripcionDetalles, $diaHit) {
		$dao = new EquipoDao();
		
		try {
			$dao -> beginTransaction();
			$equipo = $dao -> consultaGenerica("SELECT * FROM Equipo WHERE codigoCanje = ?", array($codigoCanje))[0];

			$noCorredor = self::generarNumeroCorredor();
			$folio = "{$usuario -> getIdUsuario()}-{$equipo["idEquipo"]}-{$inscripcionDetalles["detallesEvento"] -> getIdDetallesEvento()}";
			
			if ($equipo["esCortesia"] == 1) {
				DiaHitHandler::decrementarLugaresRestantes($diaHit["hit"]["idDiaHit"]);
				$dao -> sentenciaGenerica("UPDATE Equipo SET idDiaHit = ? WHERE idEquipo = ?", array(
					$diaHit["hit"]["idDiaHit"], $equipo["idEquipo"]
				));
			}
			
			$dao -> sentenciaGenerica("INSERT INTO UsuarioEquipo VALUES (?, ?, ?, ?)", array(
				$usuario -> getIdUsuario(),
				$equipo["idEquipo"],
				$noCorredor,
				$folio
			));
			$dao -> commit();
		} catch (\Exception $ex) {
			$dao -> rollback();
			throw new \Exception($ex -> getMessage(), $ex -> getCode());
		}
		
		(new CorreoInscripcion(array(
			"nombre" => $usuario -> getNombre(),
			"paterno" => $usuario -> getAPaterno(),
			"materno" => $usuario -> getAMaterno(),
			"sexo" => ($usuario -> getSexo() == "H") ? "Masculino" : "Femenino",
			"fechaNacimiento" => $usuario -> getFechaNacimiento(),
			"noCorredor" => "$noCorredor",
			"carrera" => "{$inscripcionDetalles["evento"]["nombre"]} - {$inscripcionDetalles["detallesEvento"] -> getNombre()}",
			"fecha" => $diaHit["dia"]["fechaRealizacion"],
			"hit" => $diaHit["hit"]["horario"],
			"direccion" => $inscripcionDetalles["detallesEvento"] -> getDireccion(),
			"uuid" => "{$usuario -> getIdUsuario()}",
			"folio" => $folio,
			"tipoPago" => "n/a",
			"precio" => "n/a",
			"equipo" => $equipo["nombre"]
		))) -> enviarCorreo($usuario -> getCorreo(), "{$usuario -> getNombre()} {$usuario -> getAPaterno()}");
	}
	
	/**
	 * Regresa un arreglo con la información del grupo al cual se va a agregar
	 * un nuevo usuario
	 * 
	 * @param string $folio El folio de algún integrante del equipo.
	 * @return Array Arreglo asociativo con la información del grupo.
	 */
	public static function obtenerInfoGrupoRegistroManual($folio) {
		try {
			$info = self::obtenerInfoGrupo($folio);
			return $info;
		} catch (\Exception $ex) {
			return array(
				"estatus" => 3,
				"message" => $ex -> getMessage()
			);
		}
	}
	
	/**
	 * Agrega al usuario al grupo indicado.
	 * 
	 * @param string $correo El correo del usuario a agregar.
	 * @param string $folio El folio de algún integrante del equipo.
	 * @return Array Arreglo asociativo con el resultado de la operación.
	 */
	public static function agregarUsuarioAGrupo($correo, $folio) {
		$dao = new EquipoDao();
		
		try {
			$dao -> beginTransaction();
			
			$usuario = $dao -> consultaGenerica("SELECT * FROM Usuario WHERE correo = ?", array($correo));
			if (empty($usuario)) {
				return array(
					"estatus" => 1,
					"message" => "El usuario con el correo '$correo' no está registrado."
				);
			}
			
			$usuario = $usuario[0];
			$numerosFolio = explode("-", $folio);
			$equipo = $dao -> consultaGenerica("SELECT e.* FROM Equipo e, UsuarioEquipo ue WHERE e.idEquipo = ue.idEquipo"
				. " AND ue.folio like ?", array("%-{$numerosFolio[1]}-{$numerosFolio[2]}"));

			if (empty($equipo)) {
				return array(
					"estatus" => 2,
					"message" => "No se ha encontrado ningún grupo con este número de folio."
				);
			}
			
			$equipo = $equipo[0];
			$inscritos = $dao -> consultaGenerica("SELECT COUNT(ue.idEquipo) AS inscritos FROM Equipo e, UsuarioEquipo ue"
				. " WHERE e.idEquipo = ue.idEquipo AND e.idEquipo = ? GROUP BY e.idEquipo", array($equipo["idEquipo"]))[0]["inscritos"];
			if ($inscritos >= $equipo["noIntegrantes"]) {
				return array(
					"estatus" => 3,
					"message" => "Este equipo ya está completo."
				);
			}
			
			$dao -> sentenciaGenerica("INSERT INTO UsuarioEquipo VALUES (?, ?, ?, ?)", array(
				$usuario["idUsuario"],
				$equipo["idEquipo"],
				self::generarNumeroCorredor(),
				"{$usuario["idUsuario"]}-{$equipo["idEquipo"]}-{$numerosFolio[2]}"
			));
			$dao -> commit();
			return array(
				"estatus" => 0,
				"message" => "El usuario se ha agregado con éxito al grupo."
			);
		} catch (\Exception $ex) {
			$dao -> rollback();
			return array(
				"estatus" => 4,
				"message" => $ex -> getMessage()
			);
		}
	}
	
	/**
	 * Realiza los cambios correspondientes en la base de datos y envía
	 * un correo al usuario con los datos de su inscripción.
	 * 
	 * @param int $idPago El ID del pago a confirmar.
	 * @throws Exception
	 */
	public static function aceptarPagoEfectivo($idPago) {
		$dao = new EquipoDao();
		try {
			$dao -> beginTransaction();
			$pago = $dao -> consultaGenerica("SELECT * FROM Pago WHERE idPago = ?", array($idPago))[0];
			$equipo = $dao -> consultaGenerica("SELECT * FROM Equipo WHERE idEquipo = ?", array($pago["idEquipo"]))[0];
			$usuario = $dao -> consultaGenerica("SELECT * FROM Usuario WHERE idUsuario ="
				. " (SELECT idUsuario FROM UsuarioEquipo WHERE idEquipo = ?)", array($equipo["idEquipo"]))[0];
			$diaHit["hit"] = $dao -> consultaGenerica("SELECT * FROM DiaHit WHERE idDiaHit = ?", array($equipo["idDiaHit"]))[0];
			$diaHit["dia"] = $dao -> consultaGenerica("SELECT * FROM DiaEvento WHERE idDiaEvento = ?", array($diaHit["hit"]["idDiaEvento"]))[0];
			$detallesEvento = $dao -> consultaGenerica("SELECT * FROM DetallesEvento WHERE idDetallesEvento = ?", array($diaHit["dia"]["idDetallesEvento"]))[0];
			$evento = $dao -> consultaGenerica("SELECT * FROM Evento WHERE idEvento = ?", array($detallesEvento["idEvento"]))[0];
			
			$noCorredor = self::generarNumeroCorredor();
			$folio = "{$usuario["idUsuario"]}-{$equipo["idEquipo"]}-{$detallesEvento["idDetallesEvento"]}";
			$codigoCanje = ($equipo["noIntegrantes"] > 1) ? self::generarCodigoCanje() : null;
			
			$dao -> sentenciaGenerica("UPDATE Pago SET estatus = 1 WHERE idPago = ?", array($idPago));
			$dao -> sentenciaGenerica("UPDATE Equipo SET codigoCanje = ? WHERE idEquipo = ?", array(
				$codigoCanje, $equipo["idEquipo"]
			));
			$dao -> sentenciaGenerica("UPDATE UsuarioEquipo SET noCorredor = ?, folio = ? WHERE idEquipo = ?", array(
				$noCorredor, $folio, $equipo["idEquipo"]
			));
			$dao -> commit();
		} catch (\Exception $ex) {
			$dao -> rollback();
			throw new \Exception($ex -> getMessage());
		}
		
		(new CorreoInscripcion(array(
			"nombre" => $usuario["nombre"],
			"paterno" => $usuario["aPaterno"],
			"materno" => $usuario["aMaterno"],
			"sexo" => ($usuario["sexo"] == "H") ? "Masculino" : "Femenino",
			"fechaNacimiento" => $usuario["fechaNacimiento"],
			"noCorredor" => "$noCorredor",
			"carrera" => "{$evento["nombre"]} - {$detallesEvento["nombre"]}",
			"fecha" => $diaHit["dia"]["fechaRealizacion"],
			"hit" => $diaHit["hit"]["horario"],
			"direccion" => $detallesEvento["direccion"],
			"uuid" => "{$usuario["idUsuario"]}",
			"folio" => $folio,
			"tipoPago" => "Efectivo",
			"precio" => "\${$pago["monto"]}",
			"equipo" => ($equipo["noIntegrantes"] == 1)
				? "Individual"
				: $equipo["nombre"] . " | Número de integrantes: {$equipo["noIntegrantes"]} | Código de Canje: $codigoCanje"
		))) -> enviarCorreo($usuario["correo"], "{$usuario["nombre"]} {$usuario["aPaterno"]}");
	}
	
	/**
	 * Realiza los cambios correspondientes en la base de datos para
	 * rechazar el pago realizado.
	 * 
	 * @param int $idPago El ID del pago a rechazar.
	 */
	public static function rechazarPagoEfectivo($idPago) {
		$dao = new EquipoDao();
		try {
			$dao -> beginTransaction();
			$pago = $dao -> consultaGenerica("SELECT * FROM Pago WHERE idPago = ?", array($idPago))[0];
			$idDiaHit = $dao -> consultaGenerica("SELECT idDiaHit FROM Equipo WHERE idEquipo = ?", array($pago["idEquipo"]))[0]["idDiaHit"];
			$noLugares = $dao -> consultaGenerica("SELECT noIntegrantes FROM Equipo WHERE idEquipo = ?", array($pago["idEquipo"]))[0]["noIntegrantes"];
			$dao -> sentenciaGenerica("DELETE FROM Equipo WHERE idEquipo = ?", array($pago["idEquipo"]));
			DiaHitHandler::incrementarLugaresRestantes($idDiaHit, $noLugares);
			$dao -> commit();
		} catch (\Exception $ex) {
			$dao -> rollback();
			throw new \Exception($ex -> getMessage());
		}
	}
	
	/**
	 * Regresa un arreglo con la información del grupo al cual se va a agregar
	 * un nuevo usuario
	 * 
	 * @param string $folio El folio de algún integrante del equipo.
	 * @return Array Arreglo asociativo con la información del grupo.
	 * @throws Exception
	 */
	private static function obtenerInfoGrupo($folio) {
		$dao = new EquipoDao();
		
		if (!preg_match("/^[0-9]+-[0-9]+-[0-9]+$/", $folio)) {
			return array(
				"estatus" => 1,
				"message" => "El folio debe tener tres números separados por guiones (ej. 12-34-56)."
			);
		}
		
		$numerosFolio = explode("-", $folio);
		$equipo = $dao -> consultaGenerica("SELECT e.* FROM Equipo e, UsuarioEquipo ue WHERE e.idEquipo = ue.idEquipo"
			. " AND ue.folio like ?", array("%-{$numerosFolio[1]}-{$numerosFolio[2]}"));
		
		if (empty($equipo)) {
			return array(
				"estatus" => 2,
				"message" => "No se ha encontrado ningún grupo con este número de folio."
			);
		}
		
		$equipo = $equipo[0];
		$inscritos = $dao -> consultaGenerica("SELECT COUNT(ue.idEquipo) AS inscritos FROM Equipo e, UsuarioEquipo ue"
			. " WHERE e.idEquipo = ue.idEquipo AND e.idEquipo = ? GROUP BY e.idEquipo", array($equipo["idEquipo"]))[0];
		$horario = $dao -> consultaGenerica("SELECT de.fechaRealizacion, dh.horario FROM DiaEvento de, DiaHit dh"
			. " WHERE de.idDiaEvento = dh.idDiaEvento AND dh.idDiaHit = ?", array($equipo["idDiaHit"]))[0];
		$integrantes = $dao -> consultaGenerica("SELECT u.*, ue.noCorredor FROM Usuario u, UsuarioEquipo ue"
			. " WHERE u.idUsuario = ue.idUsuario AND ue.idEquipo = ?", array($equipo["idEquipo"]));
		
		return array(
			"estatus" => 0,
			"info" => array(
				"folio" => $folio,
				"nombre" => $equipo["nombre"],
				"codigoCanje" => $equipo["codigoCanje"],
				"noIntegrantes" => $equipo["noIntegrantes"],
				"inscritos" => $inscritos["inscritos"],
				"fecha" => $horario["fechaRealizacion"],
				"bloque" => $horario["horario"],
				"integrantes" => $integrantes
			)
		);
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
		return bin2hex(openssl_random_pseudo_bytes(12));
	}
}