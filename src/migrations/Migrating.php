<?php
namespace elanpl\DM\migrations;

use elanpl\DM\migrations\MigrationModel;
use elanpl\DM\migrations\Table;
class Migrating {
    function __construct($database_path,  $vendor_path = __DIR__) {
        $this->vendor_path = $vendor_path;
        
        $this->migrations_path = $database_path.'/migrations';
        $this->seeds_path = $database_path.'/seeds';
        
        if (!file_exists($database_path))
            mkdir ($database_path, 0777);
        if (!file_exists($this->migrations_path))
            mkdir ($this->migrations_path, 0777);
        if (!file_exists($this->seeds_path))
            mkdir ($this->seeds_path, 0777);
        
    }
    
    public function run($param1 = '', $param2 = ''){
        
        switch ( $param1 ) {
            case 'migrate': 
                $this->migrate($param2);
                break;
            case 'reset':
                $this->reset();
                break;
            case 'rollback':
                $this->rollback( (int)$param2 );
                break;
            case 'make_create_migration':
                $this->makeCreateMigration($param2);
                break;
            case 'make_update_migration':
                $this->makeUpdateMigration($param2);
                break;      
            case 'seed': 
                $this->seed($param2);
                break;                 
            case 'make_seed':
                $this->MakeSeed($param2);
                break;   
            case 'status':
                echo 'Time       | Step    | Filename'."\n";
                echo '----------------------------------'."\n";
                
                $mimgrationM = new MigrationModel();
                $migrations = $mimgrationM->order_by('id','asc')->get();
                
                foreach ($migrations as $m) {
                    echo $m->timestamp.' | '.$m->step.' | '.$m->file."\n";
                }
                echo '----------------------------------'."\n";
                
                break;
            default :
                echo "params:\n"
                    ."      migrate                 Run migrations\n"
                    ."      migrate filename        Run one migration (filename - camelcase)\n"
                    ."      seed                    Run seeds \n"
                    ."      seed filename           Run one seed (filename - camelcase)\n"
                    ."      reset                   Rollback all migrations\n"
                    ."      rollback                Rollback the last migration\n"
                    ."      rollback step           Rollback last steps(int) migrations \n"
                    ."      make_create_migration filename Create a create migration file\n"   
                    ."      make_update_migration filename Create an update migration file\n"     
                    ."      make_seed filename      Create seed file\n"    
                    ."      status                  Show the status of each migration\n";
                
                break;
        }
    }
    
    private function migrate( $file_to_migrate = '' ){
        echo 'START'."\n";
                
        $this->addMigrationsTable();

        $files = scandir ( $this->migrations_path );

        $stepM = new MigrationModel();
        $stepM->select_max('step')->get();
        $step = (int)$stepM->step +1; 

        
        foreach ( $files as $file) {
            if ( $file == '.' || $file == '..' )
                continue;
            
            $className = substr($file, 15, strlen($file) - 15 - 4 );
            if ( $file_to_migrate == '' || $className == $file_to_migrate) {
                $migration = (new MigrationModel())->where('file',$file)->get();
                if ( $migration->result_count() == 0 ) {
                    echo 'Migrating '.$file."\n";

                    include( $this->migrations_path.'/'.$file );
                    $className = '\\migrations\\'.$className;
                    echo $className;
                    $m = new $className();
                    if ( $m->up() ) {
                        $migration = new MigrationModel();
                        $migration->file = $file;
                        $migration->step = $step;
                        $migration->save();
                    }  
                } else {
                    //echo ' - Already migrated '.$file."\n";
                } 
            }
        }
        echo 'END'."\n";
    }
    
    private function reset() {
        echo 'Reset start'."\n";
        $migration = new MigrationModel();
        $migratedFiles = $migration->order_by('id','desc')->get();   
        foreach ($migratedFiles as $m) {
            echo 'Rolback '.$file."\n";
            include( $this->migrations_path.'/'.$m->file );
            $className = substr($m->file, 15, strlen($m->file) - 15 - 4 );
            $m = new $className();
            $m->down();

            $del = new MigrationModel();
            $del->where('file', $file);
            $del->delete();

            echo 'Rolback '.$file.' done.'."\n";
        }

        echo 'Reset ended'."\n";
    }
    
