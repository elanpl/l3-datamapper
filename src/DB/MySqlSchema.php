<?php
namespace elanpl\DM\DB;

class MySqlSchema
{
    static function getType($type) {
        switch ( $type ) {
                case 'int':  $sql = 'int(11) ';  break;
                case 'bigInt':  $sql = 'int(20) ';  break;
                case 'binary':  $sql = 'blob ';  break;
                case 'boolean':  $sql = 'tinyint(1) ';  break;
                case 'char':  $sql = 'char(1) ';  break;
                case 'date':  $sql = 'date ';  break;
                case 'dateTime':  $sql = 'datetime ';  break;
                case 'decimal':  $sql = 'decimal(10,2) ';  break;
                case 'double':  $sql = 'double ';  break;
                case 'mediumText':  $sql = 'mediumtext ';  break;
                case 'text':  $sql = 'text ';  break;
                case 'time':  $sql = 'time ';  break;
                case 'timestamp':  $sql = 'timestamp(1) ';  break;
                case 'uuid':  $sql = 'binary(16) ';  break;
                case 'longtext':  $sql = 'longtext ';  break;
                case 'string':  $sql = 'varchar(255) ';  break;
            }
        return $sql;    
    }
    
    static function createTableSql($table) {
        
        $columns = $table->getColumns();
        $indexes = $table->getIndexes();
        $tableName = $table->tableName;
        
        $sql = ' CREATE TABLE '.$tableName.' ( '."\n";
        $delimiter = '';
        
        foreach ($columns as $col) {
            $sql .= $delimiter.'`'.$col['column'].'` ';
            
            $sql .= self::getType($col['type']);
            
            //MySQL has function UUID() so you don't need PHP to generate it. You can remove dashes and 
            //save the hex number as binary(16). If you do it via trigger, 
            //it's SELECT UNHEX(REPLACE(UUID(), '-', ''));, make it unique
            
            //UUID_TO_BIN/BIN_TO_UUID
            //INSERT INTO t VALUES(UUID_TO_BIN(UUID(), true))
            
            if (isset( $col['null'] ) && $col['null'] == false )
                $sql .= ' NOT NULL ';
            
            if ( isset( $col['default'] ) && $col['default'] ) {
                if ( $col['default'] == 'current')
                    $sql .= ' DEFAULT CURRENT_TIMESTAMP(1)';
                else
                    $sql .= ' DEFAULT '.$col['default'].' ';
            }
            
            $delimiter = ', '."\n";
        }
        
        $sql .= "\n".') ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_polish_ci; ';

        $sqls[] = $sql;
        
        foreach ($columns as $col) {
            if ( isset( $col['primaryKey'] ) && $col['primaryKey'] )
                $sqls[] = ' ALTER TABLE `'.$tableName.'` ADD PRIMARY KEY (`'.$col['column'].'`);';
            
            if ( isset( $col['unique'] ) && $col['unique'] )
                $sqls[] = ' ALTER TABLE `'.$tableName.'` ADD UNIQUE `UNIQUE_'.$tableName.'_'.$col['column'].'` (`'.$col['column'].'`);';
            
            if ( isset( $col['autoincrement'] ) && $col['autoincrement'] )
                $sqls[] = 'ALTER TABLE `'.$tableName.'` MODIFY `'.$col['column'].'` '.self::getType($col['type']).' NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;';
            
            if ( isset( $col['foreign'] ) && $col['foreign'] ) {
                $sqls[] = 'ALTER TABLE `'.$tableName.'` ADD CONSTRAINT `FK_'.$tableName.'_'.$col['column'].'` '
                    .' FOREIGN KEY (`'.$col['column'].'`) REFERENCES `'.$col['foreign_table'].'` (`'.$col['foreign_id'].'`); ';
                $sqls[] = 'commit;';
            }
            
        }
        
        $sqls[] = 'commit;';
        
        foreach ( $indexes as $key => $index ) {
        
            $sql = 'CREATE '.$index['type'].' INDEX '.$tableName.'_idx'.($key+1)
                .' ON '.$tableName
                .' ('.implode(', ', $index['columns']).');';
            
            $sqls[] = $sql;
        }
        
    
        return $sqls;
    /*
     insert uuid to binary 16: 
      UNHEX(REPLACE(UUID(),'-','')
     * 
     * 
     *    
    SHOW FUNCTION STATUS ; -- można sprawdzić czy już nie dodane

    CREATE FUNCTION UuidToBin(_uuid BINARY(36))
        RETURNS BINARY(16)
        LANGUAGE SQL  DETERMINISTIC  CONTAINS SQL  SQL SECURITY INVOKER
    RETURN
        UNHEX(CONCAT(
            SUBSTR(_uuid, 15, 4),
            SUBSTR(_uuid, 10, 4),
            SUBSTR(_uuid,  1, 8),
            SUBSTR(_uuid, 20, 4),
            SUBSTR(_uuid, 25) ));

    CREATE FUNCTION UuidFromBin(_bin BINARY(16))
        RETURNS BINARY(36)
        LANGUAGE SQL  DETERMINISTIC  CONTAINS SQL  SQL SECURITY INVOKER
    RETURN
        LCASE(CONCAT_WS('-',
            HEX(SUBSTR(_bin,  5, 4)),
            HEX(SUBSTR(_bin,  3, 2)),
            HEX(SUBSTR(_bin,  1, 2)),
            HEX(SUBSTR(_bin,  9, 2)),
            HEX(SUBSTR(_bin, 11))
                 ));
     */    
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

