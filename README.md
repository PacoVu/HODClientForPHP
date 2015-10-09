# HODClient Library for PHP. V1.0

----
## Overview
HODClient for PHP is a utility class, which helps you easily integrate your .php project with HP Haven OnDemand Services.

HODClient class exposes source code so you can modify it as you wish.

----
## Integrate HODClient into php project
1. Download the HODClient library for PHP.
2. Unzip and copy the hodclient.php under the lib folder to your project folder.
3. Include the hodclient.php file in your php file. 

----
## API References
**Constructor**

    HODClient($apiKey, $version = "v1")

*Description:* 
* Creates and initializes a HODClient object.

*Parameters:*
* $apiKey: your developer apikey.
* $version: Haven OnDemand API version. Currently it only supports version 1. Thus, the default value is "v1".

*Example code:*
    include "hodclient.php"
    $hodClient = new HODClient("your-api-key");

----
**Function GetRequest**

    GetRequest($paramArr, $hodApp, $mode, $callback)

*Description:* 
* Sends a HTTP GET request to call an Haven OnDemand API.

*Parameters:*
* $paramArr: an array() containing key/value pair parameters to be sent to a Haven OnDemand API, where the keys are the parameters of that Haven OnDemand API.

>Note: 

>In the case of a parameter type is an array<>, the key must be defined as "arrays" and the value must be an array with the key is the parameter name and the values separated by commas ",". 
>E.g.:
## 
    $paramArr = array(
        'url' => "http://www.cnn.com",
        'arrays' => array("entity_type" => "people_eng,places_eng,companies_eng")
    );


* $hodApp: a string to identify a Haven OnDemand API. E.g. "extractentities". Current supported APIs are listed in the HODApps interface.
* $mode [REQ_MODE::SYNC | REQ_MODE::ASYNC]: specifies API call as Asynchronous or Synchronous.
* $callback: the name of a callback function, which the HODClient will call back and pass the response from server.

*Response:* 
* Response from the server will be returned via the provided $callback function

*Exception:*

* This function will throw an error if the operation failed.

