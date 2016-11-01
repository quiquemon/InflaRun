<?php
namespace Application\Model\Correos;
use \Exception;

use Application\Model\Correos\Mailin;
// -------------  Barcodebakery ----------
use Application\Model\Correos\Barcodebakery\BCGColor;
use Application\Model\Correos\Barcodebakery\BCGDrawing;
use Application\Model\Correos\Barcodebakery\BCGFontFile;
use Application\Model\Correos\Barcodebakery\BCGcode128;

/**
 * Clase que permite enviar correos a los usuarios
 * al momento de terminar su inscripción en una carrera.
 *
 */
class CorreoInscripcion extends Correos {
	
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
                        "subject" => "InflaRun - Comprobante de Inscripción - $nombre",
                        "html" => $this -> generarHtmlBody2(),
                        "headers" => array("Content-Type"=> "text/html; charset=iso-8859-1","X-param1"=> "value1", "X-param2"=> "value2","X-Mailin-custom"=>"my custom value", "X-Mailin-IP"=> "102.102.1.2", "X-Mailin-Tag" => "My tag"),
                        "inline_image" => array(
                            __DIR__ . "/plantillas/banner-inscripcion.png" => $base64,
                            __DIR__ . "/images/barcode.png" => $base65
                         ));

                        $resultado= $mailin->send_email($data); 
			return true;
		} catch (\Exception $e) {
			return $e -> errorMessage();
		}
	}
	
	/**
	 * Genera el código HTML personalizado para la
	 * inscripción del usuario.
	 */
	
	private function generarHtmlBody2() {
		$nombre = $this -> params["nombre"];
		$paterno = $this -> params["paterno"];
		$materno = $this -> params["materno"];
		$nombreUsuario = "{$nombre} {$paterno} {$materno}";
		$sexo = $this -> params["sexo"];
		$fechaNacimiento = $this -> params["fechaNacimiento"];
		$noCorredor = $this -> params["noCorredor"];
		$carrera = $this -> params["carrera"];
		$fecha = $this -> params["fecha"];
		$hit = $this -> params["hit"];
		$direccion = $this -> params["direccion"];
		$uuid = $this -> params["uuid"];
		$folio = $this -> params["folio"];
		$precio = $this -> params["precio"];
		$equipo = $this -> params["equipo"];
                
                /*$dia = $this ->params["fecha"]; 
                $domingo = "Sunday";
                if ( strcmp( $dia  , $domingo )== 1){
                    $diaChido = "Domingo"; 
                }else
                                                                            
                $diaChido = "Sabado"; */
                
                

                //default data 
                
                
                //$this ->Barcodebakery($folio); 
                
		
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
                                                        <b>Estimado(a): ". $nombre."</b>
                                                      </td>
                                                    </tr>
                                                    <tr>
														<td style='padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;'>
															<div class='container-fluid'>
																<div class='row'>"
																		. "        <p>Agradecemos su inscripción a la carrera <strong>$carrera</strong> y anexamos su comprobante de inscripción. </p>"
																		. "        <br>"
																		. "        <h2>INFORMACIÓN PERSONAL</h2>"
																		. "        <p><strong>Nombre:</strong> $nombreUsuario<p>"
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
																		. "        <p><strong>Precio:</strong> \$$precio</p>"
																		. "        <p><strong>Equipo:</strong> $equipo</p>"
																		. "        <p><strong>Número de corredor:</strong> $noCorredor</p>"
																		. "
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
        
        private function barCodeBakery($folio){
            $default_value = array();
            $default_value['filetype'] = 'PNG';
            $default_value['dpi'] = 73;
            $default_value['scale'] =  2;
            $default_value['rotation'] = 0;
            $default_value['font_family'] = 'Arial.ttf';
            $default_value['font_size'] = 22;
            $default_value['text'] = '';
            $default_value['a1'] = '';
            $default_value['a2'] = '';
            $default_value['a3'] = '';
            $default_value['text'] =  45;
            $default_value['start'] = 'NULL';
            $default_value['thickness'] = '50';


            $filetype = $default_value['filetype'];
            $dpi = $default_value['dpi'];
            $scale = $default_value['scale'];
            $rotation = $default_value['rotation'];
            $font_family = $default_value['font_family'];
            $font_size =  $default_value['font_size'];
            $text = $folio;
            $start = $default_value['start'];
            $thickness = $default_value['thickness'];



            $filetypes = array('PNG' => BCGDrawing::IMG_FORMAT_PNG, 'JPEG' => BCGDrawing::IMG_FORMAT_JPEG, 'GIF' => BCGDrawing::IMG_FORMAT_GIF);

                $drawException = null;
                try {
                    $color_black = new BCGColor(0, 0, 0);
                    $color_white = new BCGColor(255, 255, 255);

                    $code_generated = new BCGcode128();


                        $this -> baseCustomSetup($code_generated,$font_size, $font_family, $thickness);

                        $this -> customSetup($code_generated, $start);

                    $code_generated->setScale(max(1, min(4, $scale)));
                    $code_generated->setBackgroundColor($color_white);
                    $code_generated->setForegroundColor($color_black);

                    if ($text !== '') {
                        $text = $this -> convertText($text);
                        $code_generated->parse($text);
                    }
                } catch(Exception $exception) {
                    $drawException = $exception;
                }

                $drawing = new BCGDrawing(__DIR__ . '/images/barcode.png', $color_white);
                //$drawing = new BCGDrawing('', $color_white);



                if($drawException) {
                    $drawing->drawException($drawException);
                } else {
                    $drawing->setBarcode($code_generated);
                    $drawing->setRotationAngle($rotation);
                    $drawing->setDPI($dpi === 'NULL' ? null : max(72, min(300, intval($dpi))));
                    $drawing->draw();
                }

                switch ($filetype) {
                    case 'PNG':
                        header('Content-Type: image/png');
                        break;
                    case 'JPEG':
                        header('Content-Type: image/jpeg');
                        break;
                    case 'GIF':
                        header('Content-Type: image/gif');
                        break;
                }

                $drawing->finish($filetypes[$filetype]);
        }
        
              
        private function customSetup($barcode, $start) {
            if (isset($start)) {
                $barcode->setStart($start === 'NULL' ? null : $start);
            }
        }

        private function baseCustomSetup($barcode, $font_size, $font_family, $thickness) {
            

            if (isset($thickness)) {
                $barcode->setThickness(max(9, min(90, intval($thickness))));
            }

            $font = 0;
            if ($font_family !== '0' && intval($font_size) >= 1) {
                $font = new BCGFontFile( __DIR__ . "/font/Arial.ttf", intval($font_size));
            }

            $barcode->setFont($font);


        }

        private function convertText($text) {
            $text = stripslashes($text);
            if (function_exists('mb_convert_encoding')) {
                $text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
            }

            return $text;
        }
        
        public function enviarCorreo($correo, $nombre = "Destinatario") {
		try {
			$this -> setFrom("admin@inflarun.mx", "InflaRun");
			$this -> addAddress($correo, $nombre);
			$this -> Subject = "¡Te has inscrito con éxito en InflaRun, $nombre!";
			$this -> addEmbeddedImage(__DIR__ . "/plantillas/banner-inscripcion.png", "banner-inscripcion");
			$this -> msgHTML(file_get_contents(__DIR__ . "/plantillas/inscripcion.html")
				. $this -> generarHtmlBody(), dirname(__FILE__));
			$this -> AltBody = "¡Te has inscrito con éxito en InflaRun!";
			
			$this -> send();
			return true;
		} catch (\Exception $e) {
			return $e -> errorMessage();
		}
	}
        
        private function generarHtmlBody() {
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
                
                

                //display generated file
                //echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>';  
		
		$html = 
			  "    <div class='container-fluid'>"
			. "      <div class='row'>"
			. "        <p>Estimado(a): $nombre</p>"
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
			. "        <br>"
			. "        <p>Imprime y firma esta forma. Llévala al registro para recoger tu paquete. Recuerda también llevar una"
			. "        <strong>identificación oficial</strong>.</p>"
			. "        <h3>FIRMA</h3>"
			. "        <p>______________________________________________</p>"
			. "        <br>"
			. "        <div class='text-justify'>"
			. "          <h4>Términos y Condiciones</h4>"
			. "          <p>Admito que al firmar este documento conozco las bases de la convocatoria, que mis datos son verdaderos y"
			. "          si fueran falsos seré descalificado del evento. Soy el único responsable de mi salud y de cualquier accidente"
			. "          o deficiencia que pudiera causar alteración a mi salud física e incluso la muerte. Por esta razón libero al"
			. "          comité organizador, a los patrocinadores, a las autoridades deportivas y a los prestadores de servicios de"
			. "          cualquier daño que sufra. Así mismo, autorizo al comité organizador para utilizar mi imagen, voz y nombre, ya sea"
			. "          total o parcialmente en lo relacionado al evento. Estoy conciente de que para participar en esta competencia debo"
			. "          estar físicamente preparado para el esfuerzo que voy a realizar.</p>"
			. "          <br>"
			. "          <h4>Exoneraciones</h4>"
			. "          <p>El paquete de correrdor se entregará el día del evento una hora antes del horario que seleccione al momento de"
			. "          inscribirme. En tu paquete recibirás el número de competidor, tu playera y tu kit de participante. Es obligatorio"
			. "          que el titular de la inscripción se presente a la entrega de paquetes a recoger el mismo llevando consigo una"
			. "          identificación. No habrá módulo de entrega de grupos y no se le entregará paquete de competidor a ninguna otra persona"
			. "          que no sea el titular de la inscripción y únicamente mostrando una identificación oficial. El corredor que por cualquier"
			. "          motivo no recoja su paquete en el lugar y horario indicados perderá todo derecho derivado de su inscripción. Por ningún"
			. "          motivo habrá cambio de horario ni de datos de competidor ni se permitirá transferir números.</p>"
			. "        </div>"
			. "      </div>"
			. "    </div>"
			. "  </body>"
			. "</html>";
		
		return $html;
	}
        
        
        
}
