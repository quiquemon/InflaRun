<?php
namespace Application\Model\Dao;

use Application\Model\Pojo\DetallesEvento;

class DetallesEventoDao extends ConexionDao {
	
	public function buscar($obj) {
		if ($obj instanceof DetallesEvento) {
			$rs = $this -> db
				-> createStatement("SELECT * FROM DetallesEvento WHERE idDetallesEvento = ?", array($obj -> getIdDetallesEvento()))
				-> execute();

			if ($rs -> count() > 0) {
				$result = $rs -> current();
				return ((new DetallesEvento())
					-> setIdDetallesEvento($result["idDetallesEvento"])
					-> setNombre($result["nombre"])
					-> setDireccion($result["direccion"])
					-> setCorreoContacto($result["correoContacto"])
					-> setPrecio($result["precio"])
					-> setRealizado($result["realizado"])
					-> setIdEstado($result["idEstado"])
					-> setIdEvento($result["idEvento"]));
			}
		}
		
		return null;
	}

	public function buscarTodos() {
		$rs = $this -> db -> query("SELECT * FROM DetallesEvento") -> execute();
		$lista = array();
		if ($rs -> count() > 0) {
			foreach($rs as $row) {
				$lista[] = (new DetallesEvento())
					-> setIdDetallesEvento($row["idDetallesEvento"])
					-> setNombre($row["nombre"])
					-> setDireccion($row["direccion"])
					-> setCorreoContacto($row["correoContacto"])
					-> setPrecio($row["precio"])
					-> setRealizado($row["realizado"])
					-> setIdEstado($row["idEstado"])
					-> setIdEvento($row["idEvento"]);
			}
		}
		
		return $lista;
	}
	
	public function insertar($obj) {
		$sql = "INSERT INTO DetallesEvento(nombre, direccion, correoContacto, precio, realizado, idEstado, idEvento)"
			. " VALUES (?, ?, ?, ?, ?, ?, ?)";
		
		if ($obj instanceof DetallesEvento) {
			$this -> db
				-> createStatement($sql, array(
					$obj -> getNombre(),
					$obj -> getDireccion(),
					$obj -> getCorreoContacto(),
					$obj -> getPrecio(),
					$obj -> getRealizado(),
					$obj -> getIdEstado(),
					$obj -> getIdEvento()
				))
				-> execute();
		}
	}
	
	public function actualizar($obj, $nuevo) {
		if ($obj instanceof DetallesEvento && $nuevo instanceof DetallesEvento) {
			$sql = "UPDATE DetallesEvento SET nombre = ?, direccion = ?, correoContacto = ?, precio = ?, realizado = ?, idEstado = ?, idEvento = ?"
				. " WHERE idDetallesEvento = ?";
			$this -> db
				-> createStatement($sql, array(
					$nuevo -> getNombre(),
					$nuevo -> getDireccion(),
					$nuevo -> getCorreoContacto(),
					$nuevo -> getPrecio(),
					$nuevo -> getRealizado(),
					$nuevo -> getIdEstado(),
					$nuevo -> getIdEvento(),
					$obj -> getIdDetallesEvento()
				))
				-> execute();
		}
	}

	public function eliminar($obj) {
		if ($obj instanceof DetallesEvento) {
			$this -> db
				-> createStatement("DELETE FROM DetallesEvento WHERE idDetallesEvento = ?", array($obj -> getIdDetallesEvento()))
				-> execute();
		}
	}
}