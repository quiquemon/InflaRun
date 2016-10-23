<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Model\Correos;
use Application\Model\Correos\Mailin;

/**
 * Description of CorreoInstrucciones
 *
 * @author qnhama
 */
class CorreoInstrucciones extends Correos{
    //put your code here
   private $params;
	
	/**
	 * @param Array $params Arreglo asociativo que incluye la información
	 * del usuario registrado.
	 */
	public function __construct($params) {
		parent::__construct(true);
		$this -> params = $params;
	}

    public function enviarSendinblue($correo, $nombre = "Destinatario") {
		try {
			$mailin = new Mailin("https://api.sendinblue.com/v2.0","NQGOIrgFEVKYxp51");   
                        
                        $this ->Barcodebakery($this -> params["folio"]); 
                        
                        $imagedata = file_get_contents( __DIR__ . "/plantillas/banner-inscripcion.png");
                        $base64 = base64_encode($imagedata);
                        $barcode = file_get_contents( __DIR__ . "/images/barcode.png");
                        $base65 = base64_encode($barcode);


                        $data = array( "to" => array($correo =>"to whom!"),
                        "from" => array("ti@numeri.mx", "InflaRun"),
                        "subject" => "¡Instrucciones de pago InflaRun!",
                        "html" => $this -> generarHtmlBody2(),
                        "headers" => array("Content-Type"=> "text/html; charset=iso-8859-1","X-param1"=> "value1", "X-param2"=> "value2","X-Mailin-custom"=>"my custom value", "X-Mailin-IP"=> "102.102.1.2", "X-Mailin-Tag" => "My tag"),
                        "inline_image" => array(
                            __DIR__ . "/plantillas/banner-inscripcion.png" => $base64,
                            __DIR__ . "/images/barcode.png" => $base65
                         ));

                        var_dump($mailin->send_email($data)); 
			return true;
		} catch (\Exception $e) {
			return $e -> errorMessage();
		}
	}
        
