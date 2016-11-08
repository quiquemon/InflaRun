<?php
use Application\Model\Correos\Mailin;
namespace Application\Model\Correos;

/**
 * Envía un correo con el link de inscribir integrantes a un equipo.
 *
 * @author qnhama
 */
class CorreoEquipo extends Correos {
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
                        
			$imagedata = file_get_contents( __DIR__ . "/plantillas/banner-inscripcion.png");
			$base64 = base64_encode($imagedata);
			$barcode = file_get_contents( __DIR__ . "/images/barcode.png");
			$base65 = base64_encode($barcode);


			$data = array(
				"to" => array($correo =>"to whom!"),
				"from" => array("ti@numeri.mx", "InflaRun"),
				"subject" => "InflaRunner $nombre: ¡Inscribe a tus compañeros de equipo a InflaRun!",
				"html" => $this -> generarHtmlBody2(),
				"headers" => array("Content-Type"=> "text/html; charset=iso-8859-1","X-param1"=> "value1", "X-param2"=> "value2","X-Mailin-custom"=>"my custom value", "X-Mailin-IP"=> "102.102.1.2", "X-Mailin-Tag" => "My tag"),
				"inline_image" => array(
					__DIR__ . "/plantillas/banner-inscripcion.png" => $base64,
					__DIR__ . "/images/barcode.png" => $base65
				 )
			);

