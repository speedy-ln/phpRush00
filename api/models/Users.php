<?php

class Users extends \Model
{
    private $user_id;
    private $username;
    private $passwd;
    private $fullname;
    private $table_name = "users";

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPasswd()
    {
        return $this->passwd;
    }

    /**
     * @param mixed $passwd
     */
    public function setPasswd($passwd)
    {
        $this->passwd = password_hash($passwd, PASSWORD_DEFAULT);
    }

    /**
     * @return mixed
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * @param mixed $fullname
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    public function setVars($data)
    {
        if (isset($data['user_id'])) $this->setUserId($data['user_id']);
        if (isset($data['username'])) $this->setUsername($data['username']);
        if (isset($data['fullname'])) $this->setFullname($data['fullname']);
        if (isset($data['passwd'])) $this->setPasswd($data['passwd']);
    }

    public function getVars()
    {
        $return = array();
        $return['user_id'] = $this->getUserId();
        $return['username'] = $this->getUsername();
        $return['passwd'] = $this->getPasswd();
        $return['fullname'] = $this->getFullname();
        return $return;
    }
}