<?php
namespace elanpl\DM\DB;

if (!defined('CONNECTION_SHOW_SQL'))
    define('CONNECTION_SHOW_SQL',false);

class Connection 
{
    private static $instance;
    private $db_connections = [];
    private $current_connection = '';
    private $db_config = null;
    
    public static function getInstance($config_file = null) {        
        if (self::$instance === null) {
            self::$instance = new Connection(realpath($config_file));
        }
        return self::$instance;
    }
    
    
    private function __construct($config_file = null) {
        $this->getConfig($config_file);
        foreach ( $this->db_config as $name => $config) {
            if ( $config['driver'] == 'mysql' ) {
                $this->db_connections[$name] = isset($config['charset']) ? 
                    new MySqlConnection(
                        $config['host'], 
                        $config['user'], 
                        $config['password'], 
                        $config['dbname'],
                        $config['port'],
                        $config['charset']
                    ) 
                    : 
                    new MySqlConnection(
                        $config['host'], 
                        $config['user'], 
                        $config['password'], 
                        $config['dbname'],
                        $config['port']
                    );
                        
            } elseif ( $config['driver'] == 'mysqli' ) {
                $this->db_connections[$name] =  isset($config['charset']) ? 
                new MySqlConnectionOld(
                    $config['host'], 
                    $config['user'], 
                    $config['password'], 
                    $config['dbname'],
                    $config['port'],
                    $config['charset']
                ) 
                : 
                new MySqlConnectionOld(
                    $config['host'], 
                    $config['user'], 
                    $config['password'], 
                    $config['dbname'],
                    $config['port']
                );
                        
            } elseif ( $config['driver'] == 'postgresql' ) {
                $this->db_connections[$name] =  isset($config['charset']) ? 
                new PostgreSQLConnection(
                    $config['host'], 
                    $config['user'], 
                    $config['password'], 
                    $config['dbname'],
                    $config['port'],
                    $config['charset']
                ) 
                : 
                new PostgreSQLConnection(
                    $config['host'], 
                    $config['user'], 
                    $config['password'], 
                    $config['dbname'],
                    $config['port']
                );
            }  elseif ( $config['driver'] == 'firebird' ) {
                $this->db_connections[$name] =  isset($config['charset']) ? 
                new FirebirdSQLConnection(
                    $config['host'], 
                    $config['user'], 
                    $config['password'], 
                    $config['dbname'],
                    $config['port'],
                    $config['charset']
                ) 
                : 
                new FirebirdSQLConnection(
                    $config['host'], 
                    $config['user'], 
                    $config['password'], 
                    $config['dbname'],
                    $config['port']
                );
            }     
        }
        
        reset($this->db_config);
        $this->current_connection = key($this->db_config);
        //$this->current_connection = key( $this->db_config );
        foreach ($this->db_config as $k => $v ) {
            $this->current_connection = $k;
            break;
        }
    }
    
    public function getCreateSQL( $table ) {
        if ( $this->db_config[$this->current_connection]['driver'] == 'mysql' ) {
            return MySqlSchema::createTableSql( $table );
        } elseif ( $this->db_config[$this->current_connection]['driver'] == 'postgresql' ) {
            return PostgreSQLSchema::createTableSql( $table );
        } elseif ( $this->db_config[$this->current_connection]['driver'] == 'firebird' ) {
            return FirebirdSQLSchema::createTableSql( $table );
        }
    }    
    
    public function getUpdateSQL( $table ) {
        if ( $this->db_config[$this->current_connection]['driver'] == 'mysql' ) {
            return MySqlSchema::updateTableSql( $table );
        } elseif ( $this->db_config[$this->current_connection]['driver'] == 'postgresql' ) {
            return PostgreSQLSchema::updateTableSql( $table );
        } elseif ( $this->db_config[$this->current_connection]['driver'] == 'firebird' ) {
            return FirebirdSQLSchema::updateTableSql( $table );
        }
    }
    
    public function getDropSQL( $table ) {
        if ( $this->db_config[$this->current_connection]['driver'] == 'mysql' ) {
            return MySqlSchema::dropTableSql( $table );
        } elseif ( $this->db_config[$this->current_connection]['driver'] == 'postgresql' ) {
            return PostgreSQLSchema::dropTableSql( $table );
        } elseif ( $this->db_config[$this->current_connection]['driver'] == 'firebird' ) {
            return FirebirdSQLSchema::dropTableSql( $table );
        }
    }
    
