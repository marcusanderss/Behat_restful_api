<?php
 
use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Client;

use function GuzzleHttp\Psr7\copy_to_string;

class FeatureContext extends MinkContext implements Context
{
    public $_response;
    public $_client;
    private $_parameters = array();
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
 
        $this->_parameters = $parameters;
        $baseUrl = $this->getParameter('base_url');
        $client = new Client(['base_url' => $baseUrl]);
        $this->_client = $client;
    }
 
    public function getParameter($name)
    {
        if (count($this->_parameters) === 0) {
            throw new \Exception('Parameters not loaded!');
        } else {
            $parameters = $this->_parameters;
            return (isset($parameters[$name])) ? $parameters[$name] : null;
        }
    }
 
    /**
     * @When /^I request "([^"]*)"$/
     */
    public function iRequest($uri)
    {
        $request = $this->_client->get($uri);
        $this->_response = $request;
    }

	 /**
     *  @When /^I graphqlrequest "([^"]*)"$/
     */
    public function iGraphqlrequest($uri)
    {

        $graphQLquery = "{\"query\":\"{\\n Country(id: \\\"ge\\\") {\\n name\\n situation\\n }\\n}\\n \"}";

        $response = (new Client)->request('post', "https://portal.ehri-project.eu/api/graphql", 
        ['headers' => ['Content-Type' => 'application/json'],'body' => $graphQLquery]);

        $this->_response = $response;

        $data = json_decode($this->_response->getBody(true));

    }
	
	/**
     * @Given I graphqlrequest :arg3;
     */
    public function iGraphqlrequest2($arg3)
    {  
        
		if ($arg3 == "ge")
		{
		$graphQLquery = "{\"query\":\"{\\n Country(id: \\\"ge\\\") {\\n name\\n situation\\n }\\n}\\n \"}"; }
		
		else $graphQLquery = "{\"query\":\"{\\n Country(id: \\\"us\\\") {\\n name\\n situation\\n }\\n}\\n \"}";
		
		 $response = (new Client)->request('post', "https://portal.ehri-project.eu/api/graphql", 
        ['headers' => ['Content-Type' => 'application/json'],'body' => $graphQLquery]);

        $this->_response = $response;

        $data = json_decode($this->_response->getBody(true));
	
    }
    
    
     /**
     * @Then the graphql response contains Country :arg1
     */
    public function theGraphqlResponseContainsCountry($arg1)
    {
         $data = json_decode($this->_response->getBody()->__tostring(), true);
		
		 echo $data['data']['Country']['name'];		 
    }
	
	

    /**
     * @Then /^the response should be JSON$/
     */
    public function theResponseShouldBeJson()
    {
        $data = json_decode($this->_response->getBody(true));
        if (empty($data)) { throw new Exception("Response was not JSON\n" . $this->_response);
       }
    }
 
    /**
     * @Then /^the response status code should be (\d+)$/
     */
    public function theResponseStatusCodeShouldBe($httpStatus)
    {
        if ((string)$this->_response->getStatusCode() !== $httpStatus) {
            throw new \Exception('HTTP code does not match '.$httpStatus.
                ' (actual: '.$this->_response->getStatusCode().')');
        }
    }  
 
    /**
     * @Given /^the response has a "([^"]*)" property$/
     */
    public function theResponseHasAProperty($propertyName)
    {
        $data = json_decode($this->_response->getBody(true));
        if (!empty($data)) {
            if (!isset($data->$propertyName)) {
                throw new Exception("Property '".$propertyName."' is not set!\n");
            }
        } else {
            throw new Exception("Response was not JSON\n" . $this->_response->getBody(true));
        }
    }

    /**
     * @Then the response contains a total of :arg1 circuits
     */
    public function theResponseContainsATotalOfCircuits($arg1)
    {
        $data = json_decode($this->_response->getBody()->__tostring(), true);
        #print_r($data);
        if ($data['MRData']['total'] != $arg1) {
            throw new Exception('Total value mismatch! (given: '.$arg1.', match: '.$data['MRData']['total'].')');    
        }
    }

    /**
     * @Then circuit number :arg1 this season took place in :arg2
     */
    public function circuitNumberThisSeasonTookPlaceInCountry($arg1,$arg2)
    {   
        $data = json_decode($this->_response->getBody()->__tostring(), true);
        if ($data['MRData']['CircuitTable']['Circuits'][$arg1]['Location']['country'] != $arg2) {
            throw new Exception('Country value mismatch! (given: '.$arg2.', match: '.$data['MRData']['CircuitTable']['Circuits'][$arg1]['Location']['country'].')');    
        }
    }
    
    /**
     * @Given the country was :arg1 the locality was :arg2
     */
    public function theCountryWasSpainTheCityWasMontmelo($arg1,$arg2)
    {
        $matches = 0;
        $data = json_decode($this->_response->getBody()->__tostring(), true);

        for ($i=0;$i<$data['MRData']['total'];$i++)
        {
            if ($data['MRData']['CircuitTable']['Circuits'][$i]['Location']['country'] == $arg1) {
                $matches++;
                if ($data['MRData']['CircuitTable']['Circuits'][$i]['Location']['locality'] != $arg2) {
                    throw new Exception('Locality value mismatch! (given: '.$arg2.', match: '.$data['MRData']['CircuitTable']['Circuits'][$i]['Location']['locality'].')');    
                }
            }
            
        }
        if ($matches==0) {
            throw new Exception('Country '.$arg1.' does not exist in table.');
        }
    }

    /**
     * @Given the locality was :arg1 the circuitName is :arg2
     */
    public function theLocalityWasTheCircuitnameIs($arg1, $arg2)
    {
        $matches = 0;
        $data = json_decode($this->_response->getBody()->__tostring(), true);

        for ($i=0;$i<$data['MRData']['total'];$i++)
        {
            if ($data['MRData']['CircuitTable']['Circuits'][$i]['Location']['locality'] == $arg1) {
                $matches++;
                if ($data['MRData']['CircuitTable']['Circuits'][$i]['circuitName'] != $arg2) {
                    throw new Exception('CircuitName value mismatch! (given: '.$arg2.', match: '.$data['MRData']['CircuitTable']['Circuits'][$i]['circuitName'].')');    
                }
            }
            
        }
        if ($matches==0) {
            throw new Exception('Locality '.$arg1.' does not exist in table.');
        }
    }

    /**
     * @Then the driver at position :arg2 was :arg1
     */
    public function theDriverAtPositionWas($arg1, $arg2)
    {
        $data = json_decode($this->_response->getBody(), true);
        
        $matches = 0;
        for ($i=0;$i<$data['MRData']['total'];$i++)
        {
             if( $data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'][$i]['position'] == $arg2) {
  
                echo $data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'][$i]['Driver']['givenName'];
                echo "\r\n";
                echo $data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'][$i]['Driver']['familyName'];
                echo "\r\n";
                echo $data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'][$i]['Driver']['nationality'];
  
             if ($data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'][$i]['Driver']['driverId'] != $arg1) {
                throw new Exception('DriverId value mismatch! (given: '.$arg1.', match: '.$data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'][$i]['Driver']['driverId'].')');    
           
             
           
			 }}

            
            }
            
        
        
    }

    /**
     * @Then /^the "([^"]*)" property equals "([^"]*)"$/
     */
    public function thePropertyEquals($propertyName, $propertyValue)
    {
        $data = json_decode($this->_response->getBody(true));
 
        if (!empty($data)) {
            if (!isset($data->$propertyName)) {
                throw new Exception("Property '".$propertyName."' is not set!\n");
            }
            if ($data->$propertyName !== $propertyValue) {
                throw new \Exception('Property value mismatch! (given: '.$propertyValue.', match: '.$data->$propertyName.')');
            }
        } else {
            throw new Exception("Response was not JSON\n" . $this->_response->getBody(true));
        }
    }

    /**
     * @Then the response from :arg1 contains a total of :arg2 circuits
     */
    public function theResponseFromContainsATotalOfCircuits($arg1, $arg2)
    {
        $json = file_get_contents($arg1);
        $data = json_decode($json, TRUE);
        echo $data['MRData']['CircuitTable']['season']; echo " ";
        echo $data['MRData']['CircuitTable']['Circuits']['0']['circuitName']; echo " ";
        echo $data['MRData']['CircuitTable']['Circuits']['0']['Location']['locality']; echo " ";
        echo $data['MRData']['CircuitTable']['Circuits']['0']['Location']['country']; echo " ";
        if ($data['MRData']['total'] != $arg2) {
            throw new Exception('Total value mismatch! (given: '.$arg2.', match: '.$data['MRData']['total'].')');    
        }
    }
}
