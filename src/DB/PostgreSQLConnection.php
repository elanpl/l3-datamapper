<?php

namespace elanpl\DM\DB;

use PDO;

//------------------------------------------------------------------------------
class PostgreSQLConnection extends QueryBuilder
{
    const DefaultBacktick = '';

    public  $last_sql;
    
    private $db_server = null;
    private $db_user = null;
    private $db_pass = null;
    private $db_database = null;
    private $resultCount = null;
    private $result = null;
    private $conn = null;
    private $sequence = null;
    
    function __construct($server, $user, $pass, $database, $port=null, $charset="utf8") {
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
                $dsn = $this->db_port === null ? "pgsql:host=".$this->db_server.";dbname=".$this->db_database 
                                                : "pgsql:host=".$this->db_server.";port=".$this->db_port.";dbname=".$this->db_database;
                $this->conn =  new PDO($dsn, $this->db_user, $this->db_pass);                         
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
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

    function queryArray($sqlArray) 
    {
	foreach ($sqlArray as $sql ) {
            if ( !$this->query($sql) )
                return false;
        }
        
        return true;
    }
 
    function escapeString($string) {
        return $this->conn->quote($string);
    }
    
    function lastQuery() {
        return $this->last_sql['sql']."\n".' BINDS:'. implode(', ',$this->last_sql['binds'])."\n";
    }      
    
    function insertId() {
        return $this->conn->lastInsertId ();
        
        // wersja do oid ??
//        if ($this->sequence) {
//            try {
//                return $this->conn->lastInsertId ( $this->sequence  );
//            } catch(PDOException $e) {
//                die($e->getMessage());
//            }    
//        }
//        return false;
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
        
        $this->resultCount = null;
        
        $this->last_sql = ['sql' => $sql, 'binds' => $binds];
        if (CONNECTION_SHOW_SQL)
            echo '>> '.$this->lastQuery().'<< <br />'."\n";
        
        $this->Login();
        
        try {
       
            $this->result = $this->conn->prepare($sql);
            
            if (! $this->result->execute($binds) ) {
               // print_r($this->result->errorInfo());
                $this->resultCount = null;
                return false;
            } else {
                $this->resultCount = $this->result->rowCount();
            }
        } catch(PDOException $e) {
            die($e->getMessage());
        }    
        
        return true;
    }
 
    
    public function fieldData($tableName) {
        $sql = 'select column_name, data_type, character_maximum_length
                from INFORMATION_SCHEMA.COLUMNS where table_name = ?';
        $binds = [$tableName];
        
        $this->query($sql, $binds);
        
        $structure = [];
        while ( $row = $this->row()) {
            $field = new \stdClass();
            $field->name = $row->column_name;
            $field->type = $row->data_type;
            $field->max_length = $row->character_maximum_length;
           // $field->primary_key = ($row['Key'] == 'PRI' ? true : false);
            $structure[] = $field; 
        }
        
        return $structure;
    }
    
    function tableExists($tableName) {
        
        $sql = "   SELECT 1 
            FROM   pg_catalog.pg_class c
            JOIN   pg_catalog.pg_namespace n ON n.oid = c.relnamespace
            WHERE  c.relname = ?
            AND    n.nspname = 'public'
            AND    c.relkind = 'r'    -- only tables ";
        
        $this->query($sql, [$tableName] );
        if ( $this->numRows() > 0)
            return true;
        else
            return false;
           
    }
    
}

