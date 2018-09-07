<?php

namespace elanpl\DM\DB;
use PDO;


//------------------------------------------------------------------------------
class MySqlConnection extends QueryBuilder
{

    public  $last_sql;
    private $db_server = null;
    private $db_user = null;
    private $db_pass = null;
    private $db_database = null;
    private $resultCount = 0;
    private $result = null;
    private $conn = null;
    
    
    function __construct($server, $user, $pass, $database, $port = null, $charset="utf8") {
        $this->connected = false; 
        $this->db_server = $server;
        $this->db_user = $user;
        $this->db_pass = $pass;
        $this->db_database = $database;
        $this->db_port = $port;
        $this->db_charset = $charset;
    }

    function Login() {
        if (! $this->conn) {
            try {
                $this->conn = $this->db_port === null ? 
                new PDO("mysql:host=".$this->db_server.";dbname=".$this->db_database.";charset=".$this->db_charset."", $this->db_user, $this->db_pass) : 
                new PDO("mysql:host=".$this->db_server."port=".$this->db_port.";dbname=".$this->db_database.";charset=".$this->db_charset."", $this->db_user, $this->db_pass);                   
            } catch(PDOException $e) {
                $this->conn = null;
                die($e->getMessage());
            }
        }
        return $this->conn;
    }
	
    function Logoff() {
        if ( $this->conn != null )
            $this->conn = null;
    }

    function escapeString($string) {
        return $this->conn->quote($string);
    }
    
    function lastQuery() {
        return $this->last_sql['sql']."\n".' BINDS:'. implode(', ',$this->last_sql['binds'])."\n";
    }           
    
    function insertId() {
        return $this->conn->lastInsertId();
    }
    
    function numRows() {
        return $this->resultCount;
    }
    
    function result() {
        return $this->result;
    }
    
    function row() {
        try {
            $row = $this->result->fetch(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            die($e->getMessage());
        }    
        
        return $row; 
    }
    
    function freeResult() {
        if ($this->result ) {
            try {
                return $this->result->closeCursor();
            } catch(PDOException $e) {
                die($e->getMessage());
            } 
        }
            
    }
    
    
    function query($sql, $binds = []) {
        
        $this->last_sql = ['sql' => $sql, 'binds' => $binds];
        if (CONNECTION_SHOW_SQL)
            echo '>> '.$sql.'<< <br />'."\n";
        
        $this->Login();
        
        try {
            $this->result = $this->conn->prepare($sql);
            $this->result->execute($binds);
            $this->resultCount = $this->result->rowCount();
        } catch(PDOException $e) {
            die($e->getMessage());
        }    
        
        return true;
    }
 
    
    function tableExists($tableName) {
        $sql = "SHOW TABLES LIKE ? ";
        $this->query( $sql, [$tableName] ); 
        if ( $this->numRows() > 0)
            return true;
        else
            return false;
    }
 
    public function fieldData($tableName) {
        
        $this->query('DESCRIBE '.$tableName, []);
        $structure = [];
        while ( $row = $this->row()) {
            $field = new \stdClass();
            $field->name = $row->Field;
            $field->type = $row->Type;
            //$field->max_length ;
            $field->primary_key = ($row->Key == 'PRI' ? true : false);
            $structure[] = $field; 
        }
        
        return $structure;
    }
    
    
    function queryArray($sqlArray) 
    {
	foreach ($sqlArray as $sql ) {
            if ( !$this->query($sql) )
                return false;
        }
        
        return true;
    }
}

