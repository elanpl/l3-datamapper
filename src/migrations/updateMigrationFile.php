<?php
namespace migrations;

use elanpl\DM\migrations\Table;

class UPDATE_MIGRATION_FILE {
    public function up() {
        
        $table = Table::update('UPDATE_MIGRATION_FILE');
        
        
        return $table->run();
    }
    
    public function down() {
        
    }
}

