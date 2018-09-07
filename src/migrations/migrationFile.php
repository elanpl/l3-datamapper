<?php
namespace migrations;

use elanpl\DM\migrations\Table;

class MIGRATION_FILE {
    public function up() {
        
        $table = Table::create('MIGRATION_FILE');
        
        $table->increments('id');
        
        return $table->run();
    }
    
    public function down() {
        $table = Table::drop('MIGRATION_FILE');
    }
}

