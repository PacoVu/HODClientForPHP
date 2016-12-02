<?php
include "libs/hodclient.php";
include "libs/hodresponseparser.php";

$hodClient = new HODClient("API_KEY");

// implement callback function
function requestCompletedWithJobId($response) {
    global $hodClient;
    $resp = new HODJobIDParser($response);
    if ($resp->error != null) {
        echo (json_encode($resp->error));
    }else {
		try {
			$hodClient->GetJobStatus($resp->jobID, 'requestCompletedWithContent');
		}catch (Exception $ex){
			echo $ex.getMessage();
		}
    }
}

// implement callback function
function requestCompletedWithContent($response)
{
    global $hodClient;
    $resp = new HODResponseParser($response);
    if ($resp->error != null){
        $err = $resp->error;
        if ($err->error == HODErrorCode::QUEUED) {
            error_log("queued:".$err->jobID);
            sleep(2);
			try {
	           $hodClient->GetJobStatus($err->jobID, 'requestCompletedWithContent');
			}catch (Exception $ex){
				echo $ex.getMessage();
			}
        } else if ($err->error == HODErrorCode::IN_PROGRESS) {
            error_log("in progress:".$err->jobID);
            sleep(5);
            try {
				$hodClient->GetJobStatus($err->jobID, 'requestCompletedWithContent');
			}catch (Exception $ex){
				echo $ex.getMessage();
			}
        } else {
            $error = "<b>Error:</b></br>";
            $error .= $resp->error->error . "</br>";
            $error .= $resp->error->reason . "</br>";
            $error .= $resp->error->detail . "</br>";
            echo $error;
        }
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

$paramArr = array(
    'file' => "0005r005.gif",
    'mode' => "document_photo"
);
try {
	$hodClient->PostRequest($paramArr, HODApps::OCR_DOCUMENT, true, 'requestCompletedWithJobId');
}catch (Exception $ex){
	echo $ex.getMessage();
}
?>