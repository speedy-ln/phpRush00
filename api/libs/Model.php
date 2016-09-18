<?php
require_once 'Database.php';
class Model extends Database {

    public $dbh = null;
    public $error = "";

    public function __construct() {
        $this->dbh = new Database();
        $this->dbh->dbConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    public function __destruct() {
        $this->dbh = null;
    }

    public function appendArray($array){
        $queryArray = array();
        foreach ($array as $key => $value) {
            if(($value == NULL || empty($value)) && !is_bool($value) && ($value !== 0 || $value !== '0')){
                continue;
            } else {
                $queryArray[$key] = $value;
            }
        }
        return $queryArray;
    }

    public function insert($table, $insert){
        $this->dbh->dbInsert($table, $insert);
        if($this->dbh->rows_affected > 0){
            return $this->dbh->last_insert_id;
        } else {
            $this->setError("We couldn't save your details.");
            return FALSE;
        }
    }

    public function update($table, $update, $condition){
        $this->dbh->dbUpdate($table, $update, $condition);
        if($this->dbh->rows_affected >= 0 && !is_null($this->dbh->rows_affected)){
            return TRUE;
        } else {
            $this->setError("A problem occurred. Support has been notified. Please try again later.");
            return FALSE;
        }
    }

    public function select($table, $select = array(), $condition = array(), $statement = FALSE){
        $query = $this->dbh->dbSelect($table, $select, $condition);
        if($this->dbh->rows_returned >= 0){
            return $query;
        } else {
            $this->setError("There seems to be a problem getting information you requested.");
            return FALSE;
        }
    }

    public function delete($table, $delete){
        $this->dbh->dbDelete($table, $delete);
        if($this->rows_affected > 0){
            return TRUE;
        } else {
            $this->setError("A problem occurred. We're on it.");
            return FALSE;
        }
    }

    public function setError($error){
        $this->error = $error;
    }

    public function getError(){
        return $this->error;
    }



}