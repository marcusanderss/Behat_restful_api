Feature:  Sample JSON queries for Formula 1 statistics 

  
Scenario: ergast com Demo circuits data any given season
  Given I request "http://ergast.com/api/f1/2017/circuits.json" 
  Then the response should be JSON
  And the response has a "MRData" property
  And the response contains a total of 20 circuits
  And circuit number '4' this season took place in Spain
  Given the country was Spain the locality was Montmeló
  Given the locality was Montmeló the circuitName is "Circuit de Barcelona-Catalunya"
  And circuit number '14' this season took place in UK
  Given the country was UK the locality was Silverstone
  Given the locality was Silverstone the circuitName is "Silverstone Circuit" 

Scenario: ergast com Demo Driver rankings any given season
  Given I request "http://ergast.com/api/f1/2017/driverstandings.json" 
  Then the response should be JSON
  And the response has a "MRData" property
  And the driver at position '1' was "hamilton"
  And the driver at position '25' was "resta"
  And the driver at position '5' was "ricciardo"

Scenario: graphql https://portal.ehri-project.eu/api/graphql
  Given I graphqlrequest 'us';
  Then the graphql response contains Country 'United States'
  Given I graphqlrequest 'ge';
  Then the graphql response contains Country 'Georgia'
  