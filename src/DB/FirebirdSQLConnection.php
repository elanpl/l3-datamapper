<?php

namespace elanpl\DM\DB;

use PDO;

//------------------------------------------------------------------------------
class FirebirdSQLConnection extends QueryBuilder
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
    
    function __construct($server, $user, $pass, $database, $port = null, $charset="utf8") {
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
                $dsn = $this->db_port === null ? "firebird:dbname=".$this->db_server.":/".$this->db_database
                                                : "firebird:dbname=".$this->db_server."/".$this->db_port.":/".$this->db_database;
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
        return null; //$this->conn->lastInsertId();
        
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
        if ( ! $this->result) {
            $this->resultCount = 0;
            return false;
        }
            
        try {
            $row = $this->result->fetch(PDO::FETCH_OBJ);
            $this->resultCount++;
            
            if (! $row) {
                $this->resultCount--;
            }
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
            if (! $this->result ) {
                print_r($this->conn->errorInfo());
                
                $this->resultCount = null;
                return false;
            } 
            
            if (! $this->result->execute($binds) ) {
               // print_r($this->result->errorInfo());
            
                $this->resultCount = null;
                return false;
            } else {
                // tutaj problem że $this->result->rowCount() nie zwraca prawidłowej wartości tylko zawsze 0
                // poprawna wartość jest ustawiana w row()
                $this->resultCount = 1; //$this->result->rowCount();
            }
        } catch(PDOException $e) {
            die($e->getMessage());
        }    
        
        return true;
    }
 
    
    public function fieldData($tableName) {
        //SELECT * FROM rdb$relations - lista tabel 
        
        
        //f.rdb$character_len as character_len, f.rdb$field_length,
        $sql = 'select rf.rdb$field_name as field_name, f.rdb$field_type, f.rdb$field_sub_type, 
                    
                    rf.rdb$field_id, rf.rdb$null_flag,
                    t.rdb$type_name as type_name
                FROM rdb$relation_fields rf
                JOIN rdb$fields f ON f.rdb$field_name = rf.rdb$field_source
                JOIN rdb$types t ON f.rdb$field_type = t.rdb$type and t.rdb$field_name = ?
                WHERE rf.rdb$relation_name =  ? ';
        $binds = ['RDB$FIELD_TYPE', $tableName]; 
        
      //  $sql = 'select * FROM rdb$relation_fields';
      //  $binds = [];
        
        $this->query($sql, $binds);
        
        $structure = [];
        while ( $row = $this->row()) {
            $field = new \stdClass();
            $field->name = trim($row->FIELD_NAME);
            $field->type = $row->TYPE_NAME;
           // $field->max_length = $row->character_len;
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
        
        $this->row(); //bez odpalenia row(), numRows() przekłamuje
        
        if ( $this->numRows() > 0)
            return true;
        else
            return false;
           
    }
    
}

