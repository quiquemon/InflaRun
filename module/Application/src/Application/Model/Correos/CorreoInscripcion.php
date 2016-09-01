<?php
namespace Application\Model\Correos;

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
	
	/**
	 * Genera el código HTML personalizado para la
	 * inscripción del usuario.
	 */
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