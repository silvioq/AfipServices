<?php
namespace AfipServices\WebServices\Census;

use AfipServices\WSException;
use AfipServices\WSHelper;
use AfipServices\AccessTicket;
use AfipServices\AccessTicketClient;
use AfipServices\AccessTicketProvider;
use AfipServices\WebServices\WebService;
use AfipServices\Traits\FileManager;

/**
 * WebService de padron ws_sr_padron_a4
 */
Class CensusA4Service extends WebService implements AccessTicketClient{

	use FileManager;

	protected $service_name = 'ws_sr_padron_a4';
	protected $soap_client;
	protected $access_ticket_provider;
	protected $access_ticket;

	/**
	 * @param SoapClient $soap_client SoapClientFactory::create( [wsdl], [end_point] )
	 * @param AccessTicketProvider $acces_ticket_manager el objeto encargado de procesar y completar el AccessTicket
	 * @param AccessTicket $access_ticket
	 */ 
	public function __construct( \SoapClient $soap_client,
								 AccessTicketProvider $access_ticket_provider, 
								 AccessTicket $access_ticket  ){
    parent::__construct();
		$this->soap_client = $soap_client;
		$this->access_ticket_provider = $access_ticket_provider;
		$this->access_ticket = $access_ticket;
	}

	/**
	 * Devuelve el nombre del servicio
   *
	 * @return string
	 */ 
	public function getServiceName(){
		return $this->service_name;
	}

	/**
	 * Devuelve el access ticket
	 * @return AccessTicket
	 */ 
	public function getAccessTicket(){
		return $this->access_ticket;
	}

	/**
	 * Le solicita el Ticket de Acceso al AccessTicketProvider
	 * @return AccessTicket
	 */ 
	public function getAT(){

		if( !$this->access_ticket->getTaxId() ){			
			throw new WSException("El Ticket de acceso al WSFE de Afip debe tener cuit", $this);			
		}

		$this->access_ticket_provider->processAccessTicket( $this );
		return $this->access_ticket;
	}


	/**
	 * Solicitar datos de una persona
	 * @param string $data  
	 * @return array
	 * @throws  WSException 
	 */
	public function getPersona( $data ){

		$request_params = $this->_buildGetPersonaParams( $data );
		try{
		  $response = $this->soap_client->getPersona( $request_params );
    } catch (\SoapFault $e){
      $msg = ($e->getMessage());
      throw new WSException($msg, $this);
    }
    return $response->personaReturn->persona;
	}	


	/**
	 * Armar el array para ser enviado al servicio y solicitar el cae
	 * @param array $data
	 * @return array $params
	 */ 
	private function _buildGetPersonaParams( $idPersona ){

		$params = [ 
      'token' => $this->getAT()->getToken(),
      'sign' => $this->getAT()->getSign(),
      'cuitRepresentada' => $this->getAT()->getTaxId(),
      'idPersona' => $idPersona,
		];


		return $params;
	}


}
