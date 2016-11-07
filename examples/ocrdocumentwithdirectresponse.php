<?php
include "libs/hodclient.php";
include "libs/hodresponseparser.php";

$hodClient = new HODClient("API_KEY");

$paramArr = array(
    'file' => "0005r005.gif",
    'mode' => "document_photo"
);

$response = $hodClient->PostRequest($paramArr, HODApps::OCR_DOCUMENT, REQ_MODE::ASYNC);
$resp = new HODJobIDParser($response);
if ($resp->error != null) {
    echo (json_encode($resp->error));
} else {
    $response = $hodClient->GetJobResult($resp->jobID);
    $resp = new HODResponseParser($response);
    if ($resp->error != null) {
        $error = "<b>Error:</b></br>";
        $error .= $resp->error->error . "</br>";
        $error .= $resp->error->reason . "</br>";
        $error .= $resp->error->detail . "</br>";
        echo $error;
    } elseif ($resp->status == "finished") {
        $result = "";
        $textBlocks =  $resp->payloadObj->text_block;
        for ($i = 0; $i < count($textBlocks); $i++) {
            $block = $textBlocks[$i];
            $result .= "<html><body><p>";
            $result .= preg_replace("/\n+/", "</br>", $block->text);
            $result .= "</p></body></html>";
        }
        echo "RECOGNIZED TEXT: " . $result;
    }
}
?>