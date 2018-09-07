<?php
namespace elanpl\DM\DB;

class PostgreSQLSchema
{
    static function getType($type) {
        switch ( $type ) {
                case 'int':  $sql = 'integer ';  break;
                case 'bigInt':  $sql = 'bigint ';  break;
                case 'text':  $sql = 'text ';  break;
                case 'string':  $sql = 'varchar(255) ';  break;
                case 'boolean':  $sql = 'boolean ';  break;
                case 'char':  $sql = 'char(1) ';  break;
                case 'date':  $sql = 'date ';  break;
                case 'dateTime':  $sql = 'timestamp ';  break;
                case 'decimal':  $sql = 'decimal(10,2) ';  break;
                case 'double':  $sql = 'float8 ';  break;
                case 'mediumText':  $sql = 'text ';  break;
                case 'time':  $sql = 'time ';  break;
                case 'timestamp':  $sql = 'timestamp ';  break;
                case 'uuid':  $sql = 'uuid ';  break;
                case 'longtext':  $sql = 'text ';  break;
                case 'binary':  $sql = 'text ';  break; 
                
            }
        return $sql;    
    }
    
    static function createTableSql($table) {
        
        $columns = $table->getColumns();
        $indexes = $table->getIndexes();
        $tableName = $table->tableName;
        
        $sql = ' CREATE TABLE public.'.$tableName.' ( '."\n";
        $delimiter = '';
        
        foreach ($columns as $col) {
            $sql .= $delimiter.''.$col['column'].' ';
            
            
            if ( isset( $col['autoincrement'] ) && $col['autoincrement'] )
                $sql .= 'BIGSERIAL';
            else    
                $sql .= self::getType($col['type']);
            
            
            if (isset( $col['null'] ) && $col['null'] == false )
                $sql .= ' NOT NULL ';
            
            if ( isset( $col['default'] ) && $col['default'] )  {
                if ( $col['default'] == 'current' )
                    $sql .= ' DEFAULT CURRENT_TIMESTAMP  ';
                else
                    $sql .= ' DEFAULT '.$col['default'].' ';
            }
            
            if ( isset( $col['foreign'] ) && $col['foreign'] ) {
                $sql .= ' references '.$col['foreign_table'].' ('.$col['foreign_id'].') ';
            }
            
            $delimiter = ', '."\n";
        }
        
        foreach ($columns as $col) {
            if ( isset( $col['primaryKey'] ) && $col['primaryKey'] )
                $sql .= $delimiter.' CONSTRAINT '.$tableName.'_pkey PRIMARY KEY ('.$col['column'].')';
            
            if ( isset( $col['unique'] ) && $col['unique'] )
                $sql .= $delimiter.' CONSTRAINT '.$tableName.'_'.$col['column'].'_key UNIQUE('.$col['column'].')';
        }
        
        $sql .= "\n".') WITH (oids = false); ';

        
        $sqls[] = $sql;
        
      //  $sqls[] = 'commit;';
    
        foreach ( $indexes as $key => $index ) {
        
            $sql = 'CREATE '.$index['type'].' INDEX '.$tableName.'_idx'.($key+1)
                .' ON public.'.$tableName
                .' USING btree ('.implode(', ', $index['columns']).');';
            
            $sqls[] = $sql;
        }
        
        
        return $sqls;
    }
    
    static function drop($tableName) { //dropIfExists
        
    }
    
    static function hasTable($tableName) {
        
    }
    
    static function hasColumn($tableName, $fieldName) {
        
    }
    
    static function rename($tableName, $newTableName) {
        
    }

}