    public function __clone() {
        //TODO: AG: dodane tylko po to żeby datamapper się nie sypał, do posprzątania później 
        if (self::$instance === null) {
            self::$instance = new Connection();
        }
        return self::$instance;
    }
    
    
    private function getConfig($config_file  = null) {
        $this->db_config = require $config_file;
    }
    
    public function setDatabase($database = '') {
        if ( $database == '') {
            reset($this->db_config);
            $this->current_connection = key($this->db_config);
        } elseif ( isset($this->db_connections[$database] ))
            $this->current_connection = $database;
        else 
            die( 'Unknown database connection: '.$database);
        
    }
    
    public function queryArray($sqlArray) {
        return $this->db_connections[$this->current_connection]->queryArray( $sqlArray ); 
    }

    
    public function query($sql, $binds = []) {
        return $this->db_connections[$this->current_connection]->query( $sql, $binds ); 
    }
    
    public function row() {
        return $this->db_connections[$this->current_connection]->row( ); 
    }
    
    public function result() {
        return $this->db_connections[$this->current_connection]->result( ); 
    }
    
    public function num_rows() {
        return $this->db_connections[$this->current_connection]->numRows();
    }
    
    public function tableExists($tableName) {
        return $this->db_connections[$this->current_connection]->tableExists( $tableName ); 
    }
    
    
    /* nowe -- datamapper -------------------------- */
   
    public function resetQuery() {
        return $this->db_connections[$this->current_connection]->resetQuery();
    }
    public function resetSelect() {
        return $this->db_connections[$this->current_connection]->resetSelect();
    }
    public function resetOrderBy() {
        return $this->db_connections[$this->current_connection]->resetOrderBy();
    }
    public function resetWhere() {
        return $this->db_connections[$this->current_connection]->resetWhere();
    }
    public function isEmptyWhere() {
        return $this->db_connections[$this->current_connection]->isEmptyWhere();
    }
    public function isEmptySelect() {
        return $this->db_connections[$this->current_connection]->isEmptySelect();
    }
    public function hasSelect($val) {
        return $this->db_connections[$this->current_connection]->hasSelect($val);
    }
    public function isEmptyOrderBy() {
        return $this->db_connections[$this->current_connection]->isEmptyOrderBy();
    }
    
    public function groupWhere() {
        return $this->db_connections[$this->current_connection]->groupWhere();
    }
    public function isDistinct() {
        return $this->db_connections[$this->current_connection]->isDistinct();
    }
    
    public function setTablePrefix($tablePrefix) {
        return $this->db_connections[$this->current_connection]->setTablePrefix($tablePrefix);
    }
    
    public function truncate( $tableName ) {
        return $this->db_connections[$this->current_connection]->truncate($tableName );   
    }
    
    public function escape( $string ) {
        return $this->db_connections[$this->current_connection]->escapeString($string );   
    }
    
    
    public function get( $tableName = null, $limit = null, $offset = null ) {
        $this->db_connections[$this->current_connection]->get( $tableName, $limit, $offset );   
        return $this;
    }
    
    
    public function from( $tableName, $alias = null) {
        $this->db_connections[$this->current_connection]->from( $tableName, $alias );   
        return $this;
    }
    
    public function check_last_query() {
        echo $this->db_connections[$this->current_connection]->lastQuery();
    }
    
    public function last_query() {
        return $this->db_connections[$this->current_connection]->lastQuery();
    }
    
    public function free_result() {
        return $this->db_connections[$this->current_connection]->freeResult();
    }
    
    public function whereRaw($expresion) {
        $this->db_connections[$this->current_connection]->whereRaw($expresion);
        return $this;
    }
    
    public function where($column, $value, $escape_values = true) {
        $this->db_connections[$this->current_connection]->where($column, $value, $escape_values);
        return $this;
    }
    
    public function whereOr() {
        $this->db_connections[$this->current_connection]->whereOr();
        return $this;
    }
    
    public function whereStartGroup() {
        $this->db_connections[$this->current_connection]->whereStartGroup();
        return $this;
    }
    
    public function whereEndGroup() {
        $this->db_connections[$this->current_connection]->whereEndGroup();
        return $this;
    }
    
    
    public function where_in($column, $value) {
        $this->db_connections[$this->current_connection]->whereIn($column, $value);
        return $this;
    }
    
