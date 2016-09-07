<?php
namespace Application\Model\Correos;

use \PHPMailer;

abstract class Correos extends PHPMailer {
	protected $host = "vm20.digitalserver.org";
	protected $port = 465;
	protected $security = "ssl";
	protected $user = "lenrique@numeri.mx";
	protected $pass = "@Numeri2016";
	
	public function __construct($exceptions = null) {
		parent::__construct($exceptions);
		$this -> inicializarMail();
	}
	
	/**
	 * Inicializa el objeto PHPMailer con los atributos
	 * de esta clase.
	 */
	private function inicializarMail() {
		$this -> setLanguage("es");
		$this -> CharSet = "utf-8";
		$this -> isHTML(true);
		$this -> isSMTP();
		$this -> SMTPDebug = false;

		$this -> Host = $this -> host;
		$this -> Port = $this -> port;
		$this -> SMTPAutoTLS = false;
		$this -> SMTPSecure = $this -> security;
		$this -> SMTPAuth = true;
		$this -> Username = $this -> user;
		$this -> Password = $this -> pass;
	}
	
	/**
	 * Envia un correo al destinatario indicado.
	 * 
	 * @param string $correo La dirección de correo electrónico del destinatario.
	 * @param string $nombre El nombre del destinatario.
	 * @return (bool|string) Regresa true si el correo se envió
	 * de manera exitosa. De lo contrario, regresa el mensaje de error.
	 */
	public abstract function enviarCorreo($correo, $nombre = "Destinatario");
}