    private function rollback($rolback_step_count = 0){
        $stepM = new MigrationModel();
        $stepM->select_max('step')->get();

        if ( $stepM->result_count() > 0 ) {
            if ( $rolback_step_count > 0) {
                $step = $stepM->step - $rolback_step_count;
            } else {
                $step = $stepM->step; 
            }

            $rolbackM = new MigrationModel();
            $rolbackM->where('step >=',$step)->get();

            foreach ($rolbackM as $m) {
                echo 'Rolback '.$file."\n";
                include( $this->migrations_path.'/'.$m->file );
                $className = substr($m->file, 15, strlen($m->file) - 15 - 4 );
                $m = new $className();
                $m->down();

                $del = new MigrationModel();
                $del->where('file', $file);
                $del->delete();

                echo 'Rolback '.$file.' done.'."\n";
            }

            echo 'Rolback ended'."\n";
        } else {
            echo 'Nothing to rolback'."\n";
        }
    }
    
    private function makeCreateMigration($create_file) {
        if ( $create_file == '') {
            echo 'Unknown filename'."\n";
        } else {
            $content = file_get_contents($this->vendor_path.'/createMigrationFile.php');
            $create_file = self::generateFileSlug($create_file, false);
            $fileName = date('YmdHis').'_'.$create_file.'.php';
            $content = str_replace('CREATE_MIGRATION_FILE', $create_file, $content);
            file_put_contents($this->migrations_path.'/'.$fileName, $content);
            echo 'File '.$fileName.' created'."\n";
        }
    }

    private function makeUpdateMigration($create_file) {
        if ( $create_file == '') {
            echo 'Unknown filename'."\n";
        } else {
            $content = file_get_contents($this->vendor_path.'/updateMigrationFile.php');
            $create_file = self::generateFileSlug($create_file, false);
            $fileName = date('YmdHis').'_'.$create_file.'.php';
            $content = str_replace('UPDATE_MIGRATION_FILE', $create_file, $content);
            file_put_contents($this->migrations_path.'/'.$fileName, $content);
            echo 'File '.$fileName.' created'."\n";
        }
    }
    
    private function MakeSeed($create_file) {
        if ( $create_file == '') {
            echo 'Unknown filename'."\n";
        } else {
            $content = file_get_contents($this->vendor_path.'/seedFile.php');
            $create_file = self::generateFileSlug($create_file, false);
            $fileName = date('YmdHis').'_'.$create_file.'.php';
            $content = str_replace('SEED_FILE', $create_file, $content);
            file_put_contents($this->seeds_path.'/'.$fileName, $content);
            echo 'File '.$fileName.' created'."\n";
        }
    }
    
    private function seed($file_to_seed = '') {
        $files = scandir ( $this->seeds_path );
        
        
        foreach ( $files as $file) {
            if ( $file == '.' || $file == '..' )
                continue;
            $className = substr($file, 15, strlen($file) - 15 - 4 );
            if ( $file_to_seed == '' || $className == $file_to_seed) {
                echo 'Seeding '.$file."\n";
                include( $this->seeds_path.'/'.$file );

                $className = '\\migrations\\'.$className;
                $m = new $className();
                $m->run();

                echo 'Seed '.$file.' done'."\n";
            }
        }
        echo 'Seeding ended'."\n";
    }
    
    private function addMigrationsTable() {
        
        $table = Table::create(MigrationModel::tableName);
        $table->increments('id');
        $table->string('file')->nullable();
        $table->timestamp('timestamp')->defaultValue('current');;
        $table->integer('step')->nullable();
        $table->run(false);
    }
    
    static function generateSlug($phrase, $maxLength, $allow_slash = false, $toLower = true) {
        $a = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð',
                   'Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã',
                   'ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ',
                   'ö','ø','ù','ú','û','ü','ý','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ',
                   'ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę',
                   'Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī',
                   'ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ',
                   'Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ',
                   'Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š',
                   'š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű',
                   'Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','ơ',
                   'Ư','ư','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ',
                   'ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ');
        $b = array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D',
                   'N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a',
                   'a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o',
                   'o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C',
                   'c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e',
                   'E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I',
                   'i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L',
                   'l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O',
                   'o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S',
                   's','S',
                   's','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u',
                   'U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o',
                   'U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U',
                   'u','A','a','AE','ae','O','o');

        $result = str_replace($a, $b, $phrase);

        if ($toLower)
            $result = strtolower($result);
        if ($allow_slash)
            $result = preg_replace("#[^A-Za-z0-9\s-_/.]#", "", $result);
        else 
            $result = preg_replace("/[^A-Za-z0-9\s-_]/", "", $result);
        $result = trim(preg_replace("/[\s]+/", " ", $result));
        $result = trim(substr($result, 0, $maxLength));
        $result = preg_replace("/\s/", "_", $result);

        return $result;
    }

    static function generateFileSlug($phrase, $toLower = true) {
        $lista = explode('.', $phrase);
        for ($i=0; $i<count($lista); $i++) {
            $lista[$i] = self::generateSlug($lista[$i],255, false, $toLower);
        }	
        $phrase = implode('.', $lista);	 
        return substr($phrase,0,255);	
    }
    
}