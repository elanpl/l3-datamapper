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
                $dsn = $this->db_port === null ? "mysql:host=".$this->db_server.";dbname=".$this->db_database.";charset=".$this->db_charset
                                                : "mysql:host=".$this->db_server.";port=".$this->db_port.";dbname=".$this->db_database.";charset=".$this->db_charset;
                $this->conn =  new PDO($dsn, $this->db_user, $this->db_pass);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);                         
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
            echo '>> '.$this->lastQuery().'<< <br />'."\n";
        
        $this->Login();
        
        try {
            $this->result = $this->conn->prepare($sql);
            $success = $this->result->execute($binds);
            if ( $success )
                $this->resultCount = $this->result->rowCount();
            else
                die('QUERY ERROR '.$this->lastQuery());
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
            $field->baseType = $row->Type;
            if ( $field->baseType == 'tinyint(1)' || $field->baseType == 'boolean' || $field->baseType == 'bit(1)' )
                $field->type = 'boolean';
            elseif ( substr($field->baseType,0,4) == 'int(' || substr($field->baseType,0,8) == 'tinyint(' 
                        ||  substr($field->baseType,0,10) == 'mediumint(' || substr($field->baseType,0,7) == 'bigint(' || substr($field->baseType,0,4) == 'bit(' )
                $field->type = 'int';
            elseif ( substr($field->baseType,0,6) == 'double' ||  substr($field->baseType,0,7) == 'decimal' || substr($field->baseType,0,5) == 'float' || substr($field->baseType,0,4) == 'real'  )
                $field->type = 'float';
            else    
                $field->type = '';
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

