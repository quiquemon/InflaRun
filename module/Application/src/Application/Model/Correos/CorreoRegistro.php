<?php
namespace Application\Model\Correos;

/**
 * Clase que permite enviar correos a los usuarios al
 * momento del registro en el sistema.
 */
class CorreoRegistro extends Correos {
	
	public function __construct() {
		parent::__construct();
	}
	
	
	public function enviarSendinblue($correo, $nombre = "Destinatario") {
		try {
			$this -> mail -> setFrom("noreply@inflarun.com", "InflaRun");
			$this -> mail -> addAddress($correo, $nombre);
			$this -> mail -> Subject = "¡Te has registrado con éxito en InflaRun, $nombre!";
			$this -> mail -> addEmbeddedImage(__DIR__ . "\plantillas\banner-carrera.png", "banner-carrera");
			$this -> mail -> msgHTML(file_get_contents(__DIR__ . "\plantillas\mensaje-correo.html"), dirname(__FILE__));
			$this -> mail -> AltBody = "¡Te has registrado con éxito en InflaRun!";
			
			$this -> mail -> send();
			return true;
		} catch (\Exception $e) {
			return $e -> errorMessage();
		}
	}
}
