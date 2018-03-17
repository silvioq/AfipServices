# Clases para interactuar con servicios afip

1. `Auth` permite interactuar con el web service de autenticacion WSAA
2. `Biller` permite interactuar con el servicio de facturacion electronica WSFEV1
3. `CensusA4` Permite interactura con el servicio de consulta de padrón `ws_sr_padron_a4`

## Carpeta Resources

En carpeta Resources debe haber: 

1. `cert.pem` este .pem nos lo da la afip.
2. `cert.key` es la llave con el cual generamos nuestro CSR. El CSR es lo que le enviamos a la afip para obtener el `.pem`

Mas info aca -> http://www.afip.gob.ar/ws/WSASS/WSASS_manual.pdf

## Carpeta Temp
Debe tener permisos de escritura

## Ejemplo de uso

```php


require_once('vendor/autoload.php');
$conf = include( 'conf.php' );


$auth_conf = $conf['wsaa'];
$biller_conf = $conf['wsfev1'];


try {

    /* Servicio de autenticacion */
    $auth = AuthServiceFactory::create( $auth_conf['wsdl'], 
                                        $auth_conf['end_point'],
                                        $auth_conf['cert_file_name'],
                                        $auth_conf['key_file_name'],
                                        $auth_conf['passprhase']  );

    /* Servicio de facturación */            
    $biller = BillerServiceFactory::create( $auth, 
                                            $biller_conf['wsdl'], 
                                        $biller_conf['end_point'], 
                                        $conf['cuit'] );


    $data = array(
        'Cuit' => '123456789',
        'CantReg' => 1,
        'PtoVta' => $biller_conf['sale_point'], //null para que lo intente obtener el web service
        'CbteTipo' => 06, //A:01 B:06 C:11 
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
    
    $data = $biller->requestCAE( $params );
    
} catch ( WSException $e ) {
     var_dump([
        'description' => "{$e->getService()->getServiceName()}: {$e->getMessage()}",
        'log_api_response' => $e->getWSResponse()
    ]);
}





```

--------------------------------------------------------------------------
**Manuales AFIP**

1. Auth: http://www.afip.gob.ar/ws/WSAA/Especificacion_Tecnica_WSAA_1.2.2.pdf

2. F.E.: http://www.afip.gob.ar/fe/documentos/manual_desarrollador_COMPG_v2_9.pdf

3. Padrón A4: https://www.afip.gob.ar/ws/ws_sr_padron_a4/manual_ws_sr_padron_a4_v1.1.pdf
