<?php
class Database
{
    public $error_info="";
    public $message_info="";
    private $dbh=NULL;
    public $user_name;
    public $password;
    public $host_name;
    public $db_name;
    private $values=array();
    public $query;
    public $rows_affected;
    public $count_rows;
    public $last_insert_id;
    public $and_or_condition="and";
    public $group_by_column="";
    public $order_by_column="";
    public $limit_val="";
    public $having="";
    public $like_cols=array();
    public $is_sanitize=true;
    public $single_row=false;
    public $backticks="`";
    public $fetch_mode="ASSOC";

    public $rows_returned=0;
    public $resetAllSettings=false;
    public $beginTransaction=false;
    public $commitTransaction=false;
    private $rollbackTransaction=false;

    public $isSameWhereCondition=false;
    public $isSameColumns=false;

    function dbConnect($hostname,$user_name,$password,$dbname)
    {
        $this->host_name=$hostname;
        $this->user_name=$user_name;
        $this->password=$password;
        $this->db_name=$dbname;
    }

    function commitTransaction()
    {
        try
        {
            if($this->dbh!=NULL)
            {
                $this->dbh->commit();
                $this->beginTransaction=false;
            }
        }
        catch(PDOException $e)
        {
            $this->error_info=$e->getMessage();
        }
    }

    function dbInsert($table_name,$insert_array)
    {
        $columns="";
        $this->values=array();
        $parameters="";

        foreach($insert_array as $col => $val)
        {
            $columns.="`".trim($col)."`,";
            $parameters.="?,";
            $this->values[]=$val;
        }

        $columns=rtrim($columns,",");
        $parameters=rtrim($parameters,",");

        try
        {

            if($this->beginTransaction==true)
            {
                if($this->dbh==NULL)
                {
                    $this->dbh = new PDO("mysql:host=$this->host_name;dbname=$this->db_name", $this->user_name, $this->password);
                    $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->dbh->beginTransaction();
                }
            }
            else
            {
                $this->dbh = new PDO("mysql:host=$this->host_name;dbname=$this->db_name", $this->user_name, $this->password);
                $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }

            $this->message_info="Connected to database";
            $this->query="INSERT INTO $table_name ($columns) values ($parameters)";
            $stmt = $this->dbh->prepare($this->query);
            $stmt->execute($this->values);
            $this->rows_affected=$stmt->rowCount();
            $this->last_insert_id=$this->dbh->lastInsertId();
            if($this->beginTransaction==false)
                $this->dbh = NULL;
            if($this->resetAllSettings==true)
                $this->resetSettings();
        }
        catch(PDOException $e)
        {
            if($this->beginTransaction==true)
            {
                $this->rollbackTransaction=true;
                $this->dbh->rollBack();
            }
            $this->error_info=$e->getMessage();
        }
    }

