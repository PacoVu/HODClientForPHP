<?php
/**
 * Created by PhpStorm.
 * User: vuv
 * Date: 10/7/2015
 * Time: 9:18 PM
 */
class HODErrorObject
{
    public $error;
    public $reason;
    public $detail;
    public $jobID;
}

interface HODErrorCode
{
    const TIMEOUT = 1600;
    const IN_PROGRESS = 1610;
    const QUEUED = 1620;
    const NONSTANDARD_RESPONSE = 1630;
    const INVALID_PARAM = 1640;
    const INVALID_HOD_RESPONSE = 1650;
    const UNKNOWN_ERROR = 1660;
}