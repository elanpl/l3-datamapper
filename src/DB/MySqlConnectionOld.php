<?php

namespace elanpl\DM\DB;

use mysqli;

//------------------------------------------------------------------------------
class MySqlConnectionOld extends QueryBuilder
{

    private $connected = false;
    private $pola;
    private $can_logoff = false;
    public $last_sql;
    
    private $db_server = null;
    private $db_user = null;
    private $db_pass = null;
    private $db_database = null;
    private $resultCount = 0;
    private $result = null;
    private $polaczenie = null;
    
    
    function __construct($server, $user, $pass, $database, $port=null, $charset="utf8") {
        $this->connected = false; 
        $this->db_server = $server;
        $this->db_user = $user;
        $this->db_pass = $pass;
        $this->db_database = $database;
        $this->db_port = $port;
        $this->db_charset = $charset;

    }

  
    function Login()
    {
        if (! $this->connected)
        {
            $this->can_logoff = false;
            $this->polaczenie =  $this->db_port === null ? 
            new mysqli($this->db_server, $this->db_user, $this->db_pass, $this->db_database) : 
            new mysqli($this->db_server, $this->db_user, $this->db_pass, $this->db_database, $this->db_port); 
            
            $this->polaczenie->set_charset( $this->db_charser );

            //echo $this->polaczenie->connect_error.'<br />';

            $this->connected = true;
        }
        return $this->polaczenie;
    }
	
    function Logoff() 	
    {
        return 0;
        
    }

    function affected_rows() 
    {
        return $this->polaczenie->affected_rows();
    }
	
    function Take($sql) 
    {
        if (CONNECTION_SHOW_SQL)
            echo '>> '.$sql.'<< <br />'."\n";
        //	echo 'take<br />';   
        $this->last_sql = $sql;
	
        $this->can_logoff = false;
        if (!$this->connected)
        {
            $this->Login();
            $this->can_logoff = true;
        }
      
        //echo $sql.'<br />';  
	$this->polaczenie->real_query($sql);	  
        //var_dump($this->polaczenie->error_list);
        
        $res = $this->polaczenie->use_result();
		//var_dump($res);
        if ($res)
	{
            while($wiersz=$res->fetch_assoc())
            {
		$tablica[]=$wiersz;
            }
        }
        //echo '<hr/>';
        //var_dump($tablica);

       // SaveLog('base_sql', 'take '.$sql); 
        if ($this->can_logoff)
            $this->Logoff();
      
        if (isset($tablica)) {
            $this->resultCount = count($tablica);
            return $tablica;
        } else
            return [];
    }
	
    function queryArray($sql) 
    {
	
        $this->can_logoff = false;
        if (!$this->connected) {
            $this->Login();
            $this->can_logoff = true;
        }	  
        //echo  $sql.'<br />';
        if (is_array($sql)) {
            foreach ($sql as $s ) {
                $this->last_sql = $s;
                if (CONNECTION_SHOW_SQL)
                    echo '>> '.$s.'<< <br />'."\n";
                
                $wynik = $this->polaczenie->query($s);
                if ( !$wynik)
                    return false;
            }
            $wynik = true;
        } else {
            $this->last_sql = $sql;
            
            if (CONNECTION_SHOW_SQL)
                echo '>> '.$sql.'<< <br />'."\n";
            
            $wynik = $this->polaczenie->query($sql);
            if( stripos($sql, "insert") !== false )
                $wynik = $this->PobierzOstatnieID();
            else
                $wynik = mysqli_affected_rows($this->polaczenie);
        }    

        // SaveLog('base_sql', 'exec '.$sql); 
      
        if ($this->can_logoff)
            $this->Logoff();
            
        return $wynik;
    }
  
    function PobierzOstatnieID()
    {
        //$ID=$this->polaczenie->insert_id();
        $ID = mysqli_insert_id($this->polaczenie);
   
        return $ID;	
    }

    //-------------------------------------------------------------
//    function InsertInto($tabela)
//    {
//        $sql = 'insert into '.$tabela.' (';
//        $start = true;
//        foreach( $this->pola as $field => $value)
//        {
//            if (!$start)
//                $sql.= ', ';
//            $sql.= $field;
//            $start = false;
//        }
//        $sql.= ') values ('; 
//        $start = true;
//        foreach( $this->pola as $field => $value)
//        {
//            if (!$start)
//                $sql.= ', ';
//            $sql.= $value;
//            $start = false;
//        }     
//        $sql.= ')'; 
//
//        unset($this->pola);
//
//
//        $this->last_sql = $sql;
//
//        //----------------
//        if (!$this->connected)
//        {
//            $this->Login();
//            $this->can_logoff = true;
//        }	  
//
//        $to_return = $this->Exec($sql);
//
//            //jezeli błąd to zwróć 0
//        if ( mysqli_errno($this->polaczenie) > 0){
//            $to_return = 0;
//        }
//
//        // SaveLog('base_sql', 'insert '.$sql); 
//
//        if ($this->can_logoff)
//            $this->Logoff();
//
//        return $to_return;      
//    }

//    function Update($tabela, $where = '')
//    {
//        $sql = 'update '.$tabela.' set ';
//        $start = true;
//        //  if (count($this->pola) > 0)
//        foreach( $this->pola as $field => $value)
//        {
//            if (!$start)
//                $sql.= ',';
//            $sql.= $field.' = '.$value;
//            $start = false;
//        }
//
//        if ($where != '')
//            $sql.= ' where '.$where; 
//
//        unset($this->pola);
//
//        $this->last_sql = $sql;
//
//        //----------------
//        if (!$this->connected)
//        {
//            $this->Login();
//            $this->can_logoff = true;
//        }	  
//
//        $to_return = $this->Exec($sql);
//
//        //  SaveLog('base_sql', 'update '.$sql); 
//
//        if ($this->can_logoff)
//            $this->Logoff();
//
//        return $to_return;   
//    } 

