<?php
namespace Application\Model\Dao;

use Application\Model\Pojo\Equipo;

class EquipoDao extends ConexionDao {
	
	public function buscar($obj) {
		if ($obj instanceof Equipo) {
			$rs = $this -> db
				-> createStatement("SELECT * FROM Equipo WHERE idEquipo = ?", array($obj -> getIdEquipo()))
				-> execute();
			
			if ($rs -> count() > 0) {
				$result = $rs -> current();
				return (new Equipo())
					-> setIdEquipo($result["idEquipo"])
					-> setNombre($result["nombre"])
					-> setCodigoCanje($result["codigoCanje"])
					-> setNoIntegrantes($result["noIntegrantes"])
					-> setIdDiaHit($result["idDiaHit"]);
			}
		}
		
		return null;
	}

	public function buscarTodos() {
		$rs = $this -> db -> query("SELECT * FROM Estado") -> execute();
		$lista = array();
		if ($rs -> count() > 0) {
			foreach($rs as $row) {
				$lista[] = (new Equipo())
					-> setIdEquipo($row["idEquipo"])
					-> setNombre($row["nombre"])
					-> setCodigoCanje($row["codigoCanje"])
					-> setNoIntegrantes($row["noIntegrantes"])
					-> setIdDiaHit($row["idDiaHit"]);
			}
		}
		
		return $lista;
	}
	
	public function insertar($obj) {
		$sql = "INSERT INTO Equipo(nombre, codigoCanje, noIntegrantes, idDiaHit) VALUES (?, ?, ?, ?)";
		
		if ($obj instanceof Equipo) {
			$this -> db
				-> createStatement($sql, array(
					$obj -> getNombre(),
					$obj -> getCodigoCanje(),
					$obj -> getNoIntegrantes(),
					$obj -> getIdDiaHit()
				))
				-> execute();
		}
	}
	
	public function actualizar($obj, $nuevo) {
		$sql = "UPDATE Equipo SET nombre = ?, codigoCanje = ?, noIntegrantes = ?, idDiaHit = ? WHERE idEquipo = ?";
		
		if ($obj instanceof Equipo && $nuevo instanceof Equipo) {
			$this -> db
				-> createStatement($sql, array(
					$nuevo -> getNombre(),
					$nuevo -> getCodigoCanje(),
					$nuevo -> getNoIntegrantes(),
					$nuevo -> getIdDiaHit(),
					$obj -> getIdEquipo()
				))
				-> execute();
		}
	}

	public function eliminar($obj) {
		if ($obj instanceof Equipo) {
			$this -> db
				-> createStatement("DELETE FROM Equipo WHERE idEquipo = ?", array($obj -> getIdEquipo()))
				-> execute();
		}
	}
}