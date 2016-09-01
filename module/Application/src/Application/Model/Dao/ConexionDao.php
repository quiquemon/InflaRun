<?php
namespace Application\Model\Dao;

use Zend\Db\Adapter\Adapter;

abstract class ConexionDao {
	protected $db;
	private $driver = "Pdo_Mysql";
	private $database = "numerico_inflarundb";
	private $hostname = "localhost";
	private $username = "numerico_InflaDB";
	private $password = "\$\$InflaRunDBUserPass256";
	
	public function __construct($hostname = "localhost") {
		$this -> db = new Adapter(array(
			"driver" => $this -> driver,
			"database" => $this -> database,
			"username" => $this -> username,
			"password" => $this -> password,
			"hostname" => $hostname,
			"charset" => "UTF8"
		));
	}
	
	/**
	 * Ejecuta una consulta genérica a la base de datos.
	 * 
	 * @param String $sql La consulta SQL a ejecutar.
	 * @param Array $params Parámetros opcionales.
	 * @return Array(String,Object) Una lista de pares (columna, valor). Si la consulta no obtuvo
	 * resultados regresa una lista vacía.
	 * @throws Exception
	 */
	public function consultaGenerica($sql, $params = null) {
		$rs = $this -> sentenciaGenerica($sql, $params);
		$lista = array();
		if ($rs -> count() > 0) {
			foreach ($rs as $row)
				$lista[] = $row;
		}
		return $lista;
	}
	
	/**
	 * Ejecuta una sentencia genérica a la base de datos.
	 * 
	 * @param string $sql La sentencia SQL a ejecutar.
	 * @param Array $params Parámetros opcionales.
	 * @return ResultSet El objeto ResultSet obtenido al ejecutar la sentencia.
	 */
	public function sentenciaGenerica($sql, $params = null) {
		if ($params === null)
			$rs = $this -> db -> query($sql)-> execute();
		else
			$rs = $this -> db -> createStatement($sql, $params) -> execute();
		
		return $rs;
	}
	
	/**
	 * Inicia una nueva transacción.
	 * 
	 * @throws Exception
	 */
	public function beginTransaction() {
		$this -> db -> getDriver() -> getConnection() -> beginTransaction();
	}
	
	/**
	 * Termina la transacción y actualiza la base de datos.
	 * 
	 * @throws Exception
	 */
	public function commit() {
		$this -> db -> getDriver() -> getConnection() -> commit();
	}
	
	/**
	 * Termina la transacción y descarta todos los cambios hechos
	 * hasta el momento.
	 * 
	 * @throws Exception
	 */
	public function rollback() {
		$this -> db -> getDriver() -> getConnection() -> rollback();
	}
	
	/**
	 * Busca el registro indicado en la base de datos por su llave primaria.
	 * 
	 * @param POJO $obj Objeto POJO. Solo es necesario tener su(s) llave(s) primaria(s),
	 *  sin los otros campos.
	 * @return InflaRunDB Un objeto POJO. Si la consulta no obtuvo un resultado regresa null.
	 * @throws Exception
	 */
	public abstract function buscar($obj);
	
	/**
	 * Regresa todos los registros de la tabla en específico.
	 * @return Array(POJO) Una lista de objetos POJO, con todos sus campos rellenos. Si la consulta no obtuvo un
	 *  resultado regresa una lista vacía.
	 * @throws Exception
	 */
	public abstract function buscarTodos();
	
	/**
	 * Inserta un nuevo registro en la base de datos.
	 * @param POJO $obj Objeto POJO a insertar.
	 * @throws Exception
	 */
	public abstract function insertar($obj);
	
	/**
	 * Actualiza un registro en la base de datos.
	 * @param POJO $obj Objeto POJO. Solo es necesario que tenga la(s) llave(s) primaria(s) del registro a modificar.
	 * @param POJO $nuevo Objeto POJO que contiene los nuevo datos a insertar.
	 * @throws Exception
	 */
	public abstract function actualizar($obj, $nuevo);
	
	/**
	 * Elimina un registro de la base de datos.
	 * @param POJO $obj Objeto POJO. Solo es necesario que tenga la(s) llave(s) primaria(s) del registro a eliminar.
	 * @throws Exception
	 */
	public abstract function eliminar($obj);
}