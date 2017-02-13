<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Reportes\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Http\Headers;


use Reportes\Model\Consulta;
use Application\Model\Dao\ConexionDao;


class IndexController extends AbstractActionController
{
   
    public function eventosAction()
    {   
        try {
                $dao = new ConexionDao();
                $eventos = $dao -> consultaGenerica("SELECT * FROM DetallesEvento  ORDER BY idDetallesEvento DESC");
                for ($i = 0; $i < count($eventos); $i++) {
                        $sql = "SELECT DISTINCT de.* FROM DiaEvento de, DiaHit dh WHERE de.idDiaEvento = dh.idDiaEvento AND"
                                . " de.idDetallesEvento = ? AND dh.lugaresRestantes > 0";
                        $dias = $dao -> consultaGenerica($sql, array($eventos[$i]["idDetallesEvento"]));
                        $eventos[$i]["dias"] = empty($dias) ? array() : $dias[0];
                        $eventos[$i]["estado"] = $dao -> consultaGenerica("SELECT * FROM Estado WHERE idEstado = ?",
                                array($eventos[$i]["idEstado"]))[0];
                }
                
                return new ViewModel(array("eventos" => $eventos));
        } catch (\Exception $ex) {
                $eventos = array();
                return new ViewModel(array(
                        "Alert" => array(
                                "code" => 1,
                                "message" => $ex -> getMessage()
                        )
                ));
        }
    }

  
   public function displayListUsersAction(){
        $idEvento =  $this->params()->fromRoute("id", null);
        $idConsulta = $this -> params() -> fromQuery("idConsulta", "");
        $consulta = new Consulta();
        $consulta -> idEvento =$idEvento;
        $consulta -> idConsulta =$idConsulta;
        $jsondata = $consulta -> generarConsulta();
        return new JsonModel($jsondata);
        
    }
    
    public function exportXlsAction()
    {
        set_time_limit( 0 );
        $idEvento =  $this->params()->fromRoute("id", null);
        $idConsulta = $this->params()->fromRoute("name", null);
        $consulta = new Consulta();
        $consulta -> idEvento =$idEvento;
        $consulta -> idConsulta =$idConsulta;
        $jsondata = $consulta -> generarConsulta();

        $filename = __DIR__ . "/tmp/excel-" . date( "m-d-Y" ) . ".xls";

        $realPath = realpath( $filename );

        if ( false === $realPath )
        {
            touch( $filename );
            chmod( $filename, 0777 );
        }

        $filename = realpath( $filename );
        $handle = fopen( $filename, "w" );
        $finalData = array();
        
        foreach ( $jsondata['data']['users'] AS $row )
        {
            $finalData[] = array(
                utf8_decode( $row["col1"] ), // For chars with accents.
                utf8_decode( $row["col2"] ),
                utf8_decode( $row["col3"] ),
            );
        }

        foreach ( $jsondata['data']['users'] AS $finalRow )
        {
            fputcsv( $handle, $finalRow, "\t" );
        }

        fclose( $handle );
        $response = new \Zend\Http\Response\Stream();
        $response->setStream(fopen($filename, 'r'));
        $response->setStatusCode(200);
        $response->setStreamName(basename($filename));
        $headers = new \Zend\Http\Headers();
        $headers->addHeaders(array(
            'Content-Disposition' => 'attachment; filename="' . basename($filename) .'"',
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => filesize($filename),
            'Expires' => '@0', // @0, because zf2 parses date as string to \DateTime() object
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public'
        ));
        $response->setHeaders($headers);
        return $response;
        
    }
    
    public function usersAction(){
        $idEvento =  $this->params()->fromRoute("id", null);
        $consulta = new Consulta();
        $consulta -> idEvento =$idEvento;
        $consultas = $consulta ->getConsultas();
        return  new ViewModel(
            array(
                "idEvento" => $idEvento,
                "consultas" => $consultas
            )
        );
    }
 
    
}
