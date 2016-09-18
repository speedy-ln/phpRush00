<?php
$_allow = array();
$_content_type = "application/json";
$_request = array();

$_code = 200;

function construct($strip = true){
    inputs($strip);
}

function get_referer(){
    return $_SERVER['HTTP_REFERER'];
}

function response($data,$status){
    global $_code;
    $_code = ($status)?$status:200;
    set_headers();
    echo $data;
    exit;
}

function json($data) {
    if (is_array($data)) {
        return json_encode($data, 1);
    } else {
        return $data;
    }
}

function get_status_message(){
    global $_code;
    $status = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported');
    return ($status[$_code])?$status[$_code]:$status[500];
}

function get_request_method(){
    return $_SERVER['REQUEST_METHOD'];
}

function inputs($strip = true){
    switch(get_request_method()){
        case "POST":
            global $_request;
            $_request = cleanInputs($_POST, $strip);
            break;
        case "GET":
            global $_request;
            $_request = cleanInputs($_POST, $strip);
            break;
        case "DELETE":
            global $_request;
            $_request = cleanInputs($_GET);
            break;
        default:
            response('',406);
            break;
    }
}

function cleanInputs($data, $strip = true){
    $clean_input = array();
    if(is_array($data)){
        foreach($data as $k => $v){
            $clean_input[$k] = cleanInputs($v, $strip);
        }
    }else{
        if(get_magic_quotes_gpc()){
            $data = trim(stripslashes($data));
        }
        if($strip){
            $data = strip_tags($data);
        }
        $clean_input = trim($data);
    }
    return $clean_input;
}

function set_headers(){
    global $_code;
    global $_content_type;
    header("HTTP/1.1 ".$_code." ".get_status_message());
    header("Content-Type:".$_content_type);
}