<?php
function register($data)
{
//        if($this->userExists($data['username'])){
//            return $this->httpUserResponse(400, 'You\'ve already been registered.');
//        }
    $user = new Users();
    if ($user->dbCheckValue($user->getTableName(), "username", $data['username']))
        return $this->httpUserResponse(400, 'You\'ve already been registered.');
    $user->setVars($data);
    $insert = $user->appendArray($user->getVars());
    $user->dbInsert($user->getTableName(), $insert);
    if($user->rows_affected > 0) {
        $insert['user_id'] = $user->last_insert_id;
        unset($insert['passwd']);
        return $this->httpUserResponse(200, "You have been successfully registered.", $insert);
    }
    else
        return $this->httpUserResponse(400, "Unable to register user at this moment. Please try again later.", false, $user->dbh);
}
