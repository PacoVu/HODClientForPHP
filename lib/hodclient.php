<?php

/**
 * Created by PhpStorm.
 * User: vuv
 * Date: 10/5/2015
 * Time: 3:53 PM
 */

class HODClient
{
    const LOG_ERROR = false;
    private $apiKey = "";
    private $ver = "";
    private $hodAppBase = "https://api.havenondemand.com/1/api/";
    private $hodJobResultBase = "https://api.havenondemand.com/1/job/result/";
    private $hodJobStatusBase = "https://api.havenondemand.com/1/job/status/";
    private $hodCombineAsync = "async/executecombination";
	private $hodCombineSync = "sync/executecombination";
    private $requestTimeout = 600;
    private $mime_boundary = "";

    function HODClient($apiKey, $version = "v1") {
        $this->apiKey = $apiKey;
        $this->ver = "/".$version;
    }

	public function SetVersion($newVersion) {
		$this->ver = "/".$newVersion;
	}

	public function setAPIKey($newkey) {
		$this->apiKey = $newkey;
	}

    public function GetJobResult($jobID, $callback="") {
        $param = sprintf("%s%s?apikey=%s",$this->hodJobResultBase,$jobID,$this->apiKey);
        try {
            $ch = curl_init($param);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);

            $strResponse = curl_exec($ch);
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
				if (self::LOG_ERROR)
	                error_log("HODClient Error: " . $curlError);
                throw new Exception($curlError, $curlErrno);
            } else {
                curl_close($ch);
                if ($callback == "") {
                    return $strResponse;
                } else
                    $callback($strResponse);
            }
        } catch (Exception $e) {
            if (self::LOG_ERROR)
                error_log("HODClient Exception: " . $e->getMessage());
            throw $e;
        }
    }
    public function GetJobStatus($jobID, $callback="") {
        $param = sprintf("%s%s?apikey=%s",$this->hodJobStatusBase,$jobID,$this->apiKey);
        try {
            $ch = curl_init($param);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);

            $strResponse = curl_exec($ch);
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
				if (self::LOG_ERROR)
	                error_log("HODClient Error: " . $curlError);
                throw new Exception($curlError, $curlErrno);
            } else {
                curl_close($ch);
                if ($callback == "") {
                    return $strResponse;
                } else
                    $callback($strResponse);
            }
        } catch (Exception $e) {
            if (self::LOG_ERROR)
                error_log("HODClient Exception: " . $e->getMessage());
             throw $e;
        }
    }

    public function GetRequest($paramArr, $hodApp, $async=true, $callback="")
    {
       if ($async) {
            $param = sprintf("%sasync/%s%s?apikey=%s",$this->hodAppBase,$hodApp,$this->ver,$this->apiKey);
        } else {
            $param = sprintf("%ssync/%s%s?apikey=%s",$this->hodAppBase,$hodApp,$this->ver,$this->apiKey);
        }
        //
        foreach($paramArr as $key => $value) {
            if ($key == "file") {
				if (self::LOG_ERROR)
	                error_log("HODClient Error: Invalid parameter\n");
                throw new Exception("File upload must be used with PostRequest method", UPLOAD_ERR_NO_FILE);
            }else{
				$type = gettype($value);
				if ($type == "array") {
					foreach($value as $vv) {
						$param .= sprintf("&%s=%s",$key,rawurlencode($vv));
					}
				} else {
					$param .= sprintf("&%s=%s",$key,rawurlencode($value));
				}
			}
        }
        try {
            $ch = curl_init($param);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);

            $strResponse = curl_exec($ch);
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
                if (self::LOG_ERROR)
                    error_log("HODClient Error: " . $curlError);
                throw new Exception($curlError, $curlErrno);
            } else {
                curl_close($ch);
                if ($callback == "")
                    return $strResponse;
                else
                    $callback($strResponse);
            }
        } catch (Exception $e) {
            if (self::LOG_ERROR)
                error_log("HODClient Exception: " . $e->getMessage());
            throw $e;
        }
    }

    public function PostRequest($paramArr, $hodApp, $async=true, $callback="")
    {
        if ($async) {
			$endpoint = sprintf("%sasync/%s%s",$this->hodAppBase,$hodApp,$this->ver);
        } else {
            $endpoint = sprintf("%ssync/%s%s",$this->hodAppBase,$hodApp,$this->ver);
        }
        $this->mime_boundary = md5(time());
        $param = $this->packData($paramArr);
        if ($param == null)
        {
            if ($callback == "")
                return null;
            else {
                $callback(null);
                return;
            }
        }
        $header = array('Content-Type: multipart/form-data; boundary=' . $this->mime_boundary);
        try {
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

            $strResponse = curl_exec($ch);
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
                if (self::LOG_ERROR)
                    error_log("HODClient Error: " . $curlError);
                throw new Exception($curlError, $curlErrno);
            } else {
                curl_close($ch);
                if ($callback == "")
                    return $strResponse;
                else
                    $callback($strResponse);
            }
        } catch (Exception $e) {
            if (self::LOG_ERROR)
                error_log("HODClient Exception: " . $e->getMessage());
            throw $e;
        }
    }
    private function packData($paramArr) {
        $data = $this->postDataField("apikey", $this->apiKey);
        foreach($paramArr as $key => $value) {
            $type = gettype($value);
            if ($type == "array") {
                foreach($value as $kk => $vv) {
                    if ($key == "file") {
                        $fileName = $vv;
                        //$fileSize = filesize($fileName);
                        if(!file_exists($fileName)) {
                            if (self::LOG_ERROR)
                                error_log("HODClient Error: " . $fileName . " does not exist.");
                            throw new Exception('File not found.', UPLOAD_ERR_NO_FILE);
                        }
                        $mime = mime_content_type($fileName);
						$data .= $this->postFileField($key, $value, $mime);

                        //$handle = fopen($fileName, "rb");
                        //$contents = fread($handle, $fileSize);
                        $contents = file_get_contents($fileName);
                        $data .= $contents . "\r\n";
                        //fclose($handle);
                    } else {
                        $data .= $this->postDataField($key, $vv);
                    }
                }
            } else {
                if ($key == "file") {
                    $fileName = $value;
                    //$fileSize = filesize($fileName);
                    if(!file_exists($fileName)) {
                        if (self::LOG_ERROR)
                            error_log("HODClient Error: " . $fileName . " does not exist.");
                        throw new Exception('File not found.', UPLOAD_ERR_NO_FILE);
                    }
                    $mime = mime_content_type($fileName);
					$data .= $this->postFileField($key, $value, $mime);

                    //$handle = fopen($fileName, "rb");
                    //$contents = fread($handle, $fileSize);
                    $contents = file_get_contents($fileName);
                    $data .= $contents . "\r\n";
                    //fclose($handle);
                } else {
                    $data .= $this->postDataField($key, $value);
                }
            }
        }
        $data .= $this->getBoundary();
        return $data;
    }

	public function GetRequestCombination($paramArr, $hodApp, $async=true, $callback="")
    {
        $queryStr = $this->hodAppBase;
        if ($async) {
            $queryStr .= sprintf("%s%s",$this->hodCombineAsync,$this->ver);
        } else {
            $queryStr .= sprintf("%s%s",$this->hodCombineSync,$this->ver);
        }
		$queryStr .= sprintf("?apikey=%s&combination=%s", $this->apiKey, $hodApp);
        foreach($paramArr as $key => $value) {
            if ($key == "file") {
				if (self::LOG_ERROR)
	                error_log("HODClient Error: Invalid parameter\n");
                throw new Exception("File upload must be used with PostRequestCombination method", UPLOAD_ERR_NO_FILE);
            }else{
	            if ($this->isJSON($value))
					$param = '&parameters={"name":"%s","value":%s}';
				else
					$param = '&parameters={"name":"%s","value":"%s"}';
				$queryStr .= sprintf($param,$key,rawurlencode($value));
			}
        }
        try {
            $ch = curl_init($queryStr);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);

            $strResponse = curl_exec($ch);
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
				if (self::LOG_ERROR)
	                error_log("HODClient Error: " . $curlError);
                throw new Exception($curlError, $curlErrno);
            } else {
                curl_close($ch);
                if ($callback == "")
                    return $strResponse;
                else
                    $callback($strResponse);
            }
        } catch (Exception $e) {
			if (self::LOG_ERROR)
	            error_log("HODClient Exception: " . $e->getMessage());
            throw $e;
        }
    }
	public function PostRequestCombination($paramArr, $hodApp, $async=true, $callback="")
    {
        $endPoint = $this->hodAppBase;
        if ($async) {
            $endPoint .= $this->hodCombineAsync . $this->ver;
        } else {
            $endPoint .= $this->hodCombineSync . $this->ver;
        }

        $this->mime_boundary = md5(time());
        $param = $this->packCombinationData($paramArr, $hodApp);
        $header = array('Content-Type: multipart/form-data; boundary=' . $this->mime_boundary);
        try {
            $ch = curl_init($endPoint);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

            $strResponse = curl_exec($ch);
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
				if (self::LOG_ERROR)
	                error_log("HODClient Error: " . $curlError);
                throw new Exception($curlError, $curlErrno);
            } else {
                curl_close($ch);
                if ($callback == "")
                    return $strResponse;
                else
                    $callback($strResponse);
            }
        } catch (Exception $e) {
			if (self::LOG_ERROR)
	            error_log("HODClient Exception: " . $e->getMessage());
            throw $e;
        }
    }
	private function packCombinationData($paramArr, $hodApp) {
		$data = $this->postDataField("apikey", $this->apiKey);
        $data .= $this->postDataField("combination", $hodApp);

        foreach($paramArr as $key => $value) {
            if ($key == "file") {
				if (self::LOG_ERROR)
	                error_log("HODClient Error: file resource is not yet supported\n");
                throw new Exception("File resource is not yet supported", UPLOAD_ERR_NO_FILE);
            } else {
				if ($this->isJSON($value))
					$param = '{"name":"%s","value":%s}';
				else
					$param = '{"name":"%s","value":"%s"}';
				$data .= $this->postDataField("parameters", sprintf($param, $key, $value));
			}
        }
        $data .= $this->getBoundary(); //$boundary . $eol;
        return $data;
    }

    private function getBoundary() {
		return sprintf("--%s\r\n", $this->mime_boundary);
	}
    private function postDataField($key, $value)
    {
        $eol = "\r\n";
        $data = $this->getBoundary();
        $data .= sprintf('Content-Disposition: form-data; name="%s"%s%s', $key, $eol, $eol);
        $data .= sprintf("%s%s", $value, $eol);
        return $data;
    }
	private function postFileField($key, $value, $mime)
    {
		$eol = "\r\n";
        $data = $this->getBoundary();
        $data .= sprintf('Content-Disposition: form-data; name="%s"; filename="%s"%s', $key, $value, $eol);
        $data .= sprintf("Content-Type: %s%s%s", $mime, $eol, $eol);
        return $data;
    }
	private function isJSON($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
}

