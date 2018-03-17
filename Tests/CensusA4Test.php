<?php
use PHPUnit\Framework\TestCase;

use AfipServices\WebServices\Census\CensusA4Service;
use AfipServices\AccessTicketManager;
use AfipServices\AccessTicket;
use \Mockery as m;

class CensusA4Test extends TestCase {

	private $census;

	public function tearDown(){
 		m::close();
 	}

 	public function setUp(){

 		$this->census = new CensusA4Service(
			m::mock('SoapClient'),
			m::mock('AfipServices\AccessTicketProvider'),
			m::mock('AfipServices\AccessTicket')
		);

 	}
	
	public function testInstance(){
	 	$this->assertInstanceOf( 'AfipServices\WebServices\Census\CensusA4Service', $this->census );
	}

	/**	 
	 * @expectedException \ArgumentCountError
	 */  	
	public function testInstanceWithNoArguments(){
		new CensusA4Service();
	}	

	public function testShouldBeAccessTicketClient(){
	 	$this->assertInstanceOf( 'AfipServices\AccessTicketClient', $this->census );
	}

	/**	 
	 * @expectedException AfipServices\WSException
	 */  
	public function testAccessTicketShouldHaveTaxId(){

		$at_mock = m::mock('AfipServices\AccessTicket');
		$at_mock->shouldReceive('getTaxId')
			    ->once()
			    ->andReturn( null );

		$census = new CensusA4Service(
			m::mock('SoapClient'),
			m::mock('AfipServices\AccessTicketProvider'),
			$at_mock
		);

		$census->getAT();
	}

	 
	public function testShouldReturnAccessTicket(){

		$ws_mock = m::mock('AfipServices\WebServices\WebService');

		$at_mock = m::mock('AfipServices\AccessTicket');
		$at_mock->shouldReceive('getTaxId')
			    ->once()
			    ->andReturn( '12345678' );

		$atp_mock = m::mock('AfipServices\AccessTicketProvider');
		$atp_mock->shouldReceive('processAccessTicket')
			     ->once();

		$census = new CensusA4Service(
			m::mock('SoapClient'),
			$atp_mock,
			$at_mock
		);

		$this->assertInstanceOf( 'AfipServices\AccessTicket', $census->getAT() );
	}

  public function testShoudReturnPersonData() {
		$at_mock = m::mock('AfipServices\AccessTicket');
		$at_mock->shouldReceive('getTaxId')
			    ->andReturn( '12345678' );

    $at_mock->shouldReceive('getToken')
        ->once()
        ->andReturn('random token');

    $at_mock->shouldReceive('getSign')
        ->once()
        ->andReturn('very secret sign');

    $soap_mock = m::mock('SoapClient');
    $soap_mock->shouldReceive('getPersona')
        ->once()
        ->with([
            'token' => 'random token',
            'sign' => 'very secret sign',
            'cuitRepresentada' => '12345678',
            'idPersona' => 'personaId'
        ])
        ->andReturn( (object)(['personaReturn' => (object)['persona' => 'datos de la persona' ]]));

		$atp_mock = m::mock('AfipServices\AccessTicketProvider');
		$atp_mock->shouldReceive('processAccessTicket')
			     ->times(3);

		$census = new CensusA4Service(
			$soap_mock,
			$atp_mock,
			$at_mock
		);

    $this->assertSame('datos de la persona', $census->getPersona('personaId'));
  }


}
