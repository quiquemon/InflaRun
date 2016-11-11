<?php
namespace Application\Model\Controller\Cuenta\Handler;

use Application\Model\Dao\ConexionDao;

/**
 * Esta clase se encarga de los procesos necesarios para
 * manejar las inscripciones por taquillas.
 */
class TaquillasHandler {
	
	private static $FILTRO = array(
		"OK" => array(
			"estatus" => 0,
			"message" => "¡Datos validados correctamente! Redirigiendo..."
		),
		"CODIGO_INVALIDO" => array(
			"estatus" => 1,
			"message" => "Ese código de canje es inválido."
		),
		"CODIGO_UTILIZADO" => array(
			"estatus" => 2,
			"message" => "Ese código de canje ya ha sido utilizado."
		),
		"EVENTO_INVALIDO" => array(
			"estatus" => 3,
			"message" => "Ese evento no está disponible."
		)
	);
	
	/**
	 * Valida que el código de canje y el ID del evento sean correctos.
	 * 
	 * @param string $codigoCanje El código de canje del usuario.
	 * @param int $idDetallesEvento El ID del evento al cual se quiere inscribir.
	 * @return Array Arreglo asociativo que regresa el resultado de la validación,
	 * de acuerdo a la variable estática de esta clase, $FILTRO.
	 */
	public static function validarCodigo($codigoCanje, $idDetallesEvento) {
		try {
			$dao = new ConexionDao();	
			$equipo = $dao -> consultaGenerica("SELECT * FROM Equipo WHERE codigoCanje = ?", array($codigoCanje));
			
			if (empty($equipo)) {
				return self::$FILTRO["CODIGO_INVALIDO"];
			} else if (isset($equipo[0]["idDiaHit"])) {
				return self::$FILTRO["CODIGO_UTILIZADO"];
			}
			
			$evento = $dao -> consultaGenerica("SELECT * FROM DetallesEvento WHERE idDetallesEvento = ?", array($idDetallesEvento));
			
			if (empty($evento)) {
				return self::$FILTRO["EVENTO_INVALIDO"];
			}
			
			return self::$FILTRO["OK"];
		} catch (\Exception $ex) {
			self::$FILTRO["BD_ERROR"]["message"] = $ex -> getMessage();
			return self::$FILTRO["BD_ERROR"];
		}
	}
	
	/**
	 * Regresa la información necesaria del evento para que el usuario pueda
	 * llevar a cabo su inscripción.
	 * 
	 * @param string $codigoCanje El código de canje del usuario.
	 * @param int $idDetallesEvento El ID del evento al cual se quiere inscribir.
	 * @return Array Arreglo asociativo que contiene los días y bloques del evento,
	 * la información del evento, la información del código de canje y las playeras.
	 * @throws Exception
	 */
	public static function obtenerDatosEvento($codigoCanje, $idDetallesEvento) {
		$dao = new ConexionDao();
		$equipo = $dao -> consultaGenerica("SELECT * FROM Equipo WHERE codigoCanje = ?", array($codigoCanje))[0];
		$evento = $dao -> consultaGenerica("SELECT * FROM DetallesEvento WHERE idDetallesEvento = ?", array($idDetallesEvento))[0];
		
		$sql = "SELECT DISTINCT de.* FROM DiaEvento de, DiaHit dh WHERE de.idDiaEvento = dh.idDiaEvento AND"
				. " de.idDetallesEvento = ? AND dh.lugaresRestantes > 0";
		$dias = $dao -> consultaGenerica($sql, array($idDetallesEvento));

		if (!empty($dias)) {
			$dias = $dias[0];
			$dias["hits"] = $dao -> consultaGenerica("SELECT * FROM DiaHit WHERE idDiaEvento = ?", array(
				$dias["idDiaEvento"]
			));
		}
		
		$evento["dias"] = $dias;
		$playeras["tamanyo"] = $dao -> consultaGenerica("SELECT * FROM TamPlayera");
		
		return array(
			"evento" => $evento,
			"equipo" => $equipo,
			"playeras" => $playeras
		);
	}
}