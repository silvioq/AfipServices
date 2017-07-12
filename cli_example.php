<?php

use AfipServices\AccessTicket;
use AfipServices\WSException;
use AfipServices\WebServices\Auth;
use AfipServices\WebServices\Biller;
use AfipServices\Factories\SoapClientFactory;
use AfipServices\Factories\AuthServiceFactory;


if( !file_exists( 'conf.php' ) ){
	echo "Copia el contenido de conf.example.php a conf.php y completa los datos correctamente\n";
	die();
}

include_once('autoload.php');
$conf = include( 'conf.php' );


$auth_conf = $conf['wsaa'];
$biller_conf = $conf['wsfev1'];

/* Servicio de autenticacion */
$auth = AuthFactory::create( $auth_conf['wsdl'], 
                             $auth_conf['end_point'],
                             $auth_conf['cert_file_name'],
                             $auth_conf['key_file_name'],
                             $auth_conf['passprhase']  );

/* Servicio de facturación */            
$biller = new Biller( 
    SoapClientFactory::create( $biller_conf['wsdl'], $biller_conf['end_point'] ), 
    $auth, 
    new AccessTicket( $conf['cuit'] ) 
);

$data = array(
    'Cuit' => '123456789',
    'CantReg' => 1,
    'PtoVta' => 1,
    'CbteTipo' => 2, //B
    'Concepto' => 2, //servicios
    'DocTipo' => 80, //80=CUIL
    'DocNro' => '123456789',
    'CbteDesde' => null, //para que lo calcule uitlizando el web service 
    'CbteHasta' => null, //para que lo calcule uitlizando el web service
    'CbteFch' => date('Ymd'),
    'ImpNeto' => 0,
    'ImpTotConc' => 1, 
    'ImpIVA' => 0,
    'ImpTrib' => 0,
    'ImpOpEx' => 0,
    'ImpTotal' => 1, 
    'FchServDesde' => date("Ymd"), 
    'FchServHasta' => date("Ymd"), 
    'FchVtoPago' => date("Ymd"),
    'MonId' => 'PES', //PES 
    'MonCotiz' => 1, //1 
);

//solicita cae y cae_validdate
;
var_dump( $biller->requestCAE( $data ) );
