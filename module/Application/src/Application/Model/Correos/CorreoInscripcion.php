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
                        
                        $imagedata = file_get_contents( __DIR__ . "/plantillas/banner-inscripcion-3.png");
                        $base64 = base64_encode($imagedata);
                        $barcode = file_get_contents( __DIR__ . "/images/barcode.png");
                        $base65 = base64_encode($barcode);


                        $data = array( "to" => array($correo =>"to whom!"),
                        "from" => array("ti@numeri.mx", "InflaRun"),
                        "subject" => "InflaRun - Comprobante de Inscripción - $nombre",
                        "html" => $this -> generarHtmlBody2(),
                        "headers" => array("Content-Type"=> "text/html; charset=iso-8859-1","X-param1"=> "value1", "X-param2"=> "value2","X-Mailin-custom"=>"my custom value", "X-Mailin-IP"=> "102.102.1.2", "X-Mailin-Tag" => "My tag"),
                        "inline_image" => array(
                            __DIR__ . "/plantillas/banner-inscripcion-3.png" => $base64,
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
		$talla = $this -> params["talla"];
                
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
                                                  <img src=\"{".__DIR__ . "/plantillas/banner-inscripcion-3.png"."}\" alt='image1' width='100%'  style='display: block;' border='0'><br/>
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
																		. "        <p><strong>Talla:</strong> $talla</p>"
																		. "
																	<br>
																	<div class='text-justify'>
																		<h4>Términos y Condiciones</h4>
																		<p>
																			Estos son los términos y condiciones de participación en la carrera INFLARUN, aplica cualquier
																			sede donde se realice; declaración de exoneración de responsibilidades y autorización.
																		</p>
																		<p>
																			<strong>1.-</strong> Esta página web y todo su contenido son propiedad de INFLARUN S.A. de C.V.
																			con todos los derechos reservados. El uso de cualquier información, salvo lo dispuesto en los
																			presentes Términos y Condiciones, sin el permiso escrito del propietario de la información está
																			estrictamente prohibido. Usted puede descargar, visualizar o imprimir la información de esta
																			página web únicamente para uso personal no comercial.
																		</p>
																		<p>
																			<strong>2.-</strong> El registro e inscripción de los usuarios es única y exclusivamente dentro
																			del portal web y en las fechas señaladas en la convocatoria presente. <strong>No habrá venta
																			de boletos el día del evento</strong> bajo ninguna circunstancia.
																		</p>
																		<p>
																			<strong>3.-</strong> INFLARUN se compromete a proteger y cifrar todas las transacciones y pagos
																			realizados con tarjeta de crédito/débito, así como toda la información personal dentro del sitio
																			mediante un certificado de seguridad SSL.
																		</p>
																		<p>
																			<strong>4.-</strong> Al suscribirme al boletín se acepta recibir correos relacionados con el
																			evento actual, próximos eventos e información relacionada con la carrera INFLARUN.
																		</p>
																		<p>
																			<strong>5.-</strong> Al inscribirme, realizar el pago de la inscripción, reclamar el kit de
																			competencia y participar en la carrera, entiendo y acepto ls términos y condiciones de
																			participación en la carrera INFLARUN en cualquiera de sus sedes, aquí descritos.
																		</p>
																		<p>
																			<strong>6.-</strong> Acepto las condiciones de inscripción y participación.
																		</p>
																		<p>
																			<strong>6.1.-</strong> Entiendo que es obligatorio proporcionar todos mis datos de contacto,
																			sin importar la categoría u horario de bloque en el que me he inscrito.
																		</p>
																		<p>
																			<strong>7.-</strong> Declaro que participo en la carrera de forma libre y por voluntad propia.
																		</p>
																		<p>
																			<strong>8.-</strong> Entiendo que la inscripción en la carrera me da el derecho de participar
																			en la competencia.
																		</p>
																		<p>
																			<strong>9.-</strong> Es de mi conocimiento que en caso de no poder asistir a la carrera, bajo
																			ninguna circunstancia podré exigir un reembolso del valor de la inscripción.
																		</p>
																		<p>
																			<strong>10.-</strong> Acepto de que en caso de cancelación del evento por causas de fuerza mayor
																			o fortuitos, ajenos a la organización, no habrá devoluciones del valor de la inscripción.
																		</p>
																		<p>
																			<strong>11.-</strong> Soy conciente de que en caso de no poder asistir a recoger el kit de
																			competencia en las fechas establecidas por la organización, no podré reclamarlo posteriormente.
																		</p>
																		<p>
																			<strong>12.-</strong> Comprendo que la entrega de playeras será sujeta a existencias y que la
																			organización no se hace responsable por falta de ellas.
																		</p>
																		<p>
																			<strong>13.-</strong> Al participar en esta carrera, declaro mi comprensión y aceptación acerca
																			de la naturaleza de las actividades relacionadas, por lo que hago constar que cuento con buen
																			estado de salud para participar en dicha carrera, entiendo y acepto que mi participación en la
																			carrera INFLARUN, dada su naturaleza puede representar riesgos de accidentes que ocasionen
																			lesiones graves antes, durante y después, incluyendo discapacidad parcial o total, temporal o
																			permanente y hasta la perdida de la vida, por lo que acepto la responsabilidad total por cualquier
																			incidente que pueda ser motivo de mi participación en la carrera, y renuncio a cualquier acción
																			legal que pudiera ejercerse en contra de todo el personal organizador, trabajadores de INFLARUN
																			en <strong>cualquier sede</strong> donde se realice la carrera.
																		</p>
																		<p>
																			<strong>14.-</strong> Declaro que conozco el lugar, la información general y particular de la carrera.
																		</p>
																		<p>
																			<strong>15.-</strong> Entiendo que la carrera está sujeta a cambios sin previo aviso.
																		</p>
																		<p>
																			<strong>16.-</strong> Declaro que conozco y he leído atentamente el reglamento de la carrera.
																		</p>
																		<p>
																			<strong>17.-</strong> Habiendo leído esta declaración, conociendo estos hechos y considerando que
																			los acepto por el hecho de participar en la carrera INFLARUN yo, en mi nombre y en el de cualquier
																			persona que actúe en mi represntación, sucesores, legatarios u herederos, libero y eximo de cualquier
																			responsabilidad, reclamo, indemnización, costo por daño, perjuicio reclamado o lucro cesante a los
																			organizadores de INFLARUN, aliados, patrocinadores, sus representantes y sucesores de todo reclamo o
																			responsabilidad de cualquier tipo, que surja de mi participación en esta competencia, incluso en
																			circunstancias de caso fortuito o fuerza mayor, en razón a que la actividad durante el desarrollo
																			de la carrera se encontrará bajo mi control y ejecución exclusiva como participante, así como cualquier
																			extravío, robo y/o hurto que pudiera sufrir.
																		</p>
																		<p>
																			<strong>18.-</strong> Autorizo a los organizadores y operadores de la carrera INFLARUN, aliados y
																			patrocinadores el uso de fotografías, película, video, grabación y cualquier otro medio de registro
																			de este evento para cualquier uso legítimo sin compensación económica alguna.
																		</p>
																		<p>
																			<strong>19.-</strong> Estos términos y condiciones están sujetos a cambios sin previo aviso, con la
																			actualización de esta publicación.
																		</p>
																		<p>
																			<strong>Acepto los términos y condiciones establecidos en el presente documento, los cuales he
																			entendido y aceptado voluntariamente.</strong>
																		</p>
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
                                                              <a href='https://twitter.com/search?q=%23InflaRun&src=typd' style='color: #ffffff;'>
                                                                <img src='images/tw.gif' alt='Twitter' width='38' height='38' style='display: block;' border='0' />
                                                              </a>
                                                            </td>
                                                            <td style='font-size: 0; line-height: 0;' width='20'>&nbsp;</td>
                                                            <td style='font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;'>
                                                              <a href='https://www.facebook.com/inflarun/?fref=ts' style='color: #ffffff;'>
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
