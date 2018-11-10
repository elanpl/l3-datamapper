<?php
namespace elanpl\DM\DB;

abstract class QueryBuilder
{
    const DefaultLimit = 100;
    const DefaultBacktick = '`';
    protected $backTick = self::DefaultBacktick;
    protected $tablePrefix = '';
    
    protected $isDistinct = false;
    protected $select = [];
    protected $set = [];
    protected $order_by = [];
    protected $group_by = [];
    protected $from = [];
    protected $join = [];
    protected $limit = null;
    protected $offset = null;
    protected $where = [];
    protected $query = '';
    protected $binds = [];
    
    // Force Extending class to define this method
    abstract protected function escapeString($string);
    abstract protected function tableExists($tableName);
    abstract protected function queryArray( $sqlArray );
    abstract protected function lastQuery();
    abstract protected function insertId();
    abstract protected function query( $sql, $binds = [] );
    abstract protected function row( );
    abstract protected function numRows( );
    abstract protected function fieldData($tableName);
    
    
    public function resetQuery() {
        $this->isDistinct = false;
        $this->select = [];
        $this->order_by = [];
        $this->group_by = [];
        $this->from = [];
        $this->limit = null;
        $this->offset = null;
        $this->where = [];
        $this->set = [];
        $this->join = [];
        $this->backTick = self::DefaultBacktick;
    }
    public function resetSelect() {
        $this->select = [];
    }
    public function resetOrderBy() {
        $this->order_by = [];
    }
    public function resetWhere() {
        $this->where = [];
    }
    public function isEmptyWhere() {
        return empty($this->where);
    }
    public function isEmptySelect() {
        return empty($this->select);
    }
    public function isEmptyOrderBy() {
        return empty($this->order_by);
    }
    
    public function hasSelect($val) {
        foreach ( $this->select as $sel ) {
            if ($sel['field'] == $val)
                return true;
        }
        return false;    
    }
    
    public function groupWhere() {
        array_unshift($this->where, '( ');
	$this->where[] = ' )';
        
        return true;
    }
    
    public function isDistinct() {
        return $this->isDistinct;
    }
    
    public function setTablePrefix($tablePrefix) {
        $this->tablePrefix = $tablePrefix;
    }
    
    public function truncate($tableName) {
        return $this->query( 'TRUNCATE ? ', [$tableName] );
    }
    
    public function where( $column, $value, $escape_values = false ) {
            
        $column = explode(' ', $column);
        if (!isset($column[1]) && $value === null)
            $column[1] = 'is';
        elseif (!isset($column[1]))
            $column[1] = '=';
        $column = implode(' ', $column);
        $this->where[] = [ 'type' => 'value', 'column' => $column, 'value' => $value, 'escape' => $escape_values ];
    }
    
    public function whereOr() {
        $this->where[] = [ 'type' => 'or' ];
    }
    
    public function whereStartGroup() {
        $this->where[] = [ 'type' => '(' ];
    }
    
    public function whereEndGroup() {
        $this->where[] = [ 'type' => ')' ];
    }
    
    
    public function whereRaw( $expresion ) {
        $this->where[] = [ 'type' => 'raw', 'expresion' => $expresion];
    }
    
    
    public function whereIn( $column, $values, $escape_values = false ) {
        $this->where[] = [ 'type' => 'in', 'column' => $column, 'value' => $values, 'escape' => $escape_values ];
    }
    
    public function whereNotIn( $column, $values, $escape_values = false ) {
        $this->where[] = [ 'type' => 'not_in', 'column' => $column, 'value' => $values, 'escape' => $escape_values ];
    }
    
    public function groupBy($column) {
        $this->group_by[] = ['column' => $column];
    }
    
    public function set( $column, $value, $escape_values = false ) {
        $this->set[] = [ 'type' => 'value', 'column' => $column, 'value' => $value, 'escape' => $escape_values ];
    }
    
    public function from($tableName, $alias = null) {
        $this->from[] = [ 'table' => $tableName, 'alias' => $alias ];
    }
    
    public function select($field, $escape = false) {
        if(\is_array($field)){
            foreach($field as $f)
                $this->select[] = [ 'field' => $f, 'escape' => $escape ];
        }
        else
            $this->select[] = [ 'field' => $field, 'escape' => $escape ];
    }
    
    public function distinct($field, $escape = false) {
        $this->isDistinct = true;
        $this->select[] = [ 'field' => $field, 'escape' => $escape, 'distinct' => true ];
    }
    
    public function selectCount($field, $escape = false) {
        $field_name = explode('.',$field);
        $field_name = end($field_name);
        if ( $field_name == '')
            $field_name = '*';
        $this->select[] = [ 'field' => $field_name, 'escape' => $escape, 'count' => true, 'alias' => 'numrows' ];
    }
    
