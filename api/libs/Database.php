<?php
class Database
{
    public $error_info = "";   // Display the error message, if any. Use this for debugging purpose
    public $message_info = "";        // Display the last message associated with the task like  connected to database
    private $dbh = NULL;              // Used for database connection object
    public $user_name;             // Username for the database
    public $password;               // Password for database
    public $host_name;              // hostname/server for database
    public $db_name;                // Database name
    private $values = array();        // array of values
    public $query;                  // Display the last query executed
    public $rows_affected;          // Display the no. of rows affected
    public $count_rows;             // Display no. of rows returned by select query operation
    public $last_insert_id;         // Display the insert id of last insert operation executed
    public $and_or_condition = "and"; // Use 'and'/'or' in where condition of select statement, default is 'and'
    public $group_by_column = "";     // Set it to column names you wants to GROUP BY e.g. 'gender' where gender is column name
    public $order_by_column = "";     // Set it to column names you wants to ORDER BY e.g. 'colName DESC'
    public $is_sanitize = true;       // Checks whether basic sanitization of query varibles needs to be done or not.
    public $single_row = false;       // Returns single row of select query operation if true, else return all rows
    public $backticks = " ";          // Backtick for preventing error if columnname contains reserverd mysql keywords.
    public $fetch_mode = "ASSOC";  // Determines fetch mode of the result of select query,Possible values are
    // ASSOC,NUM,BOTH,COLUMN and OBJ

    public $rows_returned;           // It shows no. of rows returned in select operation
    public $is_null = false;

    function dbConnect($hostname, $user_name, $password, $dbname) {
        $this->host_name = $hostname;
        $this->user_name = $user_name;
        $this->password = $password;
        $this->db_name = $dbname;
    }

