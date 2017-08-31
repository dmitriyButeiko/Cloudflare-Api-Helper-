<?php 
    require_once "vendor/autoload.php";

    use Cloudflare\Zone\Dns;

    define("API_KEY", "2c199018a24e1bc03fe8fa35b0e5333009089");
    define("API_EMAIL", "dmitriy.buteiko@gmail.com");

    function addDnsRecord($domain, $type, $name, $ip, $ttl = 120)
    {
    	// Create a connection to the Cloudflare API which you can
    	// then pass into other services, e.g. DNS, later on
   	 $client = new Cloudflare\Api(API_EMAIL, API_KEY);

        $zone = $client->get("zones", array(
            "name" => $domain
        ));

        $responseArray = get_object_vars($zone);
        $zoneResult = get_object_vars($responseArray["result"][0]);
        $zoneID = $zoneResult["id"];

    	// Create a new DNS record
    	$dns = new Cloudflare\Zone\Dns($client);

        $listedRecords = $dns->list_records($zoneID, "A");
        $listedRecordsArray = get_object_vars($listedRecords);

        /*
            Delete records with the same domain
        */

        $domainRecordsResult = $listedRecordsArray["result"];
        $domainRecordsArray = array();
        foreach($domainRecordsResult as $singleDomainRecord)
        {
            $domainRecordsArray[] = get_object_vars($singleDomainRecord);
        }

        foreach($domainRecordsArray as $singleDomainRecord)
        {
            if($singleDomainRecord["name"] == $name)
            {

                //var_dump($singleDomainRecord);
                $dns->delete_record($zoneID, $singleDomainRecord["id"]);
            }
        }

        $listedRecords = $dns->list_records($zoneID, "A", "www."  . $domain);
        $listedRecordsArray = get_object_vars($listedRecords);

        $response = $dns->create($zoneID, $type, $name, $ip, $ttl, true);

    	return $response;
    }

    function addDomain($domainName)
    {
    	$client = new Cloudflare\Api(API_EMAIL, API_KEY);

	$response = $client->post("/zones", array(
	    "name" => $domainName,
            "jump_start" => true
	));

        $responseArray = get_object_vars($response);
        $resultArray = get_object_vars($responseArray["result"]);
        $zoneIdentifier = $resultArray["id"];

	return $zoneIdentifier;
    }

    addDnsRecord("spaces.ru", "A", "spaces.ru", "26.26.26.26", 120);
    addDnsRecord("spaces.ru", "A", "www.spaces.ru", "26.26.26.26", 120);
?>
