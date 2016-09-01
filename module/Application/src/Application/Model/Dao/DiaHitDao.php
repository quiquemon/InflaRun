<?php
namespace Application\Model\Dao;

use Application\Model\Pojo\DiaHit;

class DiaHitDao extends ConexionDao {
	
	public function buscar($obj) {
		if ($obj instanceof DiaHit) {
			$rs = $this -> db
				-> createStatement("SELECT * FROM DiaHit WHERE idDiaHit = ?", array($obj -> getIdDiaHit()))
				-> execute();
			
			if ($rs -> count() > 0) {
				$result = $rs -> current();
				return (new DiaHit())
					-> setIdDiaHit($result["idDiaHit"])
					-> setHorario($result["horario"])
					-> setNoLugares($result["noLugares"])
					-> setLugaresRestantes($result["lugaresRestantes"])
					-> setIdDiaEvento($result["idDiaEvento"]);
			}
		}
		
		return null;
	}

	public function buscarTodos() {
		$rs = $this -> db -> query("SELECT * FROM DiaHit") -> execute();
		$lista = array();
		if ($rs -> count() > 0) {
			foreach($rs as $row) {
				$lista[] = (new DiaHit())
					-> setIdDiaHit($row["idDiaHit"])
					-> setHorario($row["horario"])
					-> setNoLugares($row["noLugares"])
					-> setLugaresRestantes($row["lugaresRestantes"])
					-> setIdDiaEvento($row["idDiaEvento"]);
			}
		}
		
		return $lista;
	}
	
	public function insertar($obj) {
		$sql = "INSERT INTO DiaHit(horario, noLugares, lugaresRestantes, idDiaEvento) VALUES (?, ?, ?, ?)";
		
		if ($obj instanceof DiaHit) {
			$this -> db
				-> createStatement($sql, array(
					$obj -> getHorario(),
					$obj -> getNoLugares(),
					$obj -> getLugaresRestantes(),
					$obj -> getIdDiaEvento()
				))
				-> execute();
		}
	}
	
	public function actualizar($obj, $nuevo) {
		$sql = "UPDATE DiaHit SET horario = ?, noLugares = ?, lugaresRestantes = ?, idDiaEvento = ? WHERE idDiaHit = ?";
		
		if ($obj instanceof DiaHit && $nuevo instanceof DiaHit) {
			$this -> db
				-> createStatement($sql, array(
					$nuevo -> getHorario(),
					$nuevo -> getNoLugares(),
					$nuevo -> getLugaresRestantes(),
					$nuevo -> getIdDiaEvento(),
					$obj -> getIdDiaHit()
				))
				-> execute();
		}
	}

	public function eliminar($obj) {
		if ($obj instanceof DiaHit) {
			$this -> db
				-> createStatement("DELETE FROM DiaHit WHERE idDiaHit = ?", array($obj -> getIdDiahit()))
				-> execute();
		}
	}
}