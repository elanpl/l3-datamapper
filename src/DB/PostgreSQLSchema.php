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

            if ( isset( $col['default'] ) && $col['default'] !== null )  {
                if ( $col['default'] === 'current' )
                    $sql .= ' DEFAULT CURRENT_TIMESTAMP  ';
                else
                    $sql .= ' DEFAULT '.$col['default'].' ';
            }

            if ( isset( $col['foreign'] ) && $col['foreign'] ) {
                $sql .= ' references '.$col['foreign_table'].' ('.$col['foreign_id'].') '
                        .self::prepareForeignOn($col).'; ';
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
        
            $sql = 'CREATE '.$index['type'].' INDEX '.$tableName.'_idx_'.$index['name']
                .' ON public.'.$tableName
                .' USING btree ('.implode(', ', $index['columns']).');';
            
            $sqls[] = $sql;
        }
        
        return $sqls;
    }
    
    static function prepareForeignOn($col) {
        $onDelete = 'NO ACTION';
        $onDelete = ($col['foreign_onDelete']  == 'SetNull') ? 'SET NULL' : $onDelete;
        $onDelete = ($col['foreign_onDelete']  == 'SetDefault') ? 'SET DEFAULT' : $onDelete;
        $onDelete = ($col['foreign_onDelete']  == 'Restrict') ? 'RESTRICT' : $onDelete;
        $onDelete = ($col['foreign_onDelete']  == 'Cascade') ? 'CASCADE' : $onDelete;
        $onUpdate = 'NO ACTION';
        $onUpdate = ($col['foreign_onUpdate']  == 'SetNull') ? 'SET NULL' : $onUpdate;
        $onUpdate = ($col['foreign_onUpdate']  == 'Restrict') ? 'RESTRICT' : $onUpdate;
        $onUpdate = ($col['foreign_onUpdate']  == 'Cascade') ? 'CASCADE' : $onUpdate;
        $onDelete = ($col['foreign_onUpdate']  == 'SetDefault') ? 'SET DEFAULT' : $onDelete;
        
        return ' ON DELETE '.$onDelete.' ON UPDATE '.$onUpdate;
    }
    
    static function updateTableSql($table) {
        
        $columns = $table->getColumns();
        $indexes = $table->getIndexes();
        $tableName = $table->tableName;
        
        foreach ($columns as $col) {
            if ( isset( $col['drop_foreign'] ) && $col['drop_foreign'] ) {
                $sqls[] = 'ALTER TABLE '.$tableName.' DROP CONSTRAINT FK_'.$tableName.'_'.$col['drop_foreign'].'; ';
                $sqls[] = 'commit;';
            }
            
            if ( isset( $col['drop_primary'] ) && $col['drop_primary'] ) {
                $sqls[] = 'ALTER TABLE '.$tableName.' DROP CONSTRAINT '.$tableName.'_pkey; ';
                $sqls[] = 'commit;';
            }
            
            if ( isset( $col['drop_unique'] ) && $col['drop_unique'] ) {
                $sqls[] = 'ALTER TABLE '.$tableName.' DROP INDEX UNIQUE_'.$tableName.'_'.$col['drop_unique'].'; ';
                $sqls[] = 'commit;';
            }
            
            if ( isset( $col['drop_index'] ) && $col['drop_index'] ) {
                $sqls[] = 'ALTER TABLE '.$tableName.' DROP INDEX '.$tableName.'_idx_'.$col['drop_index'].'; ';
                $sqls[] = 'commit;';
            }
        }
        
        $sql = ' ALTER TABLE '.$tableName.' ';
        $delimiter = '';
        foreach ($columns as $col) {
            if ( isset( $col['dropColumn'] ) && $col['dropColumn'] !== null ) {
                
                $sql .= $delimiter.' DROP IF EXISTS `'.$col['column'].'` ';
                
            } elseif ( isset( $col['renameColumn'] ) && $col['renameColumn'] !== null ) {
                
                $sql .= $delimiter.' RENAME `'.$col['column'].'` TO `'.$col['newName'].'` ';
                
            } elseif ( isset( $col['changeColumn'] ) && $col['changeColumn'] !== null ) {
                
                $sql .= $delimiter.' ALTER `'.$col['column'].'` TYPE  ';
                if ( isset( $col['autoincrement'] ) && $col['autoincrement'] )
                    $sql .= 'BIGSERIAL';
                else    
                    $sql .= self::getType($col['type']);
                if (isset( $col['null'] ) && $col['null'] == false )
                    $sql .= ' NOT NULL ';
                if ( isset( $col['default'] ) && $col['default'] !== null ) {
                    if ( $col['default'] == 'current')
                        $sql .= ' DEFAULT CURRENT_TIMESTAMP ';
                    else
                        $sql .= ' DEFAULT '.$col['default'].' ';
                }
                if ( isset( $col['foreign'] ) && $col['foreign'] ) {
                    $sql .= ' references '.$col['foreign_table'].' ('.$col['foreign_id'].') '
                            .self::prepareForeignOn($col).'; ';
                }
                
            } else {
                $sql .= $delimiter.' ADD `'.$col['column'].'` ';

                $sql .= self::getType($col['type']);

                if (isset( $col['null'] ) && $col['null'] === false )
                    $sql .= ' NOT NULL ';

                if ( isset( $col['default'] ) && $col['default'] !== false ) {
                    if ( $col['default'] == 'current')
                        $sql .= ' DEFAULT CURRENT_TIMESTAMP(1)';
                    else
                        $sql .= ' DEFAULT '.$col['default'].' ';
                }
                if ( isset( $col['foreign'] ) && $col['foreign'] ) {
                    $sql .= ' references '.$col['foreign_table'].' ('.$col['foreign_id'].') '
                            .self::prepareForeignOn($col).'; ';
                }
            }
            
            
            $delimiter = ', '."\n";
        }
        
        $sql .= '; ';
        
        $sqls[] = $sql;
        
        
        
        foreach ( $indexes as $key => $index ) {
        
            $sql = 'CREATE '.$index['type'].' INDEX '.$tableName.'_idx_'.$index['name']
                .' ON public.'.$tableName
                .' USING btree ('.implode(', ', $index['columns']).');';
            
            $sqls[] = $sql;
        }
        
        
        
        
        return $sqls;
    }
    
    static function dropTableSql($table) {
        $tableName = $table->tableName;
        
        $sql = ' DROP TABLE '.$tableName.';';
        
        $sqls[] = $sql;
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

