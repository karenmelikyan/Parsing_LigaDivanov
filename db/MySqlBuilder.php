<?php


class MySqlBuilder implements \parser\DatabaseInterface
{
    private $db;
    private $dbHost;
    private $dbName;
    private $dbUserName;
    private $dbPassword;
    private $dbPort;
    private $dbTableName;
    private $tableColumnProperties = [];

    /**
     * @param $dbName
     * @return MySqlBuilder
     */
    public function setDbName($dbName): self
    {
        $this->dbName = $dbName;
        return $this;
    }

    /**
     * @param $dbHost
     * @return MySqlBuilder
     */
    public function setDbHost($dbHost): self
    {
        $this->dbHost = $dbHost;
        return $this;
    }

    /**
     * @param $dbUserName
     * @return MySqlBuilder
     */
    public function setDbUserName($dbUserName): self
    {
        $this->dbUserName = $dbUserName;
        return $this;
    }

    /**
     * @param $dbPassword
     * @return MySqlBuilder
     */
    public function setDbPassword($dbPassword): self
    {
        $this->dbPassword = $dbPassword;
        return $this;
    }

    /**
     * @param $dbPort
     * @return MySqlBuilder
     */
    public function setDbPort($dbPort): self
    {
        $this->dbPort = $dbPort;
        return $this;
    }

    /**
     * @param $dbTableName
     * @return MySqlBuilder
     */
    public function setDbTableName($dbTableName): self
    {
        $this->dbTableName = $dbTableName;
        return $this;
    }

    /**
     * @param $tableColumnProperties
     * @return MySqlBuilder
     */
    public function setDbColumnProperties($tableColumnProperties): self
    {
        $this->tableColumnProperties = $tableColumnProperties;
        return $this;
    }

    /**
     * @return MySqlBuilder
     */
    public function build(): self
    {
        /** connect to SQL database & return instance of connection */
        $this->db = new mysqli($this->dbHost, $this->dbUserName, $this->dbPassword,
            $this->dbName, $this->dbPort) or die("Error " . mysqli_error($this->db));

        /** create request string `str` from properties */
        $str = '(';
        foreach($this->tableColumnProperties as $key => $value){
            $str .= $key . ' ' . $this->tableColumnProperties[$key] . ', ';
        }
        $str = substr($str, 0, strlen($str) - 2) . ')';

        /** create table if not exist */
        if(!$this->db->query("CREATE TABLE IF NOT EXISTS " . $this->dbTableName)) {
            $query = 'CREATE Table' . ' ' . $this->dbTableName . ' ' . $str;
        }

        /** to do request to database */
        mysqli_query($this->db, $query);

        return $this;
    }


    /**
     * @return array
     *
     * returns a two-dimensional array
     */
    public function getAll(): ?array
    {
        $dataArray = [];
        $query = "SELECT * FROM " . $this->dbTableName;
        $result = mysqli_query($this->db, $query);

        if($result) {
            $rows = mysqli_num_rows($result); // get amount of rows
            for ($i = 0; $i < $rows; ++ $i) {
                $dataArray[] = mysqli_fetch_row($result);
            }
            return $dataArray;
        }

        return null;
    }

    /**
     * @param $fieldName
     * @param $value
     * @return array|null
     *
     * returns a two-dimensional array
     */
    public function getByColumn($columnName, $value): ?array
    {
        $dataArray = [];
        $query = "SELECT * FROM " . $this->dbTableName . " WHERE " . $columnName . " = " . "'$value'";
        $result = mysqli_query($this->db, $query);

        if($result) {
            $rows = mysqli_num_rows($result); // get amount of rows
            for ($i = 0; $i < $rows; ++ $i) {
                $dataArray[] = mysqli_fetch_row($result);
            }
        }

        if(count($dataArray) > 0){
            return $dataArray;
        }

        return null;
    }

    /**
     * @param $fieldName
     * @param $value
     * @return bool
     */
    public function deleteByColumn($columnName, $value): bool
    {
        $query = "DELETE FROM " . $this->dbTableName . " WHERE " . $columnName . " = " . "'$value'";
        if(mysqli_query($this->db, $query)){
            return true;
        }

        return false;
    }

    /**
     * @param $postData
     * @return bool
     */
    public function insert($postData): bool
    {
        /** extract data  & create sql string for request */
        $str = ' VALUES(NULL, ';
        foreach($this->tableColumnProperties as $key => $value){
            if($key !== 'id'){
                $str .= '\'' . $postData[$key] . '\', ';
            }
        }
        $str = substr($str, 0, strlen($str) - 2) . ')';

        /** to do sql request */
        $query = "INSERT INTO " . $this->dbTableName . $str;
        if(mysqli_query($this->db, $query)){
            return true;
        }

        return false;
    }

}