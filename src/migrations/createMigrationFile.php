<?php
namespace migrations;

use elanpl\DM\migrations\Table;

class CREATE_MIGRATION_FILE {
    public function up() {
        
        $table = Table::create('CREATE_MIGRATION_FILE');
        
        $table->increments('id');
        
        return $table->run();
    }
    
    public function down() {
        $table = Table::drop('CREATE_MIGRATION_FILE');
    }
}

