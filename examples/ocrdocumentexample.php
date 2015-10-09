<?php
include "hodclient.php";

$hodClient = new HODClient("YOUR_API_KEY");

function  scanTextFromImage() {
    global $hodClient;
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