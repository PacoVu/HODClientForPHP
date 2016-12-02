# HODClient Library for PHP

Official PHP client library to help with calling [Haven OnDemand APIs](http://havenondemand.com).

## What is Haven OnDemand?
Haven OnDemand is a set of over 70 APIs for handling all sorts of unstructured data. Here are just some of our APIs' capabilities:
* Speech to text
* OCR
* Text extraction
* Indexing documents
* Smart search
* Language identification
* Concept extraction
* Sentiment analysis
* Web crawlers
* Machine learning

For a full list of all the APIs and to try them out, check out https://www.havenondemand.com/developer/apis

## Integrate HODClient into php project

### Download from Packagist and include in app
Run the following command from your terminal (composer must be installed)
```
composer require havenondemand/havenondemand
```
Place the following line in your app to include the library
```
include './vendor/havenondemand/havenondemand/lib/hodclient.php';
include './vendor/havenondemand/havenondemand/lib/hodresponseparser.php';
```

### Download directly from Github
1. Download the HODClient and HODResponseParser libraries for PHP.
2. Unzip the file and copy the hodclient.php and hodresponseparser.php under the lib folder to your project folder.

### Using the library
Creates and initializes a HODClient object.
```
HODClient($apiKey, $version = "v1")
```

* `$apiKey` is your developer apikey.
* `$version` Haven OnDemand API version. The default value is "v1".

*Example code:*
```
include "hodclient.php"
$hodClient = new HODClient("API_KEY");
```

If you want to change the API version without the need to recreate the instance of the HOD client.
```
SetVersion($newversion)
```
* `$newVersion` a string to specify an API version as "v1" or "v2"

If you want to change the API_KEY without the need to recreate the instance of the HOD client.
```
SetAPIKey($newApiKey)
```
* `newApiKey` a string to specify a new API_KEY

#
**Function GetRequest**

Sends a HTTP GET request to call an Haven OnDemand API.
```
GetRequest($paramArr, $hodApp, $async, $callback)
```
* `$paramArr` is an array() containing key/value pair parameters to be sent to a Haven OnDemand API, where the keys are the parameters of that Haven OnDemand API.

*Note:* If a parameter type is an array<>, the value must be defined as an array() or [].

E.g.:
```php
$sources = array();
array_push($sources, "http://www.cnn.com");
array_push($sources, "http://www.bbc.com");
$paramArr = array(
    'url' => $sources,
    'entity_type' => ["people_eng","places_eng","companies_eng"]
);
```

* `$hodApp` is a string to identify a Haven OnDemand API. E.g. "extractentities".
* `$async [true | false]` specifies API call as Asynchronous or Synchronous.
* `$callback` the name of a callback function. If the $callback is omitted, or is an empty string "", this function will return a response.

*Example code:*
```
// Call the Entity Extraction API synchronously to find people, places and companies from CNN website.
$paramArr = array(
    'url' => "http://www.cnn.com",
    'entity_type' => ["people_eng","places_eng","companies_eng"]
);
$response = GetRequest($paramArr, HODApps::ENTITY_EXTRACTION, false);
```
#
**Function PostRequest**

Sends a HTTP POST request to call a Haven OnDemand API.
```
PostRequest($paramArr, $hodApp, $async, $callback)
```
* `$paramArr` is an array() containing key/value pair parameters to be sent to a Haven OnDemand API, where the keys are the parameters of that Haven OnDemand API.

*Note:* If a parameter type is an array<>, the value must be defined as an array() or [].

E.g.:
```
$sources = array();
array_push($sources, "http://www.cnn.com");
array_push($sources, "http://www.bbc.com");
$paramArr = array(
    'url' => $sources,
    'entity_type' => ["people_eng","places_eng","companies_eng"]
);
```

* `$hodApp` is a string to identify an Haven OnDemand API. E.g. "ocrdocument".
* `$async [true | false]` specifies API call as Asynchronous or Synchronous.
* `$callback` the name of a callback function. If the $callback is omitted, or is an empty string "", this function will return a response.

*Example code:*
```
// Call the OCR Document API asynchronously to scan text from an image file.
$paramArr = array(
    'file' => "full/path/filename.jpg",
    'mode' => "document_photo")
);
$response = $hodClient->PostRequest($paramArr, HODApps::OCR_DOCUMENT, true);
```
#
**Function GetJobResult**

Sends a request to Haven OnDemand to retrieve content identified by a job ID.
```
GetJobResult($jobID, $callback)
```
* `$jobID` the job ID returned from an Haven OnDemand API upon an asynchronous call.
* `$callback` the name of a callback function, which the HODClient will call back and pass the response from server. If the $callback is omitted, or is an empty string "", this function will return a response.


#

**Function GetJobStatus**

Sends a request to Haven OnDemand to retrieve the status of a job identified by a job ID.
```
GetJobStatus($jobID, $callback)
```
* `$jobID` the job ID returned from an Haven OnDemand API upon an asynchronous call.
* `$callback` the name of a callback function, which the HODClient will call back and pass the response from server. If the $callback is omitted, or is an empty string "", this function will return a response.


#
**Function GetRequestCombination**

Sends a HTTP GET request to call a combination API.
```
GetRequestCombination($paramArr, $hodApp, $async, $callback)
```
* `$paramArr` is an array() containing key/value pair parameters to be sent to a Haven OnDemand API, where the keys are the parameters of the calling API.

*Note:* If a parameter type is an array [] or a JSON object {}, the value must be quoted as a string.
E.g.:
``` 
$paramArr = array(
    'url' => 'http://www.bbc.com',
    'entity_type' => '["people_eng","places_eng","companies_eng"]'
);
```

* `$hodApp` is the name of the combination API you are calling
* `$async [true | false]` specifies API call as Asynchronous or Synchronous.
* `$callback` the name of a callback function. If the $callback is omitted, or is an empty string "", this function will return a response.

*Example code:*
``` 
// Call the Entity Extraction API synchronously to find people, places and companies from CNN website.
$paramArr = array(
    'url' => "http://www.cnn.com",
    'entity_type' => '["people_eng","places_eng","companies_eng"]'
);
$response = GetRequestCombination($paramArr, "combination_api_name", false);
```
#
**Function PostRequestCombination**

Sends a HTTP POST request to call a combination API.
```
PostRequestCombination($paramArr, $hodApp, $async, $callback)
```
* `$paramArr` is an array() containing key/value pair parameters to be sent to a Haven OnDemand API, where the keys are the parameters of the calling API.

*Note:* If a parameter type is an array [] or a JSON object {}, the value must be quoted as a string.
E.g.:
``` 
$paramArr = array(
    'url' => 'http://www.bbc.com',
    'entity_type' => '["people_eng","places_eng","companies_eng"]'
);
```

* `$hodApp` is the name of the combination API you are calling
* `$async [true | false]` specifies API call as Asynchronous or Synchronous.
* `$callback` the name of a callback function. If the $callback is omitted, or is an empty string "", this function will return a response.

*Example code:*
``` 
// Call the Entity Extraction API synchronously to find people, places and companies from CNN website.
$paramArr = array(
    'url' => "http://www.cnn.com",
    'entity_type' => '["people_eng","places_eng","companies_eng"]'
);
$response = PostRequestCombination($paramArr, "combination_api_name", false);
```
#
## Demo code 1:

**Call the Entity Extraction API to extract people and places from cnn.com website with a synchronous GET request**
```php
<?php
include "hodclient.php";

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
            $people .= $entity->normalized_text . ";
            // parse any other interested information about this person ...
        } else if ($entity->type == "places_eng") {
            $places .= $entity->normalized_text . ";
            // parse any other interested information about this place ...
        }
    }
    echo "PEOPLE: " . $people;
    echo "</br>
    echo "PLACES: " . $places;
}
$hodClient = new HODClient("YOUR-API-KEY");
$paramArr = array(
    'url' => "http://www.cnn.com",
    'entity_type' => ["people_eng","places_eng"]
);
try {
    $hodClient->GetRequest($paramArr, HODApps::ENTITY_EXTRACTION, false, 'requestCompletedWithContent');
} catch (Exception $ex) {
    echo $ex->getMessage();
}
?>
```

## Demo code 2:

**Call the OCR Document API to scan text from an image with an asynchronous POST request**
```php
<?php
include "hodclient.php";
include "hodresponseparser.php";

// implement callback function
function requestCompletedWithJobId($response) {
    global $hodClient;
    $resp = new HODJobIDParser($response);
    if ($resp->error != null) {
        echo (json_encode($resp->error));
    }else {
        $hodClient->GetJobStatus($resp->jobID, 'requestCompletedWithContent');
    }
}
// implement callback function
function requestCompletedWithContent($response) {
    $resp = new HODResponseParser($response);
    if ($resp->error != null){
        $err = $resp->error;
        if ($err->error == HODErrorCode::QUEUED) {
            sleep(2);
            $hodClient->GetJobStatus($err->jobID, 'requestCompletedWithContent');
        } else if ($err->error == HODErrorCode::IN_PROGRESS) {
            sleep(5);
            $hodClient->GetJobStatus($err->jobID, 'requestCompletedWithContent');
        } else {
            $result = "<b>Error:</b></br>";
            $result .= $resp->error->error . "</br>";
            $result .= $resp->error->reason . "</br>";
        }

    }
else {

        $result = "";

        $textBlocks = $response->text_block;

        for ($i = 0; $i < count($textBlocks); $i++) {

            $block = $textBlocks[$i];
            $result .= "<html><body><p>";
            $result .= preg_replace("/\n+/", "</br>", $block->text);
            $result .= "</p></body></html>";
        }
    }
    echo "RECOGNIZED TEXT: ".$result;

}

$hodClient = new HODClient("YOUR-API-KEY");
$paramArr = array(
    'url' => "https://www.hodondemand.com/sample-content/images/speccoll.jpg",
    'mode' => "document_photo"
);
try {
    $hodClient->PostRequest($paramArr, HODApps::OCR_DOCUMENT, true, 'requestCompletedWithJobId');
} catch (Exception $ex) {
    echo $ex->getMessage();
}
?>
```

## License
Licensed under the MIT License.