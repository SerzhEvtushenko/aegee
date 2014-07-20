<?php
/**
 * Description...
 *
 * @author mounter (mounters@gmail.com)
 * @date 10.11.2009 0:44:53
 */

class DBTask extends slTask {

    protected $help_messages = array(
        'update_all'        => "db:update-all [MODEL] [--clean]",
        'update_class'      => "db:update-class [MODEL]",
        'update_db'         => "db:update-db MODEL [--clean]",
        'update_relations'  => "db:update-relations MODEL [--clean]",
        'ability_add'       => "db:ability-add MODEL ability1,ability2,...",
        'ability_configure' => "db:ability-configure MODEL [ability1,ability2,...]",
        'drop_db'           => "db:drop-db",
        'build_db'          => "db:build-db",
        'data_dump'         => "db:data-dump [MODEL1[,MODEL2,...]]",
        'data_load'         => "db:data-load [MODEL1[,MODEL2,...]]",
        'profile_configure' => "db:profile-configure [PROFILE_NAME=default] [--dsn=DSN] [--dbtype=mysql] [--dbname=DB_NAME] [--host=localhost] [--user=root] [--pass=PASSWORD] [--charset=utf8] [--collate=utf8_general_ci]",
        'profile_activate'  => "db:profile-activate PROFILE_NAME",
        'gen_model'         => "db:gen-model MODEL_NAME [--class] [--db]",
        'sql'               => "db:sql SQL_QUERY",
        'show_table'        => "db:show-table [TABLE]",
        'count'             => "db:count [TABLE]"
    );
    /**
     * @var slDBOperator
     */
    private $_dboperator = null;

    private $_model_name = null;

    public function preAction() {
        parent::preAction();
        $this->_dboperator = slDBOperator::getInstance(true);
    }

    public function actionUpdateAll() {
        $model_name = ucfirst($this->route->getVar(0));
        if ($model_name == '') {
            $model_name = null;
        }
        $this->_dboperator->generateModelClass($model_name);
        $safe_update = true;
        if ($this->route->getVar(':options/retable')) {

        } elseif ($this->route->getVar(':options/clean')) {
            $safe_update = false;
        }
        $this->_dboperator->updateDBFromStructure($model_name, $safe_update);
        echo $this->colorize('%G+ %nModel and Structure generated successfully' . "\n");
    }
    
    public function actionUpdateClass($model_name = false) {
        if (!$model_name) $model_name = $this->route->getVar(0);

        $this->_dboperator->generateModelClass($model_name);
        echo $this->colorize('%G+ %nClass generated successfully' . "\n");
    }

    public function actionUpdateDb($model_name = false) {
        if (!$model_name) {
            $this->requireParametersCount(1);
            $model_name = ucfirst($this->route->getVar(0));
            if (!$model_name) {
                echo $this->colorize('%RError:%n You must specify model name');
                die();
            }
        }
        $safe_update = true;
        if ($this->route->getVar(':options/retable')) {
            //@todo make re-table works
        } elseif ($this->route->getVar(':options/clean')) {
            $safe_update = false;
        }

        $this->_dboperator->updateDBFromStructure($model_name, $safe_update);
        echo $this->colorize('%G+ %nDatabase updated' . ($safe_update ? '' : ' and cleaned') . "\n");
    }

    public function actionDropDb() {
        $profile = SL::getDatabaseConfig('active_profile', 'default');
        $this->_dboperator->dropDB(SL::getDatabaseConfig('profiles/'.$profile.'/dbname'));
        echo $this->colorize('%G-%n Database was dropped'.PHP_EOL);
    }

    public function actionBuildDb() {
        $profile = SL::getDatabaseConfig('active_profile', 'default');
        if (!SL::getDatabaseConfig('profiles/'.$profile.'/dbname')) {
            echo $this->colorize('%RError:%n Database is not configured!'.PHP_EOL);
            die();
        }

        $profile_data = SL::getDatabaseConfig('profiles/'.$profile);
        unset($profile_data['dbname']);
        slDatabaseManager::setActiveProfile('temp', $profile_data);
        try {
            $this->_dboperator->createDB(SL::getDatabaseConfig('profiles/'.$profile.'/dbname'));
        } catch(Exception $e) {
            echo $this->colorize('%RError:%n Cannon create database'.PHP_EOL);
            die();
        }
        slDatabaseManager::setActiveProfile($profile);
        $this->_dboperator->updateDBFromStructure();
        echo $this->colorize('%G+ %nDatabase was built successfully'.PHP_EOL) ;
    }

