<?php

/**
 * Created by PhpStorm.
 * User: vuv
 * Date: 10/5/2015
 * Time: 3:53 PM
 */
interface REQ_MODE
{
    const ASYNC = "async";
    const SYNC = "sync";
}
abstract class CallbackFunctions
{
    // Force Extending class to define this method
    abstract protected function requestCompletedWithJobID($response);
    abstract protected function requestCompletedWithContent($response);

    // Common method
    public function onErrorOcurred($errorMEssage) {
        echo $errorMEssage . "\n";
    }
}

class HODClient
{
    private $apiKey = '';
    private $ver;
    private $hodAppBase = 'https://api.havenondemand.com/1/api/';
    private $hodJobResultBase = "https://api.havenondemand.com/1/job/result/";
    private $requestTimeout = 600;
    function HODClient($apiKey, $version = "v1") {
        $this->apiKey = $apiKey;
        $this->ver = "/".$version;
    }

    public function GetJobResult($jobID, $callback) {
        $param = $this->hodJobResultBase;
        $param .= $jobID;
        $param .= "?apikey=" . $this->apiKey;
        try {
            $ch = curl_init($param);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);

            //execute post
            $strResponse = curl_exec($ch);
           //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
                error_log("HODClient Error: " . $curlError);
                throw new Exception($curlError, $curlErrno);
            }
            curl_close($ch);
            $callback($strResponse);
        } catch (Exception $e) {
            error_log("HODClient Exception: " . $e->getMessage());
            throw new Exception($e);
        }
    }

    public function GetRequest($paramArr, $hodApp, $mode, $callback)
    {
        $app = "";
        if ($mode == "sync") {
            $app .= $this->hodAppBase . "sync/" . $hodApp . $this->ver;
        } else {
            $app .= $this->hodAppBase . "async/" . $hodApp . $this->ver;
        }
        $param = $app;
        $param .= "?apikey=" . $this->apiKey . "&";
        //
        foreach($paramArr as $key => $value) {
            if ($key == "file") {
                error_log("HODClient Error: Invalid parameter\n");
                throw new Exception("Failed. File upload must be used with PostRequest method", UPLOAD_ERR_NO_FILE);
            } else if ($key == "arrays") {
                $noSpace = preg_replace("/ /", "", $value);
                foreach($noSpace as $kk => $vv) {
                    $arr = preg_split("/,/", $vv, -1);
                    for($i = 0; $i < count($arr); $i++) {
                        $param .= "&". $kk."=".$arr[$i];
                    }
                }
            } else {
                $param .= "&". $key."=".$value;
            }
        }
        try {
            $ch = curl_init($param);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);

            //execute post
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
                error_log("HODClient Error: " . $curlError);
                throw new Exception($curlError, $curlErrno);
            }
            curl_close($ch);
            $callback($strResponse);
        } catch (Exception $e) {
            error_log("HODClient Exception: " . $e->getMessage());
            throw new Exception($e);
        }
    }

    public function PostRequest($paramArr, $hodApp, $mode, $callback)
    {
        $app = "";
        if ($mode == "sync") {
            $app .= $this->hodAppBase . "sync/" . $hodApp . $this->ver;
        } else {
            $app .= $this->hodAppBase . "async/" . $hodApp . $this->ver;
        }
        $mime_boundary = md5(time());
        $param = $this->packData($paramArr, $mime_boundary);
        $header = array('Content-Type: multipart/form-data; boundary=' . $mime_boundary);
        try {
            $ch = curl_init($app);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

            //execute post
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
                error_log("HODClient Error: " . $curlError);
                throw new Exception($curlError, $curlErrno);
            }
            curl_close($ch);
            $callback($strResponse);
        } catch (Exception $e) {
            error_log("HODClient Exception: " . $e->getMessage());
            throw new Exception($e);
        }
    }
    private function packData($paramArr, $mime_boundary) {
        $eol = "\r\n";
        $boundary = '--' . $mime_boundary;
        $data = $boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="apikey"' . $eol . $eol;
        $data .= $this->apiKey . $eol;

        foreach($paramArr as $key => $value) {
            if ($key == "file") {
                $fileName = $value;
                //$fileSize = filesize($fileName);
                if(!file_exists($fileName)) {
                    error_log("HODClient Error: " . $fileName . " does not exist.");
                    throw new Exception('File not found.', UPLOAD_ERR_NO_FILE);
                }
                $mime = mime_content_type($fileName);

                $data .= $boundary . $eol;
                $data .= 'Content-Disposition: form-data; name="'.$key.'"; filename="'.$value.'"' . $eol;
                $data .= 'Content-Type: '. $mime . $eol . $eol;

                //$handle = fopen($fileName, "rb");
                //$contents = fread($handle, $fileSize);
                $contents = file_get_contents($fileName);
                $data .= $contents . $eol;
                //fclose($handle);
            } else if ($key == "arrays") {
                $noSpace = preg_replace("/ /", "", $value);
                foreach($noSpace as $kk => $vv) {
                    $arr = preg_split("/,/", $vv, -1);
                    for($i = 0; $i < count($arr); $i++) {
                        $data .= $boundary . $eol;
                        $data .= 'Content-Disposition: form-data; name="'.$kk.'"' . $eol . $eol;
                        $data .= $arr[$i] . $eol;
                    }
                }
            } else {
                $data .= $boundary . $eol;
                $data .= 'Content-Disposition: form-data; name="'.$key.'"' . $eol . $eol;
                $data .= $value . $eol;
            }
        }
        $data .= $boundary . $eol;
        return $data;
    }
}
interface HODApps {
    const RECOGNIZE_SPEECH = "recognizespeech";

