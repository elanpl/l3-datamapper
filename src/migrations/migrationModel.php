<?php
namespace elanpl\DM\migrations;
use \elanpl\DM\DataMapper;

class MigrationModel extends DataMapper {
    
    const tableName = 'migrations';

    protected $columns = [
        'id' => ['primaryKey' => true ],
        'file' => [],
        'timestamp' => [],
        'step' => []
        ];
    
}