    /**
     * engine: slDB
        adapter: PDO
        profiler: true
        separate_yaml: true
        profiles:
          default:
            dbtype: mysql
            dbname: f_shop
            host: localhost
            user: root
            pass: root
            charset: utf8
            collate: utf8_general_ci

     * @return void
     */
    public function actionWizard() {
        $config = array(
            'engine'        => 'slDB',
            'adapter'       => 'PDO',
            'profiler'      => true,
            'separate_yaml' => true,
            'profiles'      => array(
                'default'   => array()
            )
        );
        $profile = array(
            'dbtype'    => 'mysql',
            'charset'   => 'utf8',
            'collate'   => 'utf8_general_ci'
        );
        $profile['dbname']  = $this->consolePrompt('Enter DB name');
        $profile['user']    = $this->consolePrompt('Enter DB user', 'root');
        $profile['pass']    = $this->consolePrompt('Enter DB pass', 'root');
        $profile['host']    = $this->consolePrompt('Enter DB host', '127.0.0.1');
        $config['profiles']['default']  = $profile;

        file_put_contents(SL::getDirRoot() . 'config/database.yml', sfYaml::dump($config));
        SL::setDatabaseConfig(null, $config, true);
        echo $this->colorize('%G+ %nDatabase configured!' . "\n");
        die();
        slDatabaseManager::initialize();
        slDatabaseManager::setActiveProfile('default');
        $connection = slDatabaseManager::getConnection('default', true);
        try {
            $connection->query('SELECT 1 FROM DUAL');
            echo $this->colorize('%G+ %nConnection test successfull!' . "\n");
        } catch(Exception $e) {
            echo $this->colorize('%R+ %nConnection test fail!' . "\n");
            echo $e->getMessage() . "\n";
        }
    }

    public function actionDataDump() {
        $models = $this->route->getVar(0);
        if ($models) {
            $models = explode(',', $models);
        }

        $this->_dboperator->dataDump($models);
        echo $this->colorize('%G+ Data dumped' . PHP_EOL);
    }

    public function actionDataLoad() {
        $models = $this->route->getVar(0);
        if ($models) {
            $models = explode(',', $models);
        }

        $this->_dboperator->dataLoad($models);
        echo $this->colorize('%G+ %nData loaded' . PHP_EOL);
    }

    public function actionProfileConfigure() {
        $name       = $this->route->getVar(0, 'default');

        $config     = array();
        $config['dsn']        = $this->route->getVar(':options/dsn');
        $config['dbtype']     = $this->route->getVar(':options/dbtype', 'mysql');
        $config['dbname']     = $this->route->getVar(':options/dbname');
        $config['host']       = $this->route->getVar(':options/host', 'localhost');
        $config['user']       = $this->route->getVar(':options/user', 'root');
        $config['pass']       = $this->route->getVar(':options/pass', $this->route->getVar('pass'));
        $config['charset']    = $this->route->getVar(':options/charset', 'utf8');
        $config['collate']    = $this->route->getVar(':options/collate', 'utf8_general_ci');

        if (!$config['dsn']) unset($config['dsn']);

        $database_config = SL::getDatabaseConfig();
        $database_config['profiles'][$name] = $config;
        file_put_contents(SL::getDirRoot() . 'config/database.yml', sfYaml::dump($database_config));
        echo $this->colorize('%G+ %nProfile configured'.PHP_EOL);
    }

    public function actionProfileActivate() {
        $this->requireParametersCount(1);
        
        $database_config = SL::getDatabaseConfig();
        $database_config['active_profile'] = $this->route->getVar(0);
        file_put_contents(SL::getDirRoot() . 'config/database.yml', sfYaml::dump($database_config));
        echo $this->colorize('%G+ %nNew profile activated: '.$database_config['active_profile'].PHP_EOL);
    }

