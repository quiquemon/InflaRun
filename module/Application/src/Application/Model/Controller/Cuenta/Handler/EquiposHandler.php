<?php
namespace Application\Model\Controller\Cuenta\Handler;

use Application\Model\Dao\ConexionDao;

/**
 * Esta clase se encarga de los procesos necesarios para manejar
 * los equipos que se creen en la carrera.
 */
class EquiposHandler {
	
	/**
	 * Regresa un arreglo asociativo con toda la información del
	 * equipo y su inscripción en InflaRun.
	 * 
	 * @param string $codigoCanje El código de canje del equipo.
	 * @return Array Arreglo asociativo con la información de la
	 * inscripción de ese equipo.
	 * @throws Exception
	 */
	public static function obtenerDatosEquipo($codigoCanje) {
		$dao = new ConexionDao();
		
		$equipo = $dao -> consultaGenerica("SELECT * FROM Equipo WHERE codigoCanje = ?", array($codigoCanje));
		if (empty($equipo))
			throw new \Exception("Ese equipo no está registrado.");
		
		$equipo = $equipo[0];
		$usuarios = $dao -> consultaGenerica("SELECT u.*, c.correo, ue.folio, ue.idNumeroCorredor FROM Usuario u, UsuarioEquipo ue, Correo c"
			. " WHERE u.idUsuario = ue.idUsuario AND u.idCorreo = c.idCorreo AND ue.idEquipo = ?", array($equipo["idEquipo"]));
		
		$hit = $dao -> consultaGenerica("SELECT * FROM DiaHit WHERE idDiaHit = ?", array($equipo["idDiaHit"]))[0];
		$dia = $dao -> consultaGenerica("SELECT * FROM DiaEvento WHERE idDiaEvento = ?", array($hit["idDiaEvento"]))[0];
		$evento = $dao -> consultaGenerica("SELECT * FROM DetallesEvento WHERE idDetallesEvento = ?", array($dia["idDetallesEvento"]))[0];
		
		$playeras["tamanyo"] = $dao -> consultaGenerica("SELECT * FROM TamPlayera");
		
		return array(
			"equipo" => $equipo,
			"usuarios" => $usuarios,
			"diaHit" => array(
				"dia" => $dia,
				"hit" => $hit
			),
			"evento" => $evento,
			"playeras" => $playeras
		);
	}
}