        private function generarHtmlBody2() {
		$nombre = $this -> params["nombre"];
		$paterno = $this -> params["paterno"];
		$materno = $this -> params["materno"];
		$sexo = $this -> params["sexo"];
		$fechaNacimiento = $this -> params["fechaNacimiento"];
		$noCorredor = $this -> params["noCorredor"];
		$carrera = $this -> params["carrera"];
		$fecha = $this -> params["fecha"];
		$hit = $this -> params["hit"];
		$direccion = $this -> params["direccion"];
		$uuid = $this -> params["uuid"];
		$folio = $this -> params["folio"];
		$tipoPago = $this -> params["tipoPago"];
		$precio = $this -> params["precio"];
		$equipo = $this -> params["equipo"];
                
		
		$html = 
			  "
                <html>
                                <head>
                                        <title>Inscrito en InflaRun</title>
                                        <meta charset='UTF-8'>
                                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                                        <style></style>
                                </head>
                                <body>

                                    <table border='0' cellpadding='0' cellspacing='0' width='100%'> 
                                        <tr>
                                          <td style='padding: 10px 0 30px 0;'>
                                            <table align='center' border='0' cellpadding='0' cellspacing='0' width='600' style='border: 1px solid #cccccc; border-collapse: collapse;'>
                                              <tr>
                                                <td align='center'  style=' color: #153643; font-size: 28px; font-weight: bold; font-family: Arial, sans-serif;'>
                                                  <img src=\"{".__DIR__ . "/plantillas/banner-inscripcion.png"."}\" alt='image1' width='100%'  style='display: block;' border='0'><br/>
                                                </td>
                                              </tr>
                                              <tr>
                                                <td bgcolor='#ffffff' style='padding: 40px 30px 40px 30px;'>
                                                  <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                                                    <tr>
                                                      <td style='color: #153643; font-family: Arial, sans-serif; font-size: 24px;'>
                                                        <b>Estimado(a):". $nombre."</b>
                                                      </td>
                                                    </tr>
                                                    <tr>
                                                      <td style='padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;'>
                                                        <div class='container-fluid'>
                                                                            <div class='row'>"
                                                                                . "        <p>Agradecemos su inscripción a la carrera <strong>$carrera</strong> y anexamos su comprobante de inscripción. </p>"
                                                                                . "        <br>"
                                                                                . "        <h2>INFORMACIÓN PERSONAL</h2>"
                                                                                . "        <p><strong>Nombre:</strong> $nombre<p>"
                                                                                . "        <p><strong>Apellido paterno:</strong> $paterno</p>"
                                                                                . "        <p><strong>Apellido materno:</strong> $materno</p>"
                                                                                . "        <p><strong>Sexo:</strong> $sexo</p>"
                                                                                . "        <p><strong>Fecha de nacimiento:</strong> $fechaNacimiento</p>"
                                                                                . "        <br>"
                                                                                . "        <h2>INFORMACIÓN DE LA CARRERA</h2>"
                                                                                . "        <p><strong>Carrera:</strong> $carrera</p>"
                                                                                . "        <p><strong>Fecha:</strong> $fecha</p>"
                                                                                . "        <p><strong>Horario:</strong> $hit</p>"
                                                                                . "        <p><strong>Dirección:</strong> $direccion</p>"
                                                                                . "        <br>"
                                                                                . "        <h2>INFORMACIÓN DE LA INSCRIPCIÓN</h2>"
                                                                                . "        <p><strong>UUID de inscripción:</strong> $uuid</p>"
                                                                                . "        <p><strong>Folio de inscripción:</strong> $folio</p>"
                                                                                . "        <p><strong>Tipo de pago:</strong> $tipoPago</p>"
                                                                                . "        <p><strong>Precio:</strong> $precio</p>"
                                                                                . "        <p><strong>Equipo:</strong> $equipo</p>"
                                                                                . "        <p><strong>Número de corredor:</strong> $noCorredor</p>"
                                                                                . "
                                                                            <br>
                                                                            <p>Imprime y firma esta forma. Llévala al registro para recoger tu paquete. Recuerda también llevar una
                                                                            <strong>identificación oficial</strong>.</p>
                                                                            <h3>FIRMA</h3>
                                                                            <p>______________________________________________</p>
                                                                            <br>




                                                                                <div class='text-justify'>
                                                                                <h4>Términos y Condiciones</h4>
                                                                                <p>Admito que al firmar este documento conozco las bases de la convocatoria, que mis datos son verdaderos y
                                                                                si fueran falsos seré descalificado del evento. Soy el único responsable de mi salud y de cualquier accidente
                                                                                o deficiencia que pudiera causar alteración a mi salud física e incluso la muerte. Por esta razón libero al
                                                                                comité organizador, a los patrocinadores, a las autoridades deportivas y a los prestadores de servicios de
                                                                                cualquier daño que sufra. Así mismo, autorizo al comité organizador para utilizar mi imagen, voz y nombre, ya sea
                                                                                total o parcialmente en lo relacionado al evento. Estoy conciente de que para participar en esta competencia debo
                                                                                estar físicamente preparado para el esfuerzo que voy a realizar.</p>
                                                                                <br>
                                                                                <h4>Exoneraciones</h4>
                                                                                <p>El paquete de correrdor se entregará el día del evento una hora antes del horario que seleccione al momento de
                                                                                inscribirme. En tu paquete recibirás el número de competidor, tu playera y tu kit de participante. Es obligatorio
                                                                                que el titular de la inscripción se presente a la entrega de paquetes a recoger el mismo llevando consigo una
                                                                                identificación. No habrá módulo de entrega de grupos y no se le entregará paquete de competidor a ninguna otra persona
                                                                                que no sea el titular de la inscripción y únicamente mostrando una identificación oficial. El corredor que por cualquier
                                                                                motivo no recoja su paquete en el lugar y horario indicados perderá todo derecho derivado de su inscripción. Por ningún
                                                                                motivo habrá cambio de horario ni de datos de competidor ni se permitirá transferir números.</p>
                                                                            </div>




                                                                        </div>
                                                                    </div>
                                                      </td>
                                                    </tr>
                                                    <tr>
                                                      <td>
                                                        <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                                                          <tr>
                                                            <td width='260'  valign='top'>
                                                              <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                                                                <tr>
                                                                  <td>
                                                                    <img src=\"{".__DIR__ . "/images/barcode.png"."}\" alt='' width='100%' height='140'  style='display: block;' />
                                                                  </td>
                                                                </tr>
                                                                <tr>

                                                                </tr>
                                                              </table>
                                                            </td>
                                                            <td style='font-size: 0; line-height: 0;' width='20'>
                                                              &nbsp;
                                                            </td>
                                                            <td width='260' valign='top'>

                                                            </td>
                                                          </tr>
                                                        </table>
                                                      </td>
                                                    </tr>
                                                  </table>
                                                </td>
                                              </tr>
                                              <tr>
                                                <td bgcolor='#ee4c50' style='padding: 30px 30px 30px 30px;'>
                                                  <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                                                    <tr>
                                                      <td style='color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;' width='75%'>
                                                        &reg; InflaRun, México 2016<br/>

                                                        <!-- <a href='#' style='color: #ffffff;'><font color='#ffffff'>Unsubscribe</font></a> to this newsletter instantly -->
                                                      </td>
                                                      <td align='right' width='25%'>
                                                        <table border='0' cellpadding='0' cellspacing='0'>
                                                          <tr>
                                                            <td style='font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;'>
                                                              <a href='http://www.twitter.com/' style='color: #ffffff;'>
                                                                <img src='images/tw.gif' alt='Twitter' width='38' height='38' style='display: block;' border='0' />
                                                              </a>
                                                            </td>
                                                            <td style='font-size: 0; line-height: 0;' width='20'>&nbsp;</td>
                                                            <td style='font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;'>
                                                              <a href='http://www.twitter.com/' style='color: #ffffff;'>
                                                                <img src='images/fb.gif' alt='Facebook' width='38' height='38' style='display: block;' border='0' />
                                                              </a>
                                                            </td>
                                                          </tr>
                                                        </table>
                                                      </td>
                                                    </tr>
                                                  </table>
                                                </td>
                                              </tr>
                                            </table>
                                          </td>
                                        </tr>
                                      </table>
                                    <br>
                                    
                            </body>
      </html>";
		
		return $html;
	}

}