    public function actionGenModel() {
        $this->requireParametersCount(1);

        $model_name = $this->route->getVar(0);
        if (!$model_name) {
            echo $this->colorize('%RError:%n No model name specified' . PHP_EOL);
            die();
        }
        $table_name = slInflector::directorize(slInflector::pluralize($model_name));
        $content = <<<TEXT
table: $table_name
columns:
  id:
    type: 'int(11) unsigned'
    auto_increment: true
  title:
    type: varchar(255)
indexes:
  primary:
    columns:
      - id
TEXT;
        slLocator::makeWritable(SL::getDirUserLibs() . 'slDB/structure/');
        $model_name = ucfirst($model_name);
        $structure_path = SL::getDirUserLibs() . 'slDB/structure/' . $model_name . '.yml';
        if (is_file($structure_path)) {
            echo $this->colorize('%RError:%n slModel '.$model_name. ' is already exists in ['.$structure_path.'].' . PHP_EOL);
            die();
        }
        file_put_contents($structure_path, $content);
        echo $this->colorize("%G+ %nModel ".$model_name.' created successfully in ['.$structure_path.']' . PHP_EOL);
        if ($this->route->getVar(':options/class')) {
            $this->actionUpdateClass($model_name);
        }
        if ($this->route->getVar(':options/db')) {
            $this->actionUpdateDb($model_name);
        }
    }

    public function actionSql() {
        $this->requireParametersCount(1);
        $sql = implode(' ', $this->route->getVar());
        if (!$sql) {
            echo $this->colorize('%RError:%n No SQL query specified'.PHP_EOL);
            die();
        }

        /**
         * @var $res PDOStatement
         */
        try {
            $res = Q::execSQL($sql);
        } catch(Exception $e) {
            echo $this->colorize('%RError:%n '.$e->getMessage().PHP_EOL);
            die();
        }
        echo $this->colorize('%G>%n '.$sql.''.PHP_EOL);

        try {
            echo $this->colorize('%[Pre]'.print_r($res->fetchAll(PDO::FETCH_ASSOC),true).'%[pre]');
        } catch (PDOException $e) {
            echo $this->colorize('%YWarning:%n Query was executed successfully, but returned no data.');
        }
    }

    public function actionShowSettings() {
        $data = SL::getDatabaseConfig();
        echo $this->colorize('%[Pre]'.dumpAsString($data).'%[pre]');
    }

    public function actionShowTable() {
        if (!($table_name = $this->route->getVar(0))) {
            $tables = $this->_dboperator->getDBTables(true);
            foreach($tables as $table_name=>$info) {
                echo $this->colorize(' - '.$table_name .PHP_EOL);
            }
        } else {
            $structure = $this->_dboperator->getTableStructure($table_name);
            echo $this->colorize('%[Pre]'.dumpAsString($structure, true).'%[pre]');
        }
    }

    public function actionCount() {
        $this->requireParametersCount(1);
        $table_name = $this->route->getVar(0);
        if (!$table_name) {
            echo $this->colorize('%RError:%n No table specifed');
            die();
        }
        $res = Q::create($table_name)->select('count(*) cnt')->useValue('cnt')->one()->exec();
        echo $this->colorize('%G> %nThere is '.$res.' rows in table '.$table_name . PHP_EOL);
    }

    public function actionUpdateConfig() {
        $datetime_format = slDatabaseManager::getConnection()->getDateTimeFormat(true);
    }

    protected function requireModelParameter() {
        $model_name = slInflector::camelCase($this->route->getVar(0));
        if (!class_exists($model_name)) {
            slDBOperator::getInstance()->generateModelClass($model_name);
            if (!class_exists($model_name)) {
                $this->error('Model "'.$model_name.'" not found!');
            }
        }
        $this->_model_name = $model_name;
    }

    public function actionAbilityAdd() {
        $this->requireParametersCount(2);
        $this->requireModelParameter();

        $abilities = explode(',', $this->route->getVar(1));
        $model_structure = new slModelStructure($this->_model_name);
        foreach($abilities as $ability_name) {
            $ability_class = slInflector::camelCase($ability_name).'Ability';
            if (!class_exists($ability_class)) {
                $this->warning('Ability '.$ability_name.' not found');
            }
            $model_structure->addAbility($ability_name);
        }
    }

    public function actionAbilityConfigure() {
        $this->requireModelParameter();
        $model_obj  = new $this->_model_name();
        $abilities = explode(',', $this->route->getVar(1));

        foreach($abilities as $ability_name) {
            $ability_class = slInflector::camelCase($ability_name).'Ability';
            $ability = new $ability_class($model_obj);
            $ability->setUp();
        }
    }

    public function actionUpdateRelations() {
        $this->requireModelParameter();
        slDBOperator::getInstance()->updateDBRelations($this->_model_name);
    }

}
