<?php
namespace Application\Model\Controller\Cuenta\Handler;

use Application\Model\Correos\CorreoInscripcion;
use Application\Model\Dao\EquipoDao;
use Application\Model\Controller\Cuenta\Pagos\PagoEfectivo;

/**
 * Clase que maneja las operaciones que el administrador lleva a cabo.
 */
class AdminHandler {
	
	/**
	 * Acepta todas las órdenes de pago que hayan sido aceptadas de acuerdo
	 * a la documentación de la API de PagoFácil, realiza los cambios necesarios
	 * a la base de datos y envía su correo de confirmación.
	 * 
	 * @throws Exception
	 */
	public static function aceptarOrdenesPagadas() {
		$dao = new EquipoDao("216.104.39.14");
		try {
			$dao -> beginTransaction();
			$pagos = self::obtenerPagosPendientes();
			$pagosRealizados = self::filtrarPagos($pagos);
			$r = self::aceptarPagos($pagosRealizados, $dao);
			$dao -> commit();
			return $pagos;
		} catch (\Exception $ex) {
			$dao -> rollback();
			throw new \Exception($ex -> getMessage());
		}
	}
	
	/**
	 * Rechaza todas las órdenes de pago que hayan expirado de acuerdo a la API
	 * de PagoFácil y realiza los cambios necesarios a la base de datos.
	 * 
	 * @throws Exception
	 */
	public static function rechazarOrdenesPagadas() {
		$dao = new EquipoDao();
		try {
			$dao -> beginTransaction();
			$dao -> commit();
		} catch (\Exception $ex) {
			$dao -> rollback();
			throw new \Exception($ex -> getMessage());
		}
	}
	
	/**
	 * Obtiene la información personal del usuario con el correo dado.
	 * 
	 * @param string $correo El correo del usuario.
	 * @return Array Arreglo asociativo con la información personal del usuario.
	 */
	public static function obtenerInfoPersonalUsuario($correo) {
		try {
			$usuario = self::obtenerInfoPersonal($correo);
			return $usuario;
		} catch (\Exception $ex) {
			return array(
				"estatus" => 3,
				"message" => $ex -> getMessage()
			);
		}
	}
	
	/**
	 * Obtiene un arreglo asociativo con la información personal del usuario.
	 * 
	 * @param string $correo El correo del usuario.
	 * @return Arreglo asociativo con la información personal del usuario.
	 * @throws Exception
	 */
	private static function obtenerInfoPersonal($correo){
		$dao = new EquipoDao();
		
		$usuario = $dao -> consultaGenerica("SELECT * FROM Usuario WHERE correo like ?", array("%$correo%"));
		if (empty($usuario)) {
			return array(
				"estatus" => 1,
				"message" => "El usuario con el correo '$correo' no está registrado.",
			);
		}
		
		return array(
			"estatus" => 0,
			"message" => "",
			"info" => $usuario[0]
		);
	}
	
	/**
	 * Obtiene los datos del usuario necesarios para su inscripción (número de corredor, equipo, etc).
	 * Es la misma información que se les envía por correo al momento de finalizar su inscripción.
	 * 
	 * @param string $correo El correo electrónico del usuario.
	 * @param int $idDetallesEvento El ID del evento en el cual el usuario está inscrito.
	 * @return Array Arreglo asociativo con la información de la inscripción del usuario.
	 */
	public static function obtenerInformacionUsuario($correo, $idDetallesEvento) {
		try{
			$info = self::obtenerInfo($correo, $idDetallesEvento);
			return $info;
		} catch (\Exception $ex) {
			return array(
				"estatus" => 3,
				"message" => $ex -> getMessage(),
			);
		}
	}
	
