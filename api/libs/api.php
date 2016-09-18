<?php
function processApi($func = false) {
    $error = array();
//        $api_key = strtolower(trim(str_replace("/", "", $_REQUEST['api_key'])));
//
//        if ($api_key !== API_KEY){//disabled
//            $error['error'] = "You are trying to access information from an invalid source.";
//            $this->response($this->json($error), 401);
//        }

    if(is_bool($func)){
        $func = strtolower(trim(str_replace("/", "", $_REQUEST['action'])).'_'.get_request_method());
        if((int) method_exists($this, $this->$func())){
            $func();
        } else {
            $error = array("Method does not exist ".$func);
            response(json($error), 401);
        }
    } else {
        $error['error'] = "The requested action is not allowed: ".$func;
        response(json($error), 401);
    }
}