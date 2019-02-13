<?php

namespace elanpl\DM\migrations;

use elanpl\DM\DB\Connection;

class Table
{
    private $type = null;
    public $tableName = '';
    private $columns = [];
    private $indexes = [];
    public $connection = null;
    private $dbName = '';

    public function __construct($addType, $tableName, $dbName = '')
    {
        $this->type = $addType;
        $this->tableName = $tableName;
        $this->dbName = $dbName;
        $this->connection = Connection::getInstance();
        $this->connection->setDatabase($this->dbName);
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getIndexes()
    {
        return $this->indexes;
    }

    public static function create($tableName, $dbName = '')
    {
        return new Table('create', $tableName);
    }

    public static function update($tableName, $dbName = '')
    {
        return new Table('update', $tableName);
    }

    public static function drop($tableName, $dbName = '')
    {
        return new Table('drop', $tableName);
    }

    public function increments($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'int',
            'autoincrement' => true,
            'primaryKey' => true,
            'null' => false,
        ];

        return $this;
    }

    public function bigIncrements($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'bigInt',
            'autoincrement' => true,
            'primaryKey' => true,
            'null' => false,
        ];

        return $this;
    }

    public function bigInteger($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'bigInt',
            'null' => false,
        ];

        return $this;
    }

    public function binary($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'binary',
            'null' => false,
        ];

        return $this;
    }

    public function boolean($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'boolean',
            'null' => false,
        ];

        return $this;
    }

    public function char($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'char',
            'null' => false,
        ];

        return $this;
    }

    public function date($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'date',
            'null' => false,
        ];

        return $this;
    }

    public function dateTime($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'dateTime',
            'null' => false,
        ];

        return $this;
    }

    public function decimal($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'decimal',
            'null' => false,
        ];

        return $this;
    }

    public function double($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'double',
            'null' => false,
        ];

        return $this;
    }

    public function integer($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'int',
            'null' => false,
        ];

        return $this;
    }

    public function mediumText($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'mediumText',
            'null' => false,
        ];

        return $this;
    }

    public function softDeletes($columnName)
    {
        $this->columns[] = [
            'column' => 'removeTime',
            'type' => 'date',
            'null' => false,
        ];

        return $this;
    }

    //TODO: uzupełnić puste funkcje
    public function softDeletesTz($columnName)
    {
    }

    public function text($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'text',
            'null' => false,
        ];

        return $this;
    }

    public function time($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'time',
            'null' => false,
        ];

        return $this;
    }

    public function timeTz($columnName)
    {
    }

    public function timestamp($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'timestamp',
            'null' => false,
        ];

        return $this;
    }

    public function timestampTz($columnName)
    {
    }

    public function uuid($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'uuid',
            'null' => false,
        ];

        return $this;
    }

    public function longText($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'longtext',
            'null' => false,
        ];

        return $this;
    }

    public function string($columnName, $lenght = 255)
    {
        $this->columns[] = [
            'column' => $columnName,
            'type' => 'string',
            'lenght' => $lenght,
            'null' => false,
        ];

        return $this;
    }

    public function timestamps()
    {
        $this->columns[] = [
            'column' => 'createTime',
            'type' => 'timestamp',
            'null' => false,
            'default' => 'current',
        ];

        $this->columns[] = [
            'column' => 'updateTime',
            'type' => 'timestamp',
            'null' => true,
        ];

        return $this;
    }

    public function timestampsTz()
    {
    }

    /* dodatki */
    public function nullable()
    {
        end($this->columns);
        $key = key($this->columns);

        $this->columns[$key]['null'] = true;

        return $this;
    }

    public function defaultValue($value)
    {
        end($this->columns);
        $key = key($this->columns);

        $this->columns[$key]['default'] = $value;

        return $this;
    }

    public function unique()
    {
        end($this->columns);
        $key = key($this->columns);

        $this->columns[$key]['unique'] = true;

        return $this;
    }

    public function change()
    {
        end($this->columns);
        $key = key($this->columns);

        $this->columns[$key]['changeColumn'] = true;

        return $this;
    }

    public function first()
    {
    }

    // function renameColumn($columnName, $newColumnName) {
    //     $this->columns[] = [
    //         'column' => $columnName,
    //         'newName' => $newColumnName,
    //         'renameColumn' => true,
    //     ];
    // }

    public function renameColumn($newColumnName)
    {
        end($this->columns);
        $key = key($this->columns);

        $this->columns[$key]['newName'] = $newColumnName;
        $this->columns[$key]['renameColumn'] = true;

        return $this;
    }

    public function dropColumn($columnName)
    {
        $this->columns[] = [
            'column' => $columnName,
            'dropColumn' => true,
        ];
    }

    public function index($name, $type, $columns)
    {
        $this->indexes[] = [
            'name' => $name,
            'type' => $type,
            'columns' => $columns,
        ];

        return $this;
    }

    public function dropPrimary($columnName)
    {
        end($this->columns);
        $key = key($this->columns);

        $this->columns[$key]['drop_primary'] = $columnName;

        return $this;
    }

    public function dropUnique($columnName)
    {
        end($this->columns);
        $key = key($this->columns);

        $this->columns[$key]['drop_unique'] = $columnName;

        return $this;
    }

    public function dropIndex($name)
    {
        end($this->columns);
        $key = key($this->columns);

        $this->columns[$key]['drop_index'] = $name;

        return $this;
    }

    public function dropForeign($columnName)
    {
        end($this->columns);
        $key = key($this->columns);

        $this->columns[$key]['drop_foreign'] = $columnName;

        return $this;
    }

    public function after($columnName)
    {
        end($this->columns);
        $key = key($this->columns);

        $this->columns[$key]['after'] = $columnName;

        return $this;
    }

    public function foreign($table, $columnName)
    {
        end($this->columns);
        $key = key($this->columns);

        $this->columns[$key]['foreign'] = true;
        $this->columns[$key]['foreign_table'] = $table;
        $this->columns[$key]['foreign_id'] = $columnName;
        $this->columns[$key]['foreign_onUpdate'] = 'NoAction';
        $this->columns[$key]['foreign_onDelete'] = 'NoAction';

        return $this;
    }

    public function onDeleteSetNull()
    {
        end($this->columns);
        $key = key($this->columns);
        $this->columns[$key]['foreign_onDelete'] = 'SetNull';

        return $this;
    }

    public function onDeleteSetDefault()
    {
        end($this->columns);
        $key = key($this->columns);
        $this->columns[$key]['foreign_onDelete'] = 'SetDefault';

        return $this;
    }

    public function onDeleteCascade()
    {
        end($this->columns);
        $key = key($this->columns);
        $this->columns[$key]['foreign_onDelete'] = 'Cascade';

        return $this;
    }

    public function onDeleteRestrict()
    {
        end($this->columns);
        $key = key($this->columns);
        $this->columns[$key]['foreign_onDelete'] = 'Restrict';

        return $this;
    }

    public function onDeleteNoAction()
    {
        end($this->columns);
        $key = key($this->columns);
        $this->columns[$key]['foreign_onDelete'] = 'NoAction';

        return $this;
    }

    public function onUpdateSetNull()
    {
        end($this->columns);
        $key = key($this->columns);
        $this->columns[$key]['foreign_onUpdate'] = 'SetNull';

        return $this;
    }

    public function onUpdateCascade()
    {
        end($this->columns);
        $key = key($this->columns);
        $this->columns[$key]['foreign_onUpdate'] = 'Cascade';

        return $this;
    }

    public function onUpdateSetDefault()
    {
        end($this->columns);
        $key = key($this->columns);
        $this->columns[$key]['foreign_onUpdate'] = 'SetDefault';

        return $this;
    }

    public function onUpdateRestrict()
    {
        end($this->columns);
        $key = key($this->columns);
        $this->columns[$key]['foreign_onUpdate'] = 'Restrict';

        return $this;
    }

    public function onUpdateNoAction()
    {
        end($this->columns);
        $key = key($this->columns);
        $this->columns[$key]['foreign_onUpdate'] = 'NoAction';

        return $this;
    }

    public function run($showTableExistInfo = true)
    {
        $result = false;

        if ($this->type == 'create') {
            if (!$this->connection->tableExists($this->tableName)) {
                $sql = $this->connection->getCreateSQL($this);
                $result = $this->connection->queryArray($sql);
                if ($result) {
                    echo ' - Table '.$this->tableName." added.\n";
                } else {
                    echo ' - Error while adding table '.$this->tableName.".\n"
                        .implode("\n", $sql)."\n";
                }
            } else {
                if ($showTableExistInfo) {
                    echo ' - Table '.$this->tableName.' already exists.'."\n";
                }
            }
        } elseif ($this->type == 'update') {
            if ($this->connection->tableExists($this->tableName)) {
                $sql = $this->connection->getUpdateSQL($this);
                $result = $this->connection->queryArray($sql);
                if ($result) {
                    echo ' - Table '.$this->tableName." updated.\n";
                } else {
                    echo ' - Error while updating table '.$this->tableName.".\n"
                        .implode("\n", $sql)."\n";
                }
            } else {
                if ($showTableExistInfo) {
                    echo ' - Table '.$this->tableName.' not exists.'."\n";
                }
            }
        } elseif ($this->type == 'drop') {
            if ($this->connection->tableExists($this->tableName)) {
                $sql = $this->connection->getDropSQL($this);
                $result = $this->connection->queryArray($sql);
                if ($result) {
                    echo ' - Table '.$this->tableName." removed.\n";
                } else {
                    echo ' - Error while removing table '.$this->tableName.".\n"
                        .implode("\n", $sql)."\n";
                }
            } else {
                if ($showTableExistInfo) {
                    echo ' - Table '.$this->tableName.' not exists.'."\n";
                }
            }
        }

        return $result;
    }
}
