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