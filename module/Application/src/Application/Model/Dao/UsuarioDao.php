<?php
namespace Application\Model\Dao;

use Application\Model\Pojo\Usuario;

class UsuarioDao extends ConexionDao {
	
	public function buscar($obj) {
		if ($obj instanceof Usuario) {
			$rs = $this -> db
				-> createStatement("SELECT * FROM Usuario WHERE idUsuario = ?", array($obj -> getIdUsuario()))
				-> execute();

			if ($rs -> count() > 0) {
				$result = $rs -> current();
				return (new Usuario())
					-> setIdUsuario($result["idUsuario"])
					-> setNombre($result["nombre"])
					-> setAPaterno($result["aPaterno"])
					-> setAMaterno($result["aMaterno"])
					-> setCorreo($result["correo"])
					-> setPassword($result["password"])
					-> setSexo($result["sexo"])
					-> setFechaNacimiento($result["fechaNacimiento"])
					-> setFechaRegistro($result["fechaRegistro"])
					-> setRecibeCorreos($result["recibeCorreos"])
					-> setIdEstado($result["idEstado"]);
			}
		}
		
		return null;
	}

	public function buscarTodos() {
		$rs = $this -> db -> query("SELECT * FROM Usuario") -> execute();
		$lista = array();
		if ($rs -> count() > 0) {
			foreach($rs as $row) {
				$lista[] = (new Usuario())
					-> setIdUsuario($row["idUsuario"])
					-> setNombre($row["nombre"])
					-> setAPaterno($row["aPaterno"])
					-> setAMaterno($row["aMaterno"])
					-> setCorreo($row["correo"])
					-> setPassword($row["password"])
					-> setSexo($row["sexo"])
					-> setFechaNacimiento($row["fechaNacimiento"])
					-> setFechaRegistro($row["fechaRegistro"])
					-> setRecibeCorreos($row["recibeCorreos"])
					-> setIdEstado($row["idEstado"]);
			}
		}
		
		return $lista;
	}

	public function insertar($obj) {
		$sql = "INSERT INTO Usuario(nombre, aPaterno, aMaterno, correo, password, sexo, fechaNacimiento, fechaRegistro, recibeCorreos, idEstado)"
			. " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		
		if ($obj instanceof Usuario) {
			$this -> db
				-> createStatement($sql, array(
					$obj -> getNombre(),
					$obj -> getAPaterno(),
					$obj -> getAMaterno(),
					$obj -> getCorreo(),
					$obj -> getPassword(),
					$obj -> getSexo(),
					$obj -> getFechaNacimiento(),
					$obj -> getFechaRegistro(),
					$obj -> getRecibeCorreos(),
					$obj -> getIdEstado()
				))
				-> execute();
		}
	}
	
	public function actualizar($obj, $nuevo) {
		if ($obj instanceof Usuario && $nuevo instanceof Usuario) {
			$sql = "UPDATE Usuario SET nombre = ?, aPaterno = ?, aMaterno= ?, correo = ?, password = ?, sexo = ?,"
				. " fechaNacimiento = ?, fechaRegistro = ?, recibeCorreos = ?, idEstado = ? WHERE idUsuario = ?";
			$this -> db
				-> createStatement($sql, array(
					$nuevo -> getNombre(),
					$nuevo -> getAPaterno(),
					$nuevo -> getAMaterno(),
					$nuevo -> getCorreo(),
					$nuevo -> getPassword(),
					$nuevo -> getSexo(),
					$nuevo -> getFechaNacimiento(),
					$nuevo -> getFechaRegistro(),
					$nuevo -> getRecibeCorreos(),
					$nuevo -> getIdEstado(),
					$obj -> getIdUsuario()
				))
				-> execute();
		}
	}

	public function eliminar($obj) {
		if ($obj instanceof Usuario) {
			$this -> db
				-> createStatement("DELETE FROM Usuario WHERE idUsuario = ?", array($obj -> getIdUsuario()))
				-> execute();
		}
	}
}