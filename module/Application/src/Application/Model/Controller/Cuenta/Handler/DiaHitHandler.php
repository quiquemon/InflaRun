<?php
namespace Application\Model\Controller\Cuenta\Handler;

use Application\Model\Dao\DiaHitDao;

/**
 * Clase con métodos auxiliares para manejar la relación 'DiaHit'
 * de la base de datos.
 *
 */
class DiaHitHandler {
	
	/**
	 * Obtiene el número de lugares restantes del hit indicado.
	 * 
	 * @param int $idDiaHit La llave primaria del hit a consultar.
	 * @return int El número de lugares restantes de ese hit.
	 * @throws Exception
	 */
	public static function obtenerLugaresRestantes($idDiaHit) {
		$dao = new DiaHitDao();
		return $dao
			-> consultaGenerica("SELECT * FROM DiaHit WHERE idDiaHit = ?", array($idDiaHit))[0]["lugaresRestantes"];
	}
	
	/**
	 * Incrementa el número de lugares restantes en uno.
	 * 
	 * @param int $idDiaHit La llave primaria del hit a modificar.
	 * @throws Exception
	 */
	public static function incrementarLugaresRestantes($idDiaHit, $numero = 1) {
		$dao = new DiaHitDao();
		$dao -> sentenciaGenerica("UPDATE DiaHit SET lugaresRestantes = lugaresRestantes + $numero WHERE idDiaHit = ?", array(
			$idDiaHit
		));
	}
	
	/**
	 * Decrementa el número de lugares restantes en uno.
	 * 
	 * @param int $idDiaHit La llave primaria del hit a modificar.
	 * @throws Exception
	 */
	public static function decrementarLugaresRestantes($idDiaHit, $numero = 1) {
		$dao = new DiaHitDao();
		$lugares = self::obtenerLugaresRestantes($idDiaHit);
		
		if ($numero > $lugares)
			throw new \Exception("Lo sentimos, los lugares de este bloque se han agotado", 45000);
		
		$dao -> sentenciaGenerica("UPDATE DiaHit SET lugaresRestantes = lugaresRestantes - $numero WHERE idDiaHit = ?", array(
			$idDiaHit
		));
	}
}