*Example code:*
## 
    // Call the Entity Extraction API synchronously to find people and places from CNN website.
    $paramArr = array(
        'url' => "http://www.cnn.com",
        'arrays' => array("entity_type" => "people_eng,places_eng,companies_eng")
    );
    try {
        $hodClient->GetRequest($paramArr, HODApps::ENTITY_EXTRACTION, REQ_MODE::SYNC, 'requestCompleted');
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
    // callback function
    function requestCompleted($response) {
        echo $response;
    }
    
----
**Function PostRequest**
 
    PostRequest($paramArr, $hodApp, $mode, $callback)

*Description:* 
* Sends a HTTP POST request to call a Haven OnDemand API.

*Parameters:*
* $paramArr: an array() containing key/value pair parameters to be sent to a Haven OnDemand API, where the keys are the parameters of that Haven OnDemand API. 

>Note: 

>In the case of a parameter type is an array<>, the key must be defined as "arrays" and the value must be an array with the key is the parameter name and the values separated by commas ",". 
>E.g.:
## 
    $paramArr = array(
        'url' => "http://www.cnn.com",
        'arrays' => array("entity_type" => "people_eng,places_eng,companies_eng")
    );

* $hodApp: a string to identify an IDOL OnDemand API. E.g. "ocrdocument". Current supported apps are listed in the IODApps class.
* $mode [REQ_MODE::SYNC | REQ_MODE::ASYNC]: specifies API call as Asynchronous or Synchronous.
* $callback: the name of a callback function, which the HODClient will call back and pass the response from server.

*Response:* 
* Response from the server will be returned via the provided $callback function

*Exception:*

* this function will throw an error if an operation failed.

*Example code:*
## 
    // Call the OCR Document API asynchronously to scan text from an image file.
    $paramArr = array(
        'file' => "full/path/filename.jpg",
        'mode' => "document_photo")
    );
    try {
        $hodClient->PostRequest($paramArr, HODApps::OCR_DOCUMENT, REQ_MODE::ASYNC, 'requestCompleted');
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
    // callback function
    function requestCompleted($response) {
        echo $response;
    }

----
**Function GetJobResult**

    GetJobResult($jobID, $callback)

*Description:*
* Sends a request to Haven OnDemand to retrieve content identified by a job ID.

*Parameter:*
* $jobID: the job ID returned from an Haven OnDemand API upon an asynchronous call.

*Response:* 
* Response from the server will be returned via the provided callback function

*Exception:*

* this function will throw an error if the operation failed.

*Example code:*
Parse a JSON string contained a jobID and call the function to get content from Haven OnDemand server.

## 

    func requestCompleted($response) {
        $jobID = json_decode($response);
        try {
            $hodClient->GetJobResult($jobID->jobID, 'requestCompletedWithContent');  
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    function requestCompletedWithContent($response) {
        $resp = json_decode($response);
        // parse $resp object
        ...
    }

----
## Define and implement callback functions

# 
When you call the GetRequest() or PostRequest() with the ASYNC mode, the response in a callback function will be a JSON string containing a jobID.

    func requestCompletedWithJobId(response:String)
    { 
        // parse the response to get the jobID
    }
# 
When you call the GetRequest() or PostRequest() with the SYNC mode or call the GetJobResult(), the response in a callback function will be a JSON string containing the actual result of the service.

    func requestCompletedWithContent(response:String)
    { 
        // parse the response to get content values
    }

----
## Demo code 1: 

**Call the Entity Extraction API to extract people and places from cnn.com website with a synchronous GET request**

    <?php
    include "hodclient.php";
    function  getPeopleAndPlaces() {
        $hodClient = new HODClient("YOUR-API-KEY");
        $paramArr = array(
            'url' => "http://www.cnn.com",
            'arrays' => array("entity_type" => "people_eng,places_eng")
        );
        try {
            $hodClient->GetRequest($paramArr, HODApps::ENTITY_EXTRACTION, REQ_MODE::SYNC, 'requestCompletedWithContent');
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
	echo "</br>
	echo "PLACES: " . $places;
    }
    getPeopleAndPlaces();
    ?>
----

## Demo code 2:
 
**Call the OCR Document API to scan text from an image with an asynchronous POST request**

    <?php
    include "hodclient.php";
    $hodClient = new HODClient("YOUR-API-KEY");
    
    function  scanTextFromImage() {    
        $paramArr = array(
            'url' => "https://www.idolondemand.com/sample-content/images/speccoll.jpg",
            'mode' => "document_photo"
        );
        try {
            $hodClient->PostRequest($paramArr, HODApps::OCR_DOCUMENT, REQ_MODE::ASYNC, 'requestCompletedWithJobId');
        } catch (Exception $ex) {
            error_log("Error: " . $ex->getMessage());
        }
    }
    
    // implement callback function
    function requestCompletedWithJobId($response) {
        global $hodClient;
        $jobID = json_decode($response);
        try {
            $hodClient->GetJobResult($jobID->jobID, 'requestCompletedWithContent');  
        } catch (Exception $ex) {
            error_log("Error: " . $ex->getMessage());
        }
    }

    // implement callback function
    function requestCompletedWithContent($response) {
        $jsonStr = stripslashes($response);
        $respObj = json_decode($jsonStr);
       	$result = "";
        $textBlocks = $respObj->actions[0]->result->text_block;
        for ($i = 0; $i < count($textBlocks); $i++) {
            $block = $textBlocks[$i];
            $result .= $block->text . "</br>";
            $result .= "#### ####" . "</br>";
        }
        echo "SCANNED TEXT: " . $result;
    }
    scanTextFromImage();
    ?>

----
## License
Licensed under the MIT License.