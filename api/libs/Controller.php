<?php

function httpUserResponse($code, $message, $content = false, $dbh = false){
    $db = new Database();
    $users = new Users();
    $users->setVars($data);
    $insert = $users->getVars();
    $db->dbInsert($users->getTableName(), $insert);
    $returnArray['content'] = $content;
    $returnArray['code'] = $code;
    $returnArray['message'] = $message;
    return  $returnArray;
}