interface HODApps
{
    const RECOGNIZE_SPEECH = "recognizespeech";
    const DETECT_SCENE_CHANGES = "detectscenechanges";
    const LICENSE_PLATE_RECOGNITION = "licenseplaterecognition";

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

    const MAP_COORDINATES = "mapcoordinates";

    const OCR_DOCUMENT = "ocrdocument";
    const RECOGNIZE_BARCODES = "recognizebarcodes";
    const DETECT_FACES = "detectfaces";
    const RECOGNIZE_IMAGES = "recognizeimages";

	const ANOMALY_DETECTION = "anomalydetection";
	const TRAIN_ANALYSIS = "trendanalysis";

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
    const DELETE_PREDICTION_MODEL = "deletepredictionmodel";
    const GET_PREDICTION_MODEL_DETAILS = "getpredictionmodeldetails";

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

    const AUTO_COMPLETE = "autocomplete";
    const CLASSIFY_DOCUMENT = "classifydocument";
    const EXTRACT_CONCEPTS = "extractconcepts";
    const CATEGORIZE_DOCUMENT = "categorizedocument";
    const ENTITY_EXTRACTION = "extractentities";
    const EXPAND_TERMS = "expandterms";
    const HIGHLIGHT_TEXT = "highlighttext";
    const IDENTIFY_LANGUAGE = "identifylanguage";
    const ANALYZE_SENTIMENT = "analyzesentiment";
    const GET_TEXT_STATISTICS = "gettextstatistics";
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