	/**
	 * Obtiene un arreglo asociativo con la información de
	 * inscripción del usuario.
	 * 
	 * @param string $correo El correo del usuario.
	 * @param int $idDetallesEvento El ID del evento en el cual el usuario está inscrito.
	 * @return Array Arreglo asociativo con la información de la inscripción del usuario.
	 * @throws Exception
	 */
	private static function obtenerInfo($correo, $idDetallesEvento) {
		$dao = new EquipoDao();
		
		$usuario = $dao -> consultaGenerica("SELECT * FROM Usuario WHERE correo like ?", array("%$correo%"));
		if (empty($usuario)) {
			return array(
				"estatus" => 1,
				"message" => "El usuario con el correo '$correo' no está registrado.",
			);
		}
		
		$usuario = $usuario[0];
		$usEq = $dao
			-> consultaGenerica("SELECT * FROM UsuarioEquipo WHERE idUsuario = {$usuario["idUsuario"]} AND folio LIKE '%-$idDetallesEvento'");
		if (empty($usEq)) {
			return array(
				"estatus" => 2,
				"message" => "Este usuario no está actualmente en ningún equipo. Puede que haya hecho su pago"
					. " en efectivo (y espere su confirmación) o que aún no se haya inscrito."
			);
		}
		
		$usEq = $usEq[0];
		$equipo = $dao -> consultaGenerica("SELECT * FROM Equipo WHERE idEquipo = ?", array($usEq["idEquipo"]))[0];
		$pago = $dao -> consultaGenerica("SELECT * FROM Pago WHERE idEquipo = ?", array($usEq["idEquipo"]))[0];
		$hit = $dao -> consultaGenerica("SELECT * FROM DiaHit WHERE idDiaHit = ?", array($equipo["idDiaHit"]))[0];
		$dia = $dao -> consultaGenerica("SELECT * FROM DiaEvento WHERE idDiaEvento = ?", array($hit["idDiaEvento"]))[0];
		$detallesEvento = $dao -> consultaGenerica("SELECT * FROM DetallesEvento WHERE idDetallesEvento = ?", array($idDetallesEvento))[0];
		$evento = $dao -> consultaGenerica("SELECT * FROM Evento WHERE idEvento = ?", array($detallesEvento["idEvento"]))[0];
		
		return array(
			"estatus" => 0,
			"message" => "",
			"info" => array(
				"usuario" => array(
					"nombre" => $usuario["nombre"],
					"paterno" => $usuario["aPaterno"],
					"materno" => $usuario["aMaterno"],
					"sexo" => ($usuario["sexo"] == "H" ? "Masculino" : "Femenino"),
					"fechaNacimiento" => "{$usuario["fechaNacimiento"]}",
					"correo" => $usuario["correo"]
				),
				"carrera" => array(
					"nombre" => "{$evento["nombre"]} - {$detallesEvento["nombre"]}",
					"fecha" => "{$dia["fechaRealizacion"]}",
					"horario" => "{$hit["horario"]}",
					"direccion" => $detallesEvento["direccion"]
				),
				"inscripcion" => array(
					"uuid" => "{$usuario["idUsuario"]}",
					"folio" => $usEq["folio"],
					"tipoPago" => ($pago["orderId"] ? "Sucursal {$pago["sucursal"]}" : "Tarjeta de crédito o débito"),
					"precio" => "\${$pago["monto"]}",
					"noCorredor" => "{$usEq["noCorredor"]}",
					"equipo" => (!$equipo["nombre"]
						? "Individual"
						: "{$equipo["nombre"]} | Número de integrantes: {$equipo["noIntegrantes"]} | Código de canje: {$equipo["codigoCanje"]}")
				)
			)
		);
	}
	
	/**
	 * Reenvía el correo al usuario con su información
	 * necesaria para su inscripción.
	 * 
	 * @param Array $usuario Información obtenida con el método obtenerInfo().
	 * @return string|int TRUE si el correo se envió de manera exitosa. De lo contrario
	 * regresa el mensaje de error.
	 */
	public static function reenviarCorreo($usuario) {
		$user = $usuario["info"]["usuario"];
		$carrera = $usuario["info"]["carrera"];
		$inscripcion = $usuario["info"]["inscripcion"];
		
		return (new CorreoInscripcion(array(
			"nombre" => $user["nombre"],
			"paterno" => $user["paterno"],
			"materno" => $user["materno"],
			"sexo" => $user["sexo"],
			"fechaNacimiento" => $user["fechaNacimiento"],
			"noCorredor" => $inscripcion["noCorredor"],
			"carrera" => $carrera["nombre"],
			"fecha" => $carrera["fecha"],
			"hit" => $carrera["horario"],
			"direccion" => $carrera["direccion"],
			"uuid" => $inscripcion["uuid"],
			"folio" => $inscripcion["folio"],
			"tipoPago" => $inscripcion["tipoPago"],
			"precio" => $inscripcion["precio"],
			"equipo" => $inscripcion["equipo"]
		))) ->enviarSendinblue($user["correo"], "{$user["nombre"]} {$user["paterno"]}");
	}
	
	/**
	 * Obtiene todos los pagos pendientes hasta el día actual.
	 * @return Array Una lista de arreglos asociativos con la información de los pagos.
	 */
	private static function obtenerPagosPendientes() {
		return ((new EquipoDao("216.104.39.14")) -> consultaGenerica("SELECT * FROM Pago"));
	}
	
	/**
	 * Filtra los pagos pendientes de acuerdo a la respuesta que PagoFácil tenga sobre ellos.
	 * 
	 * @param Array $pagos Lista de arreglos asociativos con la información de los pagos.
	 * @return Array Arreglo asociativo con únicamente aquellos pagos que ya hayan sido realizados.
	 */
	private static function filtrarPagos($pagos) {
		$pagosRealizados = array();
		
		foreach($pagos as $pago) {
			$result = self::consultarOrden($pago["transaccion"]);
			if ($result["charge"]["status"] == 4) {
				$pagosRealizados[] = $pago;
			}
		}
		
		return $pagosRealizados;
	}
	
	/**
	 * Realiza los cambios correspondientes en la base de datos y envía un correo a los
	 * usuarios cuyos pagos ya hayan sido realizados.
	 * 
	 * @param Array Arreglo asociativo con únicamente aquellos pagos que ya hayan sido realizados.
	 * @param ConexionDao Objeto con una conexión activa a la base de datos.
	 */
	private static function aceptarPagos($pagos, $dao) {
		foreach ($pagos as $pago) {
			
		}
		
		return $pagos;
	}
	
	/**
	 * Consulta el estatus de la orden indicada
	 * 
	 * @param string $referencia La referencia de la orden a consultar.
	 */
	private static function consultarOrden($referencia) {
		return (new PagoEfectivo()) -> consultarOrden($referencia);
	}
}