<?php

use AfipServices\WSException;
use AfipServices\AccessTicket;
use AfipServices\Factories\AuthServiceFactory;
use AfipServices\Factories\BillerServiceFactory;

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}


if( !file_exists( 'conf.php' ) ){	
    throw new Exception("Copia el contenido de conf.example.php a conf.php y completa los datos correctamente\n");	
}

require_once('vendor/autoload.php');
$conf = include( 'conf.php' );

$auth_conf = $conf['wsaa'];

try {

    # $ats = new AfipServices\Auth\AccessTicketStore();

    /* Servicio de autenticacion */
    $auth = AuthServiceFactory::create( $auth_conf['wsdl'], 
                                        $auth_conf['end_point'],
                                        $auth_conf['cert_file_name'],
                                        $auth_conf['key_file_name'],
                                        $auth_conf['passprhase']
                                        );

		$wsdl = 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4?WSDL';
    $ep = 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4?WSDL';
		$soapClient = new \SoapClient( $wsdl, [
                    'location'       => $ep,
                ]);
    $censusA4 = new AfipServices\WebServices\Census\CensusA4Service($soapClient,
        $auth,
        new \AfipServices\AccessTicket($conf['cuit']));

    var_dump($censusA4->getPersona('20000000516'));
} catch ( WSException $e ) {
    var_dump( $e );
    throw $e;
}