    function dbUpdate($table_name,$update_array,$update_condition_array=array())
    {
        $colums_val="";
        $where_condition="";
        $this->values=array();
        $and_val="";

        foreach($update_array as $col => $val)
        {
            $colums_val=$colums_val."`".trim($col)."`=?,";
            $this->values[]=$val;
        }
        $colums_val=rtrim($colums_val,",");

        $where_condition=$this->getWhereCondition($update_condition_array);
        /* Add Order By condition */
        $where_condition=$this->getOrderbyCondition($where_condition);
        /* Add Limit condition */
        $where_condition=$this->getLimitCondition($where_condition);

        try
        {
            if($this->rollbackTransaction&&$this->beginTransaction)
                return;
            if($this->beginTransaction==true)
            {
                if($this->dbh==NULL)
                {
                    $this->dbh = new PDO("mysql:host=$this->host_name;dbname=$this->db_name", $this->user_name, $this->password);
                    $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->dbh->beginTransaction();
                }
            }
            else
            {
                $this->dbh = new PDO("mysql:host=$this->host_name;dbname=$this->db_name", $this->user_name, $this->password);
                $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            $this->message_info="Connected to database";
            $this->query="UPDATE $table_name SET $colums_val $where_condition";
            $stmt = $this->dbh->prepare($this->query);
            $stmt->execute($this->values);
            $this->rows_affected=$stmt->rowCount();
            if($this->beginTransaction==false)
                $this->dbh = NULL;
            if($this->resetAllSettings==true)
                $this->resetSettings();

        }
        catch(PDOException $e)
        {
            if($this->beginTransaction==true)
            {
                $this->rollbackTransaction=true;
                $this->dbh->rollBack();
            }
            $this->error_info=$e->getMessage();
        }
    }

    function dbDelete($table_name,$delete_where_condition=array())
    {
        $where_condition="";
        $this->values=array();
        $and_val="";

        $where_condition=$this->getWhereCondition($delete_where_condition);
        $where_condition=$this->getOrderbyCondition($where_condition);
        $where_condition=$this->getLimitCondition($where_condition);

        try
        {
            if($this->rollbackTransaction&&$this->beginTransaction)
                return;
            if($this->beginTransaction==true)
            {
                if($this->dbh==NULL)
                {
                    $this->dbh = new PDO("mysql:host=$this->host_name;dbname=$this->db_name", $this->user_name, $this->password);
                    $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->dbh->beginTransaction();
                }
            }
            else
            {
                $this->dbh = new PDO("mysql:host=$this->host_name;dbname=$this->db_name", $this->user_name, $this->password);
                $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            $this->message_info="Connected to database";
            $this->query="DELETE FROM $table_name $where_condition";
            $stmt = $this->dbh->prepare($this->query);
            $stmt->execute($this->values);
            $this->rows_affected=$stmt->rowCount();

            if($this->beginTransaction==false)
                $this->dbh = NULL;
            if($this->resetAllSettings==true)
                $this->resetSettings();

        }
        catch(PDOException $e)
        {
            if($this->beginTransaction==true)
            {
                $this->rollbackTransaction=true;
                $this->dbh->rollBack();
            }
            $this->error_info=$e->getMessage();
        }
    }

    function dbSelect($table_name,$columns=array(),$select_where_condition=array())
    {
        $this->values=array();
        $col=$this->getColumns($columns);
        $where_condition=$this->getWhereCondition($select_where_condition);
        $where_condition=$this->getGroupByCondition($where_condition);
        $where_condition=$this->getOrderbyCondition($where_condition);
        $where_condition=$this->getLimitCondition($where_condition);

        try
        {
            $this->dbh = new PDO("mysql:host=$this->host_name;dbname=$this->db_name", $this->user_name, $this->password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->message_info="Connected to database";
            $this->query="SELECT ".$col." FROM ".$this->backticks.trim($table_name).$this->backticks.$where_condition;
            $stmt = $this->dbh->prepare($this->query);
            $stmt->execute($this->values);
            if($this->single_row==true)
                $result=$stmt->fetch($this->getPDOFetchmode());
            else
                $result=$stmt->fetchAll($this->getPDOFetchmode());
            $this->dbh = NULL;
            if(is_array($result))
                $this->rows_returned=count($result);
            if($this->single_row==true)
                $this->rows_returned=1;
            if($this->resetAllSettings==true)
                $this->resetSettings();

            return $result;
        }
        catch(PDOException $e)
        {
            $this->error_info=$e->getMessage();
        }
    }

    function dbExecuteQuery($query,$parameter_values=array())
    {
        try
        {
            $this->dbh = new PDO("mysql:host=$this->host_name;dbname=$this->db_name", $this->user_name, $this->password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->message_info="Connected to database";
            $this->query=$query;
            $stmt = $this->dbh->prepare($query);
            $stmt->execute($parameter_values);
            if($this->single_row==true)
                $result=$stmt->fetch($this->getPDOFetchmode());
            else
                $result=$stmt->fetchAll($this->getPDOFetchmode());
            $this->dbh = NULL;
            if(is_array($result))
                $this->rows_returned=count($result);
            if($this->single_row==true)
                $this->rows_returned=1;
            if($this->resetAllSettings==true)
                $this->resetSettings();
            return $result;
        }
        catch(PDOException $e)
        {
            $this->error_info=$e->getMessage();
        }
    }

    function dbCheckValue($table_name,$field_name,$field_val)
    {
        try
        {
            $this->dbh = new PDO("mysql:host=$this->host_name;dbname=$this->db_name", $this->user_name, $this->password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->message_info="Connected to database";
            $this->query="SELECT ".$this->backticks.$field_name.$this->backticks." FROM ".$this->backticks.$table_name.
                $this->backticks." WHERE ".$this->backticks.trim($field_name).$this->backticks."=?";
            $stmt = $this->dbh->prepare($this->query);
            $stmt->execute(array($field_val));
            $result=true;
            if($stmt->rowCount()==0)
                $result=false;
            $this->dbh = NULL;

            if($this->resetAllSettings==true)
                $this->resetSettings();
            return $result;
        }
        catch(PDOException $e)
        {
            $this->error_info=$e->getMessage();
        }

    }

    function dbGetColumnName($table_name)
    {
        try
        {
            $this->dbh = new PDO("mysql:host=$this->host_name;dbname=$this->db_name", $this->user_name, $this->password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->message_info="Connected to database";
            $this->query="DESCRIBE $table_name";
            $stmt = $this->dbh->prepare($this->query);
            $stmt->execute();
            $result= $stmt->fetchAll(PDO::FETCH_COLUMN);;
            $this->dbh = NULL;
            if($this->resetAllSettings==true)
                $this->resetSettings();

            return $result;
        }
        catch(PDOException $e)
        {
            $this->error_info=$e->getMessage();
        }
    }

    private function getWhereCondition($select_where_condition=array())
    {
        $where_condition="";
        $matches=array();
        $sameColName;
        if(is_array($select_where_condition))
        {
            foreach($select_where_condition as $cols => $vals)
            {
                $compare="=";
                if(preg_match("#([^=<>!]+)\s*(=|(>=)|(<=)|(>=)|<|>|(!=))#", strtolower(trim($cols)), $matches))
                {
                    $compare=$matches[2];
                    $cols=trim($matches[1]);
                }
                if(isset($sameColName)&&$this->isSameColumns)
                    $cols=$sameColName;

                $this->values[]=$vals;
                $where_condition=$where_condition." ".$this->backticks.$cols.$this->backticks.$compare."? ".$this->and_or_condition;

                if($this->isSameColumns)
                    $sameColName=$cols;
            }
            if($where_condition)
                $where_condition=" WHERE ".rtrim($where_condition,$this->and_or_condition);
        }
        return $where_condition;
    }

    private function getGroupByCondition($where_condition="")
    {
        if($this->group_by_column)
            $where_condition.=" GROUP BY ".$this->group_by_column;

        if($this->group_by_column&&$this->having)
            $where_condition.=" HAVING ".$this->having;

        return $where_condition;
    }

    private function getOrderbyCondition($where_condition="")
    {
        if($this->order_by_column)
            $where_condition.=" ORDER BY ".$this->order_by_column;

        return $where_condition;
    }

    private function getLimitCondition($where_condition="")
    {
        if($this->limit_val)
            $where_condition.=" LIMIT ".$this->limit_val;

        return $where_condition;
    }

    private function getPDOFetchmode()
    {
        switch ($this->fetch_mode)
        {
            case "BOTH":  return PDO::FETCH_BOTH;
            case "NUM":   return PDO::FETCH_NUM;
            case "ASSOC": return PDO::FETCH_ASSOC;
            case "OBJ":   return PDO::FETCH_OBJ;
            case "COLUMN":return PDO::FETCH_COLUMN;
            default:      return PDO::FETCH_ASSOC;
        }
    }

    private function resetSettings()
    {
        $this->and_or_condition="and";
        $this->group_by_column="";
        $this->order_by_column="";
        $this->limit_val="";
        $this->having="";
        $this->between_columns=array();
        $this->in=array();
        $this->not_in=array();
        $this->like_cols=array();
        $this->is_sanitize=true;
        $this->single_row=false;
        $this->backticks="`";
        $this->fetch_mode="ASSOC";
        $this->isSameColumns=false;
    }
}