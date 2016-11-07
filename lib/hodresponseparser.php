<?php
/**
 * Created by PhpStorm.
 * User: vuv
 * Date: 10/8/2015
 * Time: 12:51 PM
 */
include "hoderrorobject.php";

class HODResponseParser
{
    public $error;
    public $hodApp;
    public $status;
    public $payloadObj;

    public function HODResponseParser($jsonStr) {
        $this->error = null;
        $this->status = null;
        $respObj = json_decode($jsonStr);
        foreach ($respObj as $key => $value) {
            $type = gettype($value);
            if ($type == "array") {
                if ($key == "actions") {
                    $action = $value[0];
                    $this->hodApp = $action->action;
                    $this->status = $action->status;
                    if ($this->status == "failed") {
                        //parse error
                        $this->error = new HODErrorObject();
                        $errors = $action->errors;
                        foreach ($errors as $err) {
                            $this->error->error = $err->error;
                            $this->error->reason = $err->reason;
                            if ($err->detail != null)
                                $this->error->detail = $err->detail;
                            $this->error->jobID = $respObj->jobID;
                            break;
                        }
                        break;
                    } else if ($this->status == "in progress") {
                        $this->error = new HODErrorObject();
                        $this->error->error = HODErrorCode::IN_PROGRESS;
                        $this->error->reason = "Task is in progress.";
                        $this->error->jobID = $respObj->jobID;
                        break;
                    } else if ($this->status == "queued") {
                        $this->error = new HODErrorObject();
                        $this->error->error = HODErrorCode::QUEUED;
                        $this->error->reason = "Task is in queue.";
                        $this->error->jobID = $respObj->jobID;
                        break;
                    } else if ($this->status == "finished") {
                        $this->payloadObj = $action->result;
                        break;
                    }
                } else {
                    $this->status = "finished";
                    $this->payloadObj = $respObj;
                    break;
                }
            } else if ($type == "object") {
                $this->status = "finished";
                $this->payloadObj = $respObj;
                //break;
            } else { // sync response or // jobID or status
                if ($key == "jobID") {
                    $this->error->jobID =  $value;
                } else if ($key == "error") {
                    if ($this->error == null)
                        $this->error = new HODErrorObject();
                    $this->error->error = $value;
                } else if ($key == "reason") {
                    if ($this->error == null)
                        $this->error = new HODErrorObject();
                    $this->error->reason = $value;
                } else if ($key == "detail") {
                    if ($this->error == null)
                        $this->error = new HODErrorObject();
                    $this->error->detail = $value;
                }
            }
        }
        return $this;
    }
	
}
class HODJobIDParser
{
    public $error;
	public $jobID;
	public function HODJobIDParser($jsonStr)
    {
        $this->error = null;
        $respObj = json_decode($jsonStr);
        if (isset($respObj->error)) {
			$this->error = new HODErrorObject();
            $this->error->error = $respObj->error;
			if (isset($respObj->reason))
				$this->error->reason = $respObj->reason;
			if (isset($respObj->detail))
				$this->error->detail = $respObj->detail;
        } else {
			$this->jobID = $respObj->jobID;
        }
		return $this;
    }
}