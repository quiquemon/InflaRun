<?php
namespace Application\Model\Dao;

use Application\Model\Pojo\Estado;

class EstadoDao extends ConexionDao {
	
	public function buscar($obj) {
		if ($obj instanceof Estado) {
			$rs = $this -> db
				-> createStatement("SELECT * FROM Estado WHERE idEstado = ?", array($obj -> getIdEstado()))
				-> execute();
			
			if ($rs -> count() > 0) {
				$result = $rs -> current();
				return (new Estado()) -> setIdEstado($result["idEstado"]) -> setNombre($result["nombre"]);
			}
		}
		
		return null;
	}

	public function buscarTodos() {
		$rs = $this -> db -> query("SELECT * FROM Estado") -> execute();
		$lista = array();
		if ($rs -> count() > 0) {
			foreach($rs as $row) {
				$lista[] = (new Estado())
					-> setIdEstado($row["idEstado"])
					-> setNombre($row["nombre"]);
			}
		}
		
		return $lista;
	}

	public function insertar($obj) {
		if ($obj instanceof Estado) {
			$this -> db
				-> createStatement("INSERT INTO Estado(nombre) VALUES (?)", array($obj -> getNombre()))
				-> execute();
		}
	}

	public function actualizar($obj, $nuevo) {
		if ($obj instanceof Estado && $nuevo instanceof Estado) {
			$this -> db
				-> createStatement("UPDATE Estado SET nombre = ? WHERE idEstado = ?",
					array($nuevo -> getNombre(), $obj -> getIdEstado()))
				-> execute();
		}
	}
	
	public function eliminar($obj) {
		if ($obj instanceof Estado) {
			$this -> db
				-> createStatement("DELETE FROM Estado WHERE idEstado = ?", array($obj -> getIdEstado()))
				-> execute();
		}
	}
}