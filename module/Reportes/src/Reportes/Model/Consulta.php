<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of consultas
 *
 * @author qnhama
 */

namespace Reportes\Model;
use Application\Model\Dao\ConexionDao;


class Consulta {
    public $idEvento;
    public $idConsulta;
    public  $consultas;
   
    public function __construct() {
		
	}
        
        public function getConsultas(){
            $this -> consultas =Array(
                    1 =>    [
                                descripcion => "Usuarios inscritos al Newsletter ",
                                columnas => ["correo"],
                                consulta => "select  DISTINCT  c.correo from Correo c, Usuario u, UsuarioEquipo ue, Equipo e, DiaHit dh, DiaEvento de, DetallesEvento dte where u.recibeCorreos = 1 and c.idcorreo=u.idcorreo and u.idUsuario = ue.idUsuario AND ue.idEquipo = e.idEquipo AND e.idDiaHit = dh.idDiaHit AND dh.idDiaEvento = de.idDiaEvento AND de.idDetallesEvento = dte.idDetallesEvento and dte.idDetallesEvento = ".$this->idEvento
                            ],
                    2 => array(
                                descripcion => "Lugares disponibles",
                                columnas => ["horario","Numero de lugares","lugaresRestantes","Total Ocupados"],
                                consulta => "select    dh.horario,  dh.noLugares, dh.lugaresRestantes, dh.noLugares - dh.lugaresRestantes as Totalocupados FROM   DiaHit dh, DiaEvento de, DetallesEvento dte  where  dh.idDiaEvento = de.idDiaEvento AND de.idDetallesEvento = dte.idDetallesEvento and dte.idDetallesEvento = ".$this->idEvento
                            ),
                    3 => array(
                                descripcion => "Total de inscritos confirmados",
                                columnas => ["ddsf","idEquipo","nombre","noIntegrantes"],
                                consulta => "SELECT  e.idEquipo, e.nombre, e.noIntegrantes FROM Correo c, Usuario u, UsuarioEquipo ue, Equipo e, DiaHit dh, DiaEvento de, DetallesEvento dte WHERE ue.idEquipo = e.idEquipo and u.idUsuario = ue.idUsuario AND ue.idEquipo = e.idEquipo AND e.idDiaHit = dh.idDiaHit AND dh.idDiaEvento = de.idDiaEvento AND de.idDetallesEvento = dte.idDetallesEvento and dte.idDetallesEvento = ".$this->idEvento." GROUP BY e.idEquipo"
                            ),
                    4 => array(
                                descripcion => "Total de usuarios inscritos",
                                columnas => ["correo","nombre","apellido paterno","apellido materno"],
                                consulta => "SELECT DISTINCT c.correo, u.nombre, u.aPaterno, u.aMaterno FROM    Correo c, Usuario u, UsuarioEquipo ue,Equipo e, DiaHit dh, DiaEvento de, DetallesEvento dte WHERE c.idCorreo=u.idCorreo AND u.idUsuario = ue.idUsuario AND ue.idEquipo = e.idEquipo AND e.idDiaHit = dh.idDiaHit AND dh.idDiaEvento = de.idDiaEvento AND de.idDetallesEvento = dte.idDetallesEvento and dte.idDetallesEvento = ".$this->idEvento
                            ),
                    5 => array(
                                descripcion => "Usuarios inscritos al Newsletter",
                                columnas =>[ "correo"],
                                consulta => "select c.correo from Correo c, Usuario u where u.recibeCorreos = 1 and c.idcorreo =u.idcorreo"
                            )
                );
            return $this->consultas;
        }

        public function generarConsulta(){
        $dao = new ConexionDao();
        $consultas = $this->getConsultas();
        $query = $dao -> consultaGenerica($consultas[$this->idConsulta]['consulta']);
        $jsondata = array();
        $jsondata["success"] = true;
        $jsondata["data"]["message"] = sprintf("Se han encontrado ". sizeof($query)." elementos");
        $jsondata["data"]["descripcion"] = sprintf($this->consultas[$this->idConsulta]['descripcion']);
        $jsondata["data"]["columnas"] = $this->consultas[$this->idConsulta]['columnas']; 
        $jsondata["data"]["idEvento"] = $this->idEvento; 
        $jsondata["data"]["idConsulta"] = $this->idConsulta; 
        $jsondata["data"]["users"] = array();
        foreach ($query as $key => $value){
            $jsondata["data"]["users"][$key] = $value;
        }
        return $jsondata;
    }
}


/*----- Descripcion de consultas -------
 * 1 => Usuarios inscritos al Newsletter
 * 
 */