    function dbInsert($table_name, $insert_array) {
        $columns = "";
        $this->values = array();
        $parameters = "";

        foreach ($insert_array as $col => $val) {
            $columns.="" . trim($col) . ",";
            $parameters.="?,";
            $this->values[] = $val;
        }

        $columns = rtrim($columns, ",");
        $parameters = rtrim($parameters, ",");

        try {
            $this->dbh = new PDO("sqlsrv:server=$this->host_name;Database=$this->db_name", $this->user_name, $this->password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->message_info = "Connected to database";
            $this->query = "INSERT INTO $table_name ($columns) values ($parameters)";
            $stmt = $this->dbh->prepare($this->query);
            $stmt->execute($this->values);
            $this->rows_affected = $stmt->rowCount();
            $this->last_insert_id = $this->dbh->lastInsertId();
            $this->resetSettings();
        } catch (PDOException $e) {
            $this->error_info = $e->getMessage();
        }
    }

    function dbUpdate($table_name, $update_array, $update_condition_array = array()) {
        $colums_val = "";
        $where_condition = "";
        $this->values = array();
        $and_val = "";

        foreach ($update_array as $col => $val) {
            $colums_val = $colums_val . "" . trim($col) . "=?,";
            $this->values[] = $val;
        }
        $colums_val = rtrim($colums_val, ",");

        foreach ($update_condition_array as $col => $val) {
            $where_condition = $where_condition . $and_val . " " . trim($col) . "=? ";
            $this->values[] = $val;
            $and_val = $this->and_or_condition;
        }

        if ($where_condition)
            $where_condition = " WHERE " . rtrim($where_condition, ",");

        $where_condition = $this->getOrderbyCondition($where_condition);

        $where_condition = $this->getLimitCondition($where_condition);

        try {
                $this->dbh = new PDO("sqlsrv:server=$this->host_name;Database=$this->db_name", $this->user_name, $this->password);
                $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->message_info = "Connected to database";
            $this->query = "UPDATE $table_name SET $colums_val $where_condition";
            $stmt = $this->dbh->prepare($this->query);
            $stmt->execute($this->values);
            $this->rows_affected = $stmt->rowCount();
            $this->resetSettings();
        } catch (PDOException $e) {
            $this->error_info = $e->getMessage();
        }
    }

    function dbDelete($table_name, $delete_where_condition = array()) {
        $where_condition = "";
        $this->values = array();
        $and_val = "";

        foreach ($delete_where_condition as $col => $val) {
            $where_condition = $where_condition . $and_val . " " . trim($col) . "=? ";
            $this->values[] = $val;
            $and_val = $this->and_or_condition;
        }

        if ($where_condition)
            $where_condition = " WHERE " . rtrim($where_condition, ",");

        $where_condition = $this->getOrderbyCondition($where_condition);

        $where_condition = $this->getLimitCondition($where_condition);

        try {
                $this->dbh = new PDO("sqlsrv:server=$this->host_name;Database=$this->db_name", $this->user_name, $this->password);
                $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->message_info = "Connected to database";
            $this->query = "DELETE FROM $table_name $where_condition";
            $stmt = $this->dbh->prepare($this->query);
            $stmt->execute($this->values);
            $this->rows_affected = $stmt->rowCount();
            $this->resetSettings();
        } catch (PDOException $e) {
            $this->error_info = $e->getMessage();
        }
    }

    function dbSelect($table_name, $columns = array(), $select_where_condition = array()) {
        $this->values = array();
        /* Get Columns */
        $col = $this->getColumns($columns);
        $where_condition = "";

        /* Add where condition */
        if(is_bool($this->is_null))
            $where_condition = $this->getWhereCondition($select_where_condition);

        /* Add Group By and Having condition */
        $where_condition = $this->getGroupByCondition($where_condition);

        /* Add Order By condition */
        $where_condition = $this->getOrderbyCondition($where_condition);

        /* Add Limit condition */
        $where_condition = $this->getLimitCondition($where_condition);

        try {
            $this->dbh = new PDO("sqlsrv:server=$this->host_name;Database=$this->db_name", $this->user_name, $this->password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->message_info = "Connected to database";
            $this->query = "SELECT " . $col . " FROM " . $this->backticks . trim($table_name) . $this->backticks . $where_condition;
            $stmt = $this->dbh->prepare($this->query);
            $stmt->execute($this->values);
            if ($this->single_row == true)
                $result = $stmt->fetch($this->getPDOFetchmode());
            else
                $result = $stmt->fetchAll($this->getPDOFetchmode());
            $this->dbh = NULL;
            if (is_array($result))
                $this->rows_returned = count($result);

            return $result;
        } catch (PDOException $e) {
            $this->error_info = $e->getMessage();
        }
    }

    function dbCheckValue($table_name, $field_name, $field_val) {
        try {
            $this->dbh = new PDO("sqlsrv:server=$this->host_name;Database=$this->db_name", $this->user_name, $this->password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->message_info = "Connected to database";
            $this->query = "SELECT " . $this->backticks . $field_name . $this->backticks . " FROM " . $this->backticks . $table_name .
                $this->backticks . " WHERE " . $this->backticks . trim($field_name) . $this->backticks . "=?";
            $stmt = $this->dbh->prepare($this->query);
            $stmt->execute(array($field_val));
            $result = true;
            if ($stmt->rowCount() == 0)
                $result = false;
            $this->dbh = NULL;

            $this->resetSettings();
            return $result;
        } catch (PDOException $e) {
            $this->error_info = $e->getMessage();
        }
    }

    private function getColumns($columns = array()) {
        $col = "*";
        if (count($columns) > 0 && is_array($columns)) {
            $col = "";
            foreach ($columns as $column) {
                $col = $col . $this->backticks . trim($column) . $this->backticks . ",";
            }
            $col = rtrim($col, ",");
        }
        return $col;
    }

    private function getWhereCondition($select_where_condition = array()) {
        $where_condition = "";
        $matches = array();
        if (is_array($select_where_condition)) {
            foreach ($select_where_condition as $cols => $vals) {
                $compare = "=";
                if (preg_match("#([^=<>!]+)\s*(=|<|>|(!=)|(>=)|(<=)|(>=))#", strtolower(trim($cols)), $matches)) {
                    $compare = $matches[2];
                    $cols = trim($matches[1]);
                }
                $this->values[] = $vals;
                $where_condition = $where_condition . $this->backticks . $cols . $this->backticks . $compare . "? " . $this->and_or_condition;
            }

            if ($where_condition)
                $where_condition = " WHERE " . rtrim($where_condition, $this->and_or_condition);
        }
        return $where_condition;
    }

    private function getGroupByCondition($where_condition = "") {
        if ($this->group_by_column)
            $where_condition.=" GROUP BY " . $this->group_by_column;

        if ($this->group_by_column && $this->having)
            $where_condition.=" HAVING " . $this->having;

        return $where_condition;
    }

    private function getOrderbyCondition($where_condition = "") {
        if ($this->order_by_column)
            $where_condition.=" ORDER BY " . $this->order_by_column;

        return $where_condition;
    }

    private function getLimitCondition($where_condition = "") {
        if ($this->limit_val)
            $where_condition.=" LIMIT " . $this->limit_val;

        return $where_condition;
    }

    private function getPDOFetchmode() {
        switch ($this->fetch_mode) {
            case "BOTH": return PDO::FETCH_BOTH;
            case "NUM": return PDO::FETCH_NUM;
            case "ASSOC": return PDO::FETCH_ASSOC;
            case "OBJ": return PDO::FETCH_OBJ;
            case "COLUMN":return PDO::FETCH_COLUMN;
            default: return PDO::FETCH_ASSOC;
        }
    }

    private function resetSettings() {
        $this->and_or_condition = "and";
        $this->group_by_column = "";
        $this->order_by_column = "";
        $this->limit_val = "";
        $this->having = "";
        $this->between_columns = array();
        $this->in = array();
        $this->not_in = array();
        $this->like_cols = array();
        $this->is_sanitize = true;
        $this->single_row = false;
        $this->backticks = "";
        $this->fetch_mode = "ASSOC";
    }

}