    public function selectMax($field, $escape = false) {
        $field_name = explode('.',$field);
        $field_name = end($field_name);
        
        $this->select[] = [ 'field' => $field, 'escape' => $escape, 'max' => true, 'alias' => 'max_'.$field_name ];
    }
    
    public function selectMin($field, $escape = false) {
        $field_name = explode('.',$field);
        $field_name = end($field_name);
        $this->select[] = [ 'field' => $field, 'escape' => $escape, 'min' => true, 'alias' => 'min_'.$field_name ];
    }
    
    public function selectAvg($field, $escape = false) {
        $field_name = explode('.',$field);
        $field_name = end($field_name);
        $this->select[] = [ 'field' => $field, 'escape' => $escape, 'avg' => true, 'alias' => 'avg_'.$field_name ];
    }
    
    public function selectSum($field, $escape = false) {
        $field_name = explode('.',$field);
        $field_name = end($field_name);
        $this->select[] = [ 'field' => $field, 'escape' => $escape, 'sum' => true, 'alias' => 'sum_'.$field_name ];
    }
    
    public function get( $tableName = null, $limit = null, $offset = null ) {
        
        if ($tableName) 
            $this->from($tableName);
        if ( $limit )
            $this->limit = $limit;
        if ( $offset )
            $this->offset = $offset;
        
        $this->binds = [];
        $this->query = [];
        $this->query[] = $this->getSelect( );
        $this->query[] = $this->getFrom( );
        $this->query[] = $this->getWhere( );
        $this->query[] = $this->getGroupBy();
        $this->query[] = $this->getHaving();
        
        $this->query[] = $this->getOrderBy();
        $this->query[] = $this->getOffsetLimit();
        
        $this->resetQuery();
        
        return $this->query( implode(' ',$this->query), $this->binds );
    }
    
    public function insert($tableName, $data ) {
        
        $columns = [];
        $values = [];
        $binds = [];
        foreach ($data as $column => $value) {
            $columns[] = $column;
            $binds[] = $value;
            $values[] = "?";
        }
        
        $this->query = [];
        $this->query[] = 'insert into '.$tableName.' (' ;
        $this->query[] = implode(', ',$columns);
        $this->query[] = ') values (';
        $this->query[] = implode(', ',$values);
        $this->query[] = ')';
        
        $this->resetQuery();
        
        $this->query( implode(' ',$this->query), $binds );
                
        return $this->insertId();
    }
    
    public function update($tableName, $data = []) {
        foreach ( $data as $d) {
            $this->set($d);
        }
        $this->binds = [];
        
        $this->query = [];
        $this->query[] = 'update '.$tableName.' set ' ;
        $this->query[] = $this->getSet();
        $this->query[] = $this->getWhere();
        
        $this->resetQuery();
        
        $this->query( implode(' ',$this->query), $this->binds );
                
        return $this->numRows() > 0 ? true : false;
    }
    
    public function delete($tableName, $where = []) {
        
        $this->binds = [];
        
        foreach ( $where as $f => $w) {
            $this->where($f, $w);
        }
        
        $this->binds = [];
        $sql = 'delete from '.$tableName.' '
                .$this->getWhere();
        
        $this->resetQuery();
        
        return $this->query( $sql, $this->binds );
    }
    
    public function limit($value, $offset) {
        $this->limit = (int)$value;
        $this->offset = (int)$offset;
    }
    
    public function offset($offset) {
        $this->offset = (int)$offset;
    }
    
    public function orderBy($orderby, $direction) {
        $this->order_by[] = [ 'column' => $orderby, 'direction' => $direction ];
    }
    
    public function join($tableName, $on, $joinType = '', $alias = '') {
        $this->join[] = [ 'tableName' => $tableName, 'on' => $on, 'joinType' => $joinType.' JOIN', 'alias' => $alias ];
    }
    
    public function protectIdentifiers($value) {
        return $this->backTick.$value.$this->backTick;
    }
    
    
    
    /* ---- protected ---------------------- */
    protected function getFrom() {
        if ( empty($this->from) )
            return 'Unknown table';
        $from = '';
        foreach ( $this->from as $table ) {
            $from  = 'from '.$this->tablePrefix.$table['table']
                        .( $table['alias'] ? ' as '.$table['alias'] : '');
        }
        foreach ($this->join as $join) {
            $from  .= ' '.$join['joinType'].' '
                    .$this->tablePrefix.$join['tableName'].' on '.$join['on'];
        }
        
        return $from;
    }
    