			$resultado = $mailin->send_email($data); 
			return true;
		} catch (\Exception $e) {
			return $e -> errorMessage();
		}
	}
        
        
	private function generarHtmlBody2() {
		$anyoActual = explode("-", date("Y-m-d"))[0];
		$nombreCarrera = $this -> params["nombreCarrera"];
		$codigoCanje = $this -> params["codigoCanje"];
		$nombreEquipo = $this -> params["nombreEquipo"];
		$link = "https://inflarun.mx/InflaRun/public/application/cuenta/inscripcionesequipos?codigoCanje=$codigoCanje";
		$html = "
                    

	<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
	<html xmlns='http://www.w3.org/1999/xhtml'>
	<head>
		
		<!--
			Outlook Conditional CSS

			These two style blocks target Outlook 2007 & 2010 specifically, forcing
			columns into a single vertical stack as on mobile clients. This is
			primarily done to avoid the 'page break bug' and is optional.

			More information here:
			http://templates.mailchimp.com/development/css/outlook-conditional-css
		-->
		<!--[if mso 12]>
			<style type='text/css'>
				.flexibleContainer{display:block !important; width:100% !important;}
			</style>
		<![endif]-->
		<!--[if mso 14]>
			<style type='text/css'>
				.flexibleContainer{display:block !important; width:100% !important;}
			</style>
		<![endif]-->
	</head>
	<body bgcolor='#E1E1E1' leftmargin='0' marginwidth='0' topmargin='0' marginheight='0' offset='0'>

		<!-- CENTER THE EMAIL // -->
		<!--
		1.  The center tag should normally put all the
			content in the middle of the email page.
			I added 'table-layout: fixed;' style to force
			yahoomail which by default put the content left.

		2.  For hotmail and yahoomail, the contents of
			the email starts from this center, so we try to
			apply necessary styling e.g. background-color.
		-->
		<center style='background-color:#E1E1E1;'>
			<table border='0' cellpadding='0' cellspacing='0' height='100%' width='100%' id='bodyTable' style='table-layout: fixed;max-width:100% !important;width: 100% !important;min-width: 100% !important;'>
				<tr>
					<td align='center' valign='top' id='bodyCell'>

						<!-- EMAIL HEADER // -->
						<!--
							The table 'emailBody' is the email's container.
							Its width can be set to 100% for a color band
							that spans the width of the page.
						-->
						<table bgcolor='#E1E1E1' border='0' cellpadding='0' cellspacing='0' width='500' id='emailHeader'>

							

						</table>
						<!-- // END -->

						<!-- EMAIL BODY // -->
						<!--
							The table 'emailBody' is the email's container.
							Its width can be set to 100% for a color band
							that spans the width of the page.
						-->
						<table bgcolor='#FFFFFF'  border='0' cellpadding='0' cellspacing='0' width='500' id='emailBody'>

							<!-- MODULE ROW // -->
							<!--
								To move or duplicate any of the design patterns
								in this email, simply move or copy the entire
								MODULE ROW section for each content block.
							-->
							<tr>
								<td align='center' valign='top'>
									<!-- CENTERING TABLE // -->
									<!--
										The centering table keeps the content
										tables centered in the emailBody table,
										in case its width is set to 100%.
									-->
									<table border='0' cellpadding='0' cellspacing='0' width='100%' style='color:#FFFFFF;' bgcolor='#3498db'>
										<tr>
											<td align='center' valign='top'>
												<!-- FLEXIBLE CONTAINER // -->
												<!--
													The flexible container has a set width
													that gets overridden by the media query.
													Most content tables within can then be
													given 100% widths.
												-->
												<table border='0' cellpadding='0' cellspacing='0' width='500' class='flexibleContainer'>
													<tr>
														<td align='center' valign='top' width='500' class='flexibleContainerCell'>

															<!-- CONTENT TABLE // -->
															<!--
															The content table is the first element
																that's entirely separate from the structural
																framework of the email.
															-->
															<table border='0' cellpadding='30' cellspacing='0' width='100%'>
																<tr>
																	<td align='center' valign='top' class='textContent'>
																		<h1 style='color:#FFFFFF;line-height:100%;font-family:Helvetica,Arial,sans-serif;font-size:35px;font-weight:normal;margin-bottom:5px;text-align:center;'>¡Ya eres parte de la carrera $nombreCarrera!</h1>
																		<h2 style='text-align:center;font-weight:normal;font-family:Helvetica,Arial,sans-serif;font-size:23px;margin-bottom:10px;color:#205478;line-height:135%;'>Inscribe a tu equipo: $nombreEquipo</h2>
																		<div style='text-align:center;font-family:Helvetica,Arial,sans-serif;font-size:15px;margin-bottom:0;color:#FFFFFF;line-height:135%;'>Ahora debes inscribir a los demás integrantes de tu equipo. Es muy fácil, solo debes seguir los siguientes pasos:</div>
																	</td>
																</tr>
															</table>
															<!-- // CONTENT TABLE -->

														</td>
													</tr>
												</table>
												<!-- // FLEXIBLE CONTAINER -->
											</td>
										</tr>
									</table>
									<!-- // CENTERING TABLE -->
								</td>
							</tr>
							<!-- // MODULE ROW -->


							<!-- MODULE ROW // -->
							<!--  The 'mc:hideable' is a feature for MailChimp which allows
								you to disable certain row. It works perfectly for our row structure.
								http://kb.mailchimp.com/article/template-language-creating-editable-content-areas/
							-->
							<tr mc:hideable>
								<td align='center' valign='top'>
									<!-- CENTERING TABLE // -->
									<table border='0' cellpadding='0' cellspacing='0' width='100%'>
										<tr>
											<td align='center' valign='top'>
												<!-- FLEXIBLE CONTAINER // -->
												<table border='0' cellpadding='30' cellspacing='0' width='500' class='flexibleContainer'>
													<tr>
														<td valign='top' width='500' class='flexibleContainerCell'>

															<!-- CONTENT TABLE // -->
															<table align='left' border='0' cellpadding='0' cellspacing='0' width='100%'>
																<tr>
																	<td align='left' valign='top' class='flexibleContainerBox'>
																		<table border='0' cellpadding='0' cellspacing='0' width='210' style='max-width: 100%;'>
																			<tr>
																				<td align='left' class='textContent'>
																					<h3 style='color:#5F5F5F;line-height:125%;font-family:Helvetica,Arial,sans-serif;font-size:20px;font-weight:normal;margin-top:0;margin-bottom:3px;text-align:left;'>Paso 1</h3>
																					<div style='text-align:left;font-family:Helvetica,Arial,sans-serif;font-size:15px;margin-bottom:0;color:#5F5F5F;line-height:135%;'>•	Reenvía este email a todos los integrantes de tu equipo.</div>
																				</td>
																			</tr>
																		</table>
																	</td>
																	<td align='right' valign='middle' class='flexibleContainerBox'>
																		<table class='flexibleContainerBoxNext' border='0' cellpadding='0' cellspacing='0' width='210' style='max-width: 100%;'>
																			<tr>
																				<td align='left' class='textContent'>
																					<h3 style='color:#5F5F5F;line-height:125%;font-family:Helvetica,Arial,sans-serif;font-size:20px;font-weight:normal;margin-top:0;margin-bottom:3px;text-align:left;'>Paso 2</h3>
																					<div style='text-align:left;font-family:Helvetica,Arial,sans-serif;font-size:15px;margin-bottom:0;color:#5F5F5F;line-height:135%;'>•	Cada uno de ellos debe dar clic en el siguiente botón para registrar sus datos y recibir su comprobante de inscripción.</div>
																				</td>
																			</tr>
																		</table>
																	</td>
																</tr>
															</table>
															<!-- // CONTENT TABLE -->

														</td>
													</tr>
												</table>
												<!-- // FLEXIBLE CONTAINER -->
											</td>
										</tr>
									</table>
									<!-- // CENTERING TABLE -->
								</td>
							</tr>
							<!-- // MODULE ROW -->


							<!-- MODULE ROW // -->
							<tr>
								<td align='center' valign='top'>
									<!-- CENTERING TABLE // -->
									<table border='0' cellpadding='0' cellspacing='0' width='100%'>
										<tr style='padding-top:0;'>
											<td align='center' valign='top'>
												<!-- FLEXIBLE CONTAINER // -->
												<table border='0' cellpadding='30' cellspacing='0' width='500' class='flexibleContainer'>
													<tr>
														<td style='padding-top:0;' align='center' valign='top' width='500' class='flexibleContainerCell'>

															<!-- CONTENT TABLE // -->
															<table border='0' cellpadding='0' cellspacing='0' width='50%' class='emailButton' style='background-color: #3498DB;'>
																<tr>
																	<td align='center' valign='middle' class='buttonContent' style='padding-top:15px;padding-bottom:15px;padding-right:15px;padding-left:15px;'>
																		<a style='color:#FFFFFF;text-decoration:none;font-family:Helvetica,Arial,sans-serif;font-size:20px;line-height:135%;' href='$link' target='_blank'>Inscribe a tu equipo</a>
																	</td>
																</tr>
															</table>
															<!-- // CONTENT TABLE -->

														</td>
													</tr>
												</table>
												<!-- // FLEXIBLE CONTAINER -->
											</td>
										</tr>
									</table>
									<!-- // CENTERING TABLE -->
								</td>
							</tr>
							<!-- // MODULE ROW -->


							


						</table>
						<!-- // END -->

						<!-- EMAIL FOOTER // -->
						<!--
							The table 'emailBody' is the email's container.
							Its width can be set to 100% for a color band
							that spans the width of the page.
						-->
						<table bgcolor='#E1E1E1' border='0' cellpadding='0' cellspacing='0' width='500' id='emailFooter'>

							<!-- FOOTER ROW // -->
							<!--
								To move or duplicate any of the design patterns
								in this email, simply move or copy the entire
								MODULE ROW section for each content block.
							-->
							<tr>
								<td align='center' valign='top'>
									<!-- CENTERING TABLE // -->
									<table border='0' cellpadding='0' cellspacing='0' width='100%'>
										<tr>
											<td align='center' valign='top'>
												<!-- FLEXIBLE CONTAINER // -->
												<table border='0' cellpadding='0' cellspacing='0' width='500' class='flexibleContainer'>
													<tr>
														<td align='center' valign='top' width='500' class='flexibleContainerCell'>
															<table border='0' cellpadding='30' cellspacing='0' width='100%'>
																<tr>
																	<td valign='top' bgcolor='#E1E1E1'>

																		<div style='font-family:Helvetica,Arial,sans-serif;font-size:13px;color:#828282;text-align:center;line-height:120%;'>
																			<div>InflaRun <!-- &#169; --> $anyoActual <!-- <a href='http://www.charlesmudy.com/respmail/' target='_blank' style='text-decoration:none;color:#828282;'><span style='color:#828282;'>Respmail</span></a>. All&nbsp;rights&nbsp;reserved.</div>
																			<div>If you do not want to recieve emails from us, you can <a href='#' target='_blank' style='text-decoration:none;color:#828282;'><span style='color:#828282;'>unsubscribe</span></a>.--></div>
																		</div>

																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
												<!-- // FLEXIBLE CONTAINER -->
											</td>
										</tr>
									</table>
									<!-- // CENTERING TABLE -->
								</td>
							</tr>

						</table>
						<!-- // END -->

					</td>
				</tr>
			</table>
		</center>
	</body>
</html>

";
		
		return $html;
	}
}
