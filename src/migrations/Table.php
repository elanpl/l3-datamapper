<?php
namespace elanpl\DM\migrations;

use elanpl\DM\DB\Connection;

class Table {
    
    private $type = null;
    public $tableName = '';
    private $columns = [];
    private $indexes = [];
    public $connection = null;
    private $dbName = '';
    
    
    function __construct($addType, $tableName, $dbName = '') {
        $this->type = $addType;
        $this->tableName = $tableName;
        $this->dbName = $dbName;
        $this->connection = Connection::getInstance();
        $this->connection->setDatabase($this->dbName);
    }
    
    function getColumns() {
        return $this->columns;
    }
    
    function getIndexes() {
        return $this->indexes;
    }
    
    static function create($tableName, $dbName = '') {
        return new Table('create', $tableName);
    }
    
    static function update($tableName, $dbName = '') {
        return new Table('update', $tableName);
    }
    
    static function drop($tableName, $dbName = '') {
        $table = new Table('drop', $tableName);
        $table->dropTable();
    }
    
    function increments($columnName) {   
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'int',
            'autoincrement' => true,
            'primaryKey' => true,
            'null' => false,
        ];
        
        return $this;
    }
    
    function bigIncrements($columnName) {  
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'bigInt',
            'autoincrement' => true,
            'primaryKey' => true,
            'null' => false,
        ];
        
        return $this;
    }
    
    function bigInteger($columnName) {  
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'bigInt',
            'null' => false,
        ];
        
        return $this;
    }
    
    function binary($columnName) {     
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'binary',
            'null' => false,
        ];
        
        return $this;
    }
    
    function boolean($columnName) { 
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'boolean',
            'null' => false,
        ];
        
        return $this;
    }
    
    function char($columnName) {   
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'char',
            'null' => false,
        ];
        
        return $this;
    }
    
    function date($columnName) {   
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'date',
            'null' => false,
        ];
        
        return $this;
    }
    
    function dateTime($columnName) {   
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'dateTime',
            'null' => false,
        ];
        
        return $this;
    }
    
    function decimal($columnName) {  
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'decimal',
            'null' => false,
        ];
        
        return $this;
    }
    
    function double($columnName) {        
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'double',
            'null' => false,
        ];
        
        return $this;        
    }
    
    function integer($columnName) {  
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'int',
            'null' => false,
        ];
        
        return $this;
    }
    
    function mediumText($columnName) {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'mediumText',
            'null' => false,
        ];
        return $this;
    }
    function softDeletes($columnName) {     
        $this->columns[] = [
            'column' => 'removeTime',
            'type' => 'date',
            'null' => false,
        ];
        return $this;  
    }
    
    //TODO: uzupełnić puste funkcje
    function softDeletesTz($columnName) {   
        
    }
    
    function text($columnName) {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'text',
            'null' => false,
        ];
        return $this;    
    }
    
    function time($columnName) {  
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'time',
            'null' => false,
        ];
        return $this; 
    }
    
    function timeTz($columnName) {    
        
    }
    function timestamp($columnName) {   
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'timestamp',
            'null' => false,
        ];
        return $this; 
    }
    function timestampTz($columnName) {   
        
    }
    
    function uuid($columnName) {   
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'uuid',
            'null' => false,
        ];
        return $this; 
    }
    
    function longText($columnName) {  
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'longtext',
            'null' => false,
        ];
        return $this; 
    }
    
    function string($columnName, $lenght = 255) {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'string',
            'lenght' => $lenght,
            'null' => false,
        ];
        return $this; 
    }
    
    function timestamps() {
        $this->columns[] = [
            'column' => 'createTime',
            'type' => 'timestamp',
            'null' => false,
            'default' => 'current'
        ];
        
        $this->columns[] = [
            'column' => 'updateTime',
            'type' => 'timestamp',
            'null' => true,
        ];
        
        return $this;  
    }
    
    function timestampsTz() {
        
    }
    
    /* dodatki */
    function nullable () {
        end($this->columns);  
        $key = key($this->columns);
        
        $this->columns[$key]['null'] = true;
        return $this; 
    }
    
    
    function defaultValue($value) {
        end($this->columns);  
        $key = key($this->columns);
        
        $this->columns[$key]['default'] = $value; 
        return $this; 
    }
    
    function unique() {
        end($this->columns);  
        $key = key($this->columns);
        
        $this->columns[$key]['unique'] = true;  
        return $this; 
    }
    

    function change() {
        end($this->columns);  
        $key = key($this->columns);
        
        $this->columns[$key]['changeColumn'] = true;  
        return $this; 
    }    
        
    function first() {
    }    
    
    function renameColumn($columnName, $newColumnName) {
        $this->columns[] = [
            'column' => $columnName,
            'newName' => $newColumnName,
            'renameColumn' => true,
        ];
    }
    
    function dropColumn($columnName) {
        $this->columns[] = [
            'column' => $columnName,
            'dropColumn' => true,
        ];
    }
        
            
    function index($type, $columns) {
        $this->indexes[] = [
            'type' => $type,
            'columns' => $columns
        ];
        
        return $this;
    }
    
            
            
    function dropPrimary() {
        
    }
    function dropUnique() {
        
    }
    function dropIndex() {
        
    }
    
    function after($columnName) {
        end($this->columns);  
        $key = key($this->columns);
        
        $this->columns[$key]['after'] = $columnName;  
        return $this; 
    }
    
    function foreign($table, $columnName){
        end($this->columns);  
        $key = key($this->columns);
        
        $this->columns[$key]['foreign'] = true;   
        $this->columns[$key]['foreign_table'] = $table;  
        $this->columns[$key]['foreign_id'] = $columnName;  
        return $this; 
    }
    
    function onDelete($option){
        
    }
    function dropForeign(){
        
    }
    
    function run($showTableExistInfo = true){
        
        $result = false;
        
        
        if ( $this->type == 'create' ) {
            if ( ! $this->connection->tableExists($this->tableName) ) {
                $sql = $this->connection->getCreateSQL( $this );
                $result = $this->connection->queryArray( $sql );
                if ( $result )
                    echo " - Table ".$this->tableName." added.\n";
                else
                    echo " - Error while adding table ".$this->tableName.".\n"
                        .implode("\n",$sql)."\n";

            } else {
                if ($showTableExistInfo)
                    echo ' - Table '.$this->tableName.' already exists.'."\n";
            }
        } else if ( $this->type == 'update') {
            if ( $this->connection->tableExists($this->tableName) ) {
                $sql = $this->connection->getUpdateSQL( $this );
                $result = $this->connection->queryArray( $sql );
                if ( $result )
                    echo " - Table ".$this->tableName." updated.\n";
                else
                    echo " - Error while updating table ".$this->tableName.".\n"
                        .implode("\n",$sql)."\n";
            } else {
                if ($showTableExistInfo)
                    echo ' - Table '.$this->tableName.' not exists.'."\n";
            }
        } else if ( $this->type == 'drop') {
            if ( $this->connection->tableExists($this->tableName) ) {
                $sql = $this->connection->getDropSQL( $this );
                $result = $this->connection->queryArray( $sql );
                if ( $result )
                    echo " - Table ".$this->tableName." removed.\n";
                else
                    echo " - Error while removing table ".$this->tableName.".\n"
                        .implode("\n",$sql)."\n";
            } else {
                if ($showTableExistInfo)
                    echo ' - Table '.$this->tableName.' not exists.'."\n";
            }
        }
        
        return $result;
    }
    
    function dropTable($showTableExistInfo = true){
        if ( $this->connection->tableExists($this->tableName) ) {
            if ( $this->connection->query( 'drop table ?', [$this->tableName] ) )
                echo " - Table ".$this->tableName." droped.\n";
            else
                echo " - Error while droping table ".$this->tableName.".\n";
        } else {
            if ($showTableExistInfo)
                echo ' - Table '.$this->tableName.' not exists.'."\n";
        }
    }
    
}