    protected function getSelect() {
        
        if ( empty($this->select))
            return 'select *'; 
        else {
            $select = [];
            foreach ( $this->select as $s ) {
                $prefix = '';
                $sufix = '';
                if ( isset($s['distinct']) && $s['distinct']) {
                    $prefix = 'distinct '; $sufix = '';
                }
                if ( isset($s['count']) && $s['count']) { 
                    $prefix = 'count( ';  $sufix = ' ) as '.$s['alias'];
                }
                if ( isset($s['min']) && $s['min']) {
                    $prefix = 'min( ';  $sufix = ' ) as '.$s['alias'];
                }
                if ( isset($s['max']) && $s['max']) { 
                    $prefix = 'max( ';  $sufix = ' ) as '.$s['alias'];
                }
                if ( isset($s['avg']) && $s['avg']) {
                    $prefix = 'avg( '; $sufix = ' ) as '.$s['alias'];
                }
                if ( isset($s['sum']) && $s['sum']) {
                    $prefix = 'sum( '; $sufix = ' ) as '.$s['alias'];
                }
                
                if ($s['escape'])
                    $select[] = $prefix.trim($this->escapeString($s['field']),"'").$sufix;
        
                else
                    $select[] = $prefix.$s['field'].$sufix;
            }    
            return 'select '.implode(', ',$select);
        }
    }
    
    protected function getWhere() {
            
        if ( empty($this->where))
            return '';
        else {
            
//            echo '<pre>';
//            print_r($this->where);
//            echo '</pre>';
            
            $add_end = false;
            $where = [];
            foreach ($this->where as $w) {
                if ($w['type'] == 'raw') {
                    $where[] = ' '.$w['expresion'].' ';
                } else {
                    if ($w['type'] == 'or') {
                        $where[] = " or ";
                        $add_end = false;
                    } elseif ($w['type'] == '(') {
                        if ($add_end) 
                            $where[] = " and ";
                        $where[] = " ( ";
                        $add_end = false;
                    } elseif ($w['type'] == ')') {
                        $where[] = " ) ";    
                    } else {
                        if ($add_end) 
                            $where[] = " and ";
                        else 
                            $add_end = true;
                        
                        
                        if ( $w['escape'] ) { 
                            if (is_array($w['value']) ) {
                                foreach ( $w['value'] as &$v ) 
                                    $v = $this->escapeString($v);
                            } else
                                $w['escape'] = $this->escapeString($w['value']);
                        }

                        if ($w['type'] == 'in' || $w['type'] == 'not_in' ) {
                            $_where =  $w['column'].( $w['type'] == 'in' ? ' in ' : ' not in ').'( ';
                            $comma = '';
                            foreach ( $w['value'] as $val )  {
                               $_where .= $comma.' ?';
                               $comma = ',';
                               $this->binds[] = str_replace("'", "", $val);
                            } 
                            $_where.= ')';
                            $where[ ] = $_where;
                        } elseif ( $w['value'] === null )
                            $where[] = $w['column']." null ";
                        else {
                            $where[] = $w['column']." ? ";
                            $this->binds[] = $w['value'];
                        }
                    }
                }
            }
            return 'where '.implode('', $where );    
        }    
    }
    
    protected function getSet() {
            
        if ( empty($this->set))
            return '';
        else {
            
            $set = [];
            foreach ($this->set as $s) {
                if ($s['type'] == 'raw') {
                    $set[] = $s['expresion'];
                } else {
                
                    if ( $s['escape'] ) { 
                        if (is_array($s['value']) ) {
                            foreach ( $s['value'] as &$v ) 
                                $v = $this->escapeString($v);
                        } else
                            $s['escape'] = $this->escapeString($s['value']);
                    }

                    if ( $s['value'] === null )
                        $set[] = $s['column']." = null ";
                    else {
                        $set[] = $s['column']." = ?";
                        $this->binds[] = $s['value'];
                    }
                }
            }
            return implode(', ', $set );    
        }    
    }
        
    
    protected function getOffsetLimit() {
        if ( $this->offset != null || $this->limit != null ) {
            if ( $this->offset == null )
                $this->offset = 0;
            if ( $this->limit == null )
                $this->limit = self::DefaultLimit;
            return 'limit '.(int)$this->offset.', '.(int)$this->limit;
        } else
            return '';
    }
    
    protected function getOrderBy(){
        
        if (empty($this->order_by)) {
            return '';
        } else {
            $order = 'order by ';
            $sep = '';
            foreach ($this->order_by as $ob ) {
                $order .= $sep.$ob['column'].' '.$ob['direction'];
                $sep = ', ';
            }
            return $order;
        }
    }
    
    protected function getGroupBy(){
        if (empty($this->group_by)) {
            return '';
        } else {
            $order = 'group by ';
            $sep = '';
            foreach ($this->group_by as $gb ) {
                $order .= $gb['column'].$sep;
                $sep = ', ';
            }
            return $order;
        }
    }
    
    protected function getHaving(){
        return '';
    }
}

