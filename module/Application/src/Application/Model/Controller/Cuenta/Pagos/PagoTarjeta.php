<?php
namespace Application\Model\Controller\Cuenta\Pagos;

use Application\Model\Controller\Cuenta\Pagos\Pagos;

/**
 * Clase que permite realizar pagos con tarjeta de crédito o débito
 * por medio del servicio REST de PagoFácil. El proceso se realiza
 * de acuerdo a la documentación de la API v.1.4.3 de PagoFácil.
 */
class PagoTarjeta extends Pagos {

	public function realizarPago($params) {
		$params["nombre"] = urlencode($params["nombre"]);
		$params["apellidos"] = urlencode($params["apellidos"]);
		$params["calleyNumero"] = urlencode($params["calleyNumero"]);
		$params["colonia"] = urlencode($params["colonia"]);
		$params["municipio"] = urlencode($params["municipio"]);
		$params["estado"] = urlencode($params["estado"]);
		$params["pais"] = urlencode($params["pais"]);
		
		$url = "https://www.pagofacil.net/ws/public/Wsrtransaccion/index/format/json?"
			. "method=transaccion"
			. "&data[nombre]={$params["nombre"]}"
			. "&data[apellidos]={$params["apellidos"]}"
			. "&data[numeroTarjeta]={$params["numeroTarjeta"]}"
			. "&data[cvt]={$params["cvt"]}"
			. "&data[cp]={$params["cp"]}"
			. "&data[mesExpiracion]={$params["mesExpiracion"]}"
			. "&data[anyoExpiracion]={$params["anyoExpiracion"]}"
			. "&data[monto]={$params["monto"]}"
			. "&data[idSucursal]={$this -> idSucursal}"
			. "&data[idUsuario]={$this -> idUsuario}"
			. "&data[idServicio]=3"
			. "&data[email]={$params["email"]}"
			. "&data[telefono]={$params["telefono"]}"
			. "&data[celular]={$params["celular"]}"
			. "&data[calleyNumero]={$params["calleyNumero"]}"
			. "&data[colonia]={$params["colonia"]}"
			. "&data[municipio]={$params["municipio"]}"
			. "&data[estado]={$params["estado"]}"
			. "&data[pais]={$params["pais"]}";
		
		$json = file_get_contents($url);
		$respuesta = json_decode($json, true);
		return $respuesta;
	}
}