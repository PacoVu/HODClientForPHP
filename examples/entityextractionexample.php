<?php
include "hodclient.php";

function  getPeopleAndPlaces() {
    $hodClient = new HODClient("YOUR_API_KEY");
    $paramArr = array(
        'url' => "http://www.cnn.com",
        'arrays' => array("entity_type" => "people_eng,places_eng")
    );
    try {
        $hodClient->PostRequest($paramArr, HODApps::ENTITY_EXTRACTION, REQ_MODE::SYNC, 'requestCompletedWithContent');
    } catch (Exception $ex) {
        error_log("Error: " . $ex->getMessage());
    }
}

// implement callback function
function requestCompletedWithContent($response) {
    $jsonStr = stripslashes($response);
    $respObj = json_decode($jsonStr);
    $people = "";
    $places = "";
    $entities = $respObj->entities;
    for ($i = 0; $i < count($entities); $i++) {
        $entity = $entities[$i];
        if ($entity->type == "people_eng") {
            $people .= $entity->normalized_text . "; ";
            // parse any other interested information about this person ...
        } else if ($entity->type == "places_eng") {
            $places .= $entity->normalized_text . "; ";
            // parse any other interested information about this place ...
        }
    }
	echo "PEOPLE: " . $people;
	echo "</br>";
	echo "PLACES: " . $places;
    }

    getPeopleAndPlaces();
?>