    const CANCEL_CONNECTOR_SCHEDULE = "cancelconnectorschedule";
    const CONNECTOR_HISTORY = "connectorhistory";
    const CONNECTOR_STATUS = "connectorstatus";
    const CREATE_CONNECTOR = "createconnector";
    const DELETE_CONNECTOR = "deleteconnector";
    const RETRIEVE_CONFIG = "retrieveconfig";
    const START_CONNECTOR = "startconnector";
    const STOP_CONNECTOR = "stopconnector";
    const UPDATE_CONNECTOR = "updateconnector";

    const EXPAND_CONTAINER = "expandcontainer";
    const STORE_OBJECT = "storeobject";
    const EXTRACT_TEXT = "extracttext";
    const VIEW_DOCUMENT = "viewdocument";

    const OCR_DOCUMENT = "ocrdocument";
    const RECOGNIZE_BARCODES = "recognizebarcodes";
    const DETECT_FACES = "detectfaces";
    const RECOGNIZE_IMAGES = "recognizeimages";

    const GET_COMMON_NEIGHBORS = "getcommonneighbors";
    const GET_NEIGHBORS = "getneighbors";
    const GET_NODES = "getnodes";
    const GET_SHORTEST_PATH = "getshortestpath";
    const GET_SUB_GRAPH = "getsubgraph";
    const SUGGEST_LINKS = "suggestlinks";
    const SUMMARIZE_GRAPH = "summarizegraph";

    const CREATE_CLASSIFICATION_OBJECTS = "createclassificationobjects";
    const CREATE_POLICY_OBJECTS = "createpolicyobjects";
    const DELETE_CLASSIFICATION_OBJECTS = "deleteclassificationobjects";
    const DELETE_POLICY_OBJECTS = "deletepolicyobjects";
    const RETRIEVE_CLASSIFICATION_OBJECTS = "retrieveclassificationobjects";
    const RETRIEVE_POLICY_OBJECTS = "retrievepolicyobjects";
    const UPDATE_CLASSIFICATION_OBJECTS = "updateclassificationobjects";
    const UPDATE_POLICY_OBJECTS = "updatepolicyobjects";

    const PREDICT = "predict";
    const RECOMMEND = "recommend";
    const TRAIN_PREDICTOR = "trainpredictor";

    const CREATE_QUERY_PROFILE = "createqueryprofile";
    const DELETE_QUERY_PROFILE = "deletequeryprofile";
    const RETRIEVE_QUERY_PROFILE = "retrievequeryprofile";
    const UPDATE_QUERY_PROFILE = "updatequeryprofile";

    const FIND_RELATED_CONCEPTS = "findrelatedconcepts";
    const FIND_SIMILAR = "findsimilar";
    const GET_CONTENT = "getcontent";
    const GET_PARAMETRIC_VALUES = "getparametricvalues";
    const QUERY_TEXT_INDEX = "querytextindex";
    const RETRIEVE_INDEX_FIELDS = "retrieveindexfields";

    const CLASSIFY_DOCUMENT = "classifydocument";
    const EXTRACT_CONCEPTS = "extractconcepts";
    const CATEGORIZE_DOCUMENT = "categorizedocument";
    const ENTITY_EXTRACTION = "extractentities";
    const EXPAND_TERMS = "expandterms";
    const HIGHLIGHT_TEXT = "highlighttext";
    const IDENTIFY_LANGUAGE = "identifylanguage";
    const ANALYZE_SENTIMENT = "analyzesentiment";
    const TOKENIZE_TEXT = "tokenizetext";

    const ADD_TO_TEXT_INDEX = "addtotextindex";
    const CREATE_TEXT_INDEX = "createtextindex";
    const DELETE_TEXT_INDEX = "deletetextindex";
    const DELETE_FROM_TEXT_INDEX = "deletefromtextindex";
    const INDEX_STATUS = "indexstatus";
    //const LIST_INDEXES = "listindexes"; REMOVED
    const LIST_RESOURCES = "listresources";
    const RESTORE_TEXT_INDEX = "restoretextindex";
}
?>