    function FieldQuoted($field,$value)
    {
        $this->pola[$field] = '"'.$value.'"';
    }

    function FieldText($field,$value)
    {
        // w formularzu funkcja odwrotna
        $this->pola[$field] = "'".htmlspecialchars($value, ENT_QUOTES)."'"; 
    }

    function FieldInteger($field,$value, $set_null = false)
    {
        if ($set_null && $value=='')
            $this->pola[$field] = "null";
        else   
            $this->pola[$field] = (int)$value; 
    }

    function FieldFloat($field,$value, $set_null = false)
    {
        if ($set_null && $value=='')
            $this->pola[$field] = "null";
        else   
        {
            if ($value == '')
                $value = 0;

            $value = str_replace(',','.',$value);  

            if (!  (bool)is_numeric($value) )
            // if (! eregi("^[0-9]+\.{0,1}[0-9]{0,2}$", $value) )
               $value = 0;   

            $this->pola[$field] = $value;
        } 
    }
  
 
    function FieldTextEditor($field,$value)
    {
        if (!$this->connected)
        {
            $this->Login();
            $this->can_logoff = true;
        }	  

        $this->pola[$field] = "'".$this->polaczenie->real_escape_string($value)."'";
    }

    function FieldIntegerImplode($field,$prefix, $values, $glue = ',', $quoted_glue = true)
    {
        $lista = array();
        foreach ( $values as $key => $value)
        {
            if ( strpos( $key, $prefix) === 0  && $value != '')
                $lista[] = (int)$value;
        }

        $value = implode($glue, $lista); 
        if ( count($lista) >0 && $quoted_glue ) 
            $value = $glue.$value.$glue; 


        $this->pola[$field] = "'".$value."'";
    } 
 
    function FieldStringImplode($field,$prefix, $values, $glue = ',', $quoted_glue = true)
    {
        $lista = array();
        foreach ( $values as $key => $value)
        {
            if ( strpos( $key, $prefix) === 0  && $value != '')
                $lista[] = htmlspecialchars($value, ENT_QUOTES);
        }

        $value = implode($glue, $lista); 
        if ( count($lista) >0 && $quoted_glue ) 
            $value = $glue.$value.$glue; 

        $this->pola[$field] = "'".$value."'";
    }  

    static function Quoted($text)
    {
        return "'".$text."'"; 	
    }

    static function ValidText($text, $quoted = true)
    {
        if ($quoted )
        // w formularzu funkcja odwrotna
            return "'".htmlspecialchars($text, ENT_QUOTES)."'";
        else
            return htmlspecialchars($text, ENT_QUOTES);    
    }

    static function ValidInteger($text, $set_null = false)
    {
        if ($set_null && $text=='')
            return "null";
        else  
            return (int)$text;  
    }

    function ValidTextEditor($text, $quoted = true)
    {
        if (!$this->connected)
        {
            $this->Login();
            $this->can_logoff = true;
        }	   

        if ($quoted)
            return "'".$this->polaczenie->real_escape_string($text)."'";
        else  
            return $this->polaczenie->real_escape_string($text);

        //return "'".$text."'";  
    }

//------------ nowe rzeczy --------------------------- 
 
    function tableExists($tableName) {
        $sql = "SHOW TABLES LIKE '".$tableName."'";
        $result = $this->Take( $sql ); 
        if ( count($result) > 0)
            return true;
        else
            return false;
    }
 
    function escapeString($string) {
        
        if (!$this->connected )
            $this->Login();
        return $this->polaczenie->real_escape_string($string);
    }
    
    function lastQuery() {
    	return $this->last_sql;
    }      
    
    function insertId() {
	return mysqli_insert_id($this->polaczenie);
    }
    
    function numRows() {
        return $this->resultCount;
    }
    
    function result() {
        return $this->result;
    }
    
    function row() {
        $row = $this->result->fetch_object();
        return $row; 
    }
    
    function freeResult() {
        if ($this->result )
            $this->result->free();
    }
    
    
    function query($sql, $binds = []) {
        
        //TODO: obsłużyć binds
        
        $this->last_sql = $sql;
        if (CONNECTION_SHOW_SQL)
            echo '>> '.$sql.'<< <br />'."\n";
        
        $this->can_logoff = false;
        if (!$this->connected)
        {
            $this->Login();
            $this->can_logoff = true;
        }
       
        //echo $sql.'<br />';
	$this->result = $this->polaczenie->query($sql);	  
        //var_dump($this->polaczenie->error_list);
            
        if ($this->result)
	    $this->resultCount = $this->result->num_rows;
        else
            $this->resultCount = 0;
    }
    
    public function fieldData($tableName) {
        $result = $this->Take( 'DESCRIBE '.$tableName );
        
        $structure = [];
        foreach ($result as $row) {
            $field = new \stdClass();
            $field->name = $row['Field'];
            $field->type = $row['Type'];
            //$field->max_length ;
            $field->primary_key = ($row['Key'] == 'PRI' ? true : false);
            $structure[] = $field; 
        }
        
        return $structure;
    }
    
}

