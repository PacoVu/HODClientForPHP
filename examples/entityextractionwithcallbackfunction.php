<?php
include "libs/hodclient.php";
include "libs/hodresponseparser.php";

$hodClient = new HODClient("API_KEY");

// implement callback function
function requestCompletedWithContent($response) {
    $resp = new HODResponseParser($response);
    if ($resp->error != null){
        echo ("Error code: ".$resp->error->error."</br>Error reason: ".$resp->error->reason."</br>Error detail: ".$resp->error->detail);
    } elseif ($resp->status == "finished") {
        $people = "";
        $places = "";
        $companies = "";
        $entities = $resp->payloadObj->entities;
        for ($i = 0; $i < count($entities); $i++) {
            $entity = $entities[$i];
            if ($entity->type == "people_eng") {
                $people .= $entity->normalized_text . "</br>";
                // parse any other interested information about this person ...
            } else if ($entity->type == "places_eng") {
                $places .= $entity->normalized_text . "</br>";
                // parse any other interested information about this place ...
            } else if ($entity->type == "companies_eng") {
                $companies .= $entity->normalized_text . "</br>";
                // parse any other interested information about this place ...
            }
        }
        echo "PEOPLE: " . $people;
        echo "</br>";
        echo "PLACES: " . $places;
        echo "</br>";
        echo "COMPANIES: " . $companies;
    }
}

$paramArr = array(
    'url' => ["http://www.bbc.com","http://www.cnn.com"],
    "entity_type" => ["people_eng", "places_eng", "companies_eng"],
    "unique_entities" => "true"
);
$hodClient->GetRequest($paramArr, HODApps::ENTITY_EXTRACTION, REQ_MODE::SYNC, 'requestCompletedWithContent');
?>