    public function where_not_int($column, $value) {
        $this->db_connections[$this->current_connection]->whereNotIn($column, $value);
        return $this;
    }
    
    
    public function insert($tableName, $data) {
        return $this->db_connections[$this->current_connection]->insert($tableName, $data);
    }
    
    public function insert_id() {
        return $this->db_connections[$this->current_connection]->insertId();
    }
    
    public function set($column, $value) {
        return $this->db_connections[$this->current_connection]->set($column, $value);
    }
    
    public function update($tableName, $data = []) {
        
        return $this->db_connections[$this->current_connection]->update($tableName, $data);
    }
 
    public function delete($tableName, $where = []) {
        return $this->db_connections[$this->current_connection]->delete($tableName, $where);
    }
    
    public function selectRaw($field) {
        return $this->db_connections[$this->current_connection]->select($field, false);
    }
    
    public function select($field, $escape = true) {
        return $this->db_connections[$this->current_connection]->select($field, $escape);
    }
    
    public function distinct($field, $escape = true) {
        return $this->db_connections[$this->current_connection]->distinct($field, $escape);
    }
    
    public function select_count($field, $escape = true) {
        return $this->db_connections[$this->current_connection]->selectCount($field, $escape);
    }
    
    
    public function select_max($field, $escape = true) {
        return $this->db_connections[$this->current_connection]->selectMax($field, $escape);
    }
    
    public function select_min($field, $escape = true) {
        return $this->db_connections[$this->current_connection]->selectMin($field, $escape);
    }
    
    public function select_avg($field, $escape = true) {
        return $this->db_connections[$this->current_connection]->selectAvg($field, $escape);
    }
    
    public function select_sum($field, $escape = true) {
        return $this->db_connections[$this->current_connection]->selectSum($field, $escape);
    }
    
    public function limit($value, $offset) {
        return $this->db_connections[$this->current_connection]->limit($value, $offset);
    }
    
    public function offset($offset) {
        return $this->db_connections[$this->current_connection]->offset($offset);
    }
    
    public function order_by($orderby, $direction) {
        return $this->db_connections[$this->current_connection]->orderBy($orderby, $direction);
    }
   
    public function protect_identifiers($value) {
        return $this->db_connections[$this->current_connection]->protectIdentifiers($value);
    }
    
    public function field_data($tableName) {
        return $this->db_connections[$this->current_connection]->fieldData($tableName);
    }
    
    public function group_by($field) {
        return $this->db_connections[$this->current_connection]->groupBy($field);
    }
    
    public function join($tableName, $on, $joinType = '', $alias = '') {
        return $this->db_connections[$this->current_connection]->join($tableName, $on, $joinType, $alias);
    }
    
    
    public function trans_begin($test_mode = false) {
        //TODO: zrobić transakcje
    }
    
    public function trans_strict($mode = true) {
        //TODO: zrobić transakcje
    }
    
    public function trans_start($test_mode = false) {
        //TODO: zrobić transakcje
    }
    
    public function trans_status() {
        //TODO: zrobić transakcje
    }
    
    public function trans_commit() {
        //TODO: zrobić transakcje
    }
    
    public function trans_rollback() {
        //TODO: zrobić transakcje
    }
    
    public function trans_complete() {
        //TODO: zrobić transakcje
    }
    
    
    
    
    /* ----------- śmieci do zrobienia ---------------------*/
    // funkcje co nie zadziałają:
    //public function count($exclude_ids = NULL, $column = NULL, $related_id = NULL)
    //public function get_sql($limit = NULL, $offset = NULL, $handle_related = FALSE)
    //protected function _parse_subquery_object($sql)
    //protected function _subquery($query, $args)
    //protected function _related_subquery($query, $args)
    //protected function _parse_subquery_object($sql)
    //protected function _handle_default_order_by()
    //protected function _having($key, $value = '', $type = 'AND ', $escape = TRUE)
    //
    // db->dm_call_method('_track_aliases', $this->table);  - szuka jaki alias ma tabela
    // $sql = $this->db->dm_call_method('_compile_select'); - zwraca sql do wykonania
    // $tablename = $this->db->dm_call_method('_escape_identifiers', $this->table);
   
  
}
