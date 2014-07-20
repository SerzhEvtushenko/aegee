<?php
/**
 * @package SolveProject
 * @subpackage ACL
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created Dec 23, 2009 12:04:05 AM
 */

/**
 * Controller for running test in console
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class TestTask extends slTask {

    protected $help_messages = array(
        'unit'       => "test:unit TEST_NAME [--abs] [--framework]",
        'create_data'=> "test:create-data MODEL [--count=OBJECT_COUNT] [--FIELD=VALUE] [--FIELD-length=FIELD_LENGTH]"
    );

    public function actionUnit() {
        $this->requireParametersCount(1);
        $test_name = $this->route->getVar(0);
        if (!$test_name) {
            echo $this->colorize('%RError: %nNo test specified'.PHP_EOL);
            die();
        }
        $pre_path = SL::getDirUserLibs() . 'tests/unit/';

        if ($this->route->getVar(':options/abs')) {
            $pre_path = '';
        } elseif ($this->route->getVar(':options/framework')) {
            $pre_path = SL::getDirFramework() . 'tests/unit/';
        }

        $files_to_test = array();
        $test_file = $pre_path . $test_name . 'Test.php';
        if (is_dir($pre_path . $test_name)) {
            $files_to_test = slLocator::getInstance()->in($pre_path . $test_name)->find('*Test.php');
        } elseif (is_file($test_file)) {
            $files_to_test = array($test_file);
        }
        if (empty($files_to_test)) {
            echo $this->colorize('%RError: %nNo tests found:['.$pre_path . $test_name . 'Test.php'.']' . PHP_EOL);
            die();
        }

        $unitTestRunner = slUnitTestRunner::getInstance();

        foreach($files_to_test as $file_path) {
            $test_class = explode('/', $file_path);
            $test_class = array_pop($test_class);
            $test_class = substr($test_class, 0, strrpos($test_class, '.'));
            $unitTestRunner->addTest($test_class, $file_path);

        }
        $results = slUnitTestRunner::getInstance()->runAllTests();
        $console_output = slUnitTestResult::getConsoleResult($results, $this->route->getVar(':options/verbose'));
        echo $this->colorize($console_output);

        $data = ob_get_clean();
        if (slRouter::getCurrentMode() == 'web') {
            echo str_replace("\n", '<br/>', $data);
        } else {
            echo $data;
        }
    }

    public function actionConfig (){
        $result = array();
        $php_version = phpversion();
        echo $this->colorize('Testing whether the server matches requirements:'
            ."\n".'PHP Version >= 5.2.8 - '.((version_compare(phpversion(),'5.2.8','>=')) ? '%GOK%n' : '%RFAIL%n').' (current '.$php_version.')');
        echo $this->colorize(
            "\n".'PDO loaded - '.((extension_loaded('pdo')) ? '%GOK%n' : '%RFAIL%n').' (current '.$php_version.')');
        echo $this->colorize(
            "\n".'PDO MySQL loaded- '.((extension_loaded('pdo_mysql')) ? '%GOK%n' : '%RFAIL%n').' (current '.$php_version.')');
        $mysql_version = Q::execSQL('SELECT VERSION()')->fetchColumn(0);
        echo $this->colorize(
            "\n".'MySQL Version >= 5.0 - '.(($mysql_version[0] >= 5) ? '%GOK%n' : '%RFAIL%n').' (current '.$mysql_version.')');
        $disabled = explode(', ', ini_get('disable_functions'));
        echo $this->colorize(
            "\n".'exec() function enabled - '.((!in_array('exec', $disabled)) ? '%GOK%n' : '%RFAIL%n'));
        if (!in_array('exec', $disabled)) {
            $a = $b = null;
            exec('convert -version',$a,$b);
            if ($b == 0
                || $b == -1073741819 //my strange windows return code
                ) {
                $imagick_installed = true;
            } else {
                $imagick_installed = false;
            }
        } else {
            $imagick_installed = false;
        }
        echo $this->colorize(
            "\n".'imagick installed - '.(($imagick_installed) ? '%GOK%n' : '%RFAIL%n'));
        echo $this->colorize(
            "\n".'curl enabled - '.((function_exists('curl_version')) ? '%GOK%n' : '%RFAIL%n'));
        echo $this->colorize(
            "\n".'mb_string enabled - '.((function_exists('mb_strlen')) ? '%GOK%n' : '%RFAIL%n'));
        echo $this->colorize(
            "\n".'GD enabled - '.((function_exists('gd_info')) ? '%GOK%n' : '%RFAIL%n'));
        echo $this->colorize(
            "\n".'JSON enabled - '.((function_exists('json_encode')) ? '%GOK%n' : '%RFAIL%n'));
        echo $this->colorize(
            "\n".'ctype enabled - '.((function_exists('ctype_digit')) ? '%GOK%n' : '%RFAIL%n'));
    }

    public function actionCreateData(){
        $count = $this->route->getVar(':options/count',rand(1,10));
        $model = $this->route->getVar(0);
        if (!class_exists($model)){
            echo $this->colorize('%RError: %nModel ['.$model.'] not found');
            die();
        }
        
        $object = new $model;
        $relations = $object->getStructure('relations');
        $many_to_one_relation_fields = array();
        $many_to_many_relations = array();
        if (is_array($relations)){
            foreach ($relations as $relation_name => $params) {
                $vars = slDBOperator::calculateRelationVariables($object, $params, $relation_name);
                if ($vars['type'] == 'many_to_one') {
                    $many_to_one_relation_fields[$vars['local_field']] = Q::create($vars['foreign_table'])->useValue($vars['foreign_key'])->exec();
                } elseif ($vars['type'] == 'many_to_many') {
                    $many_to_many_relations[$relation_name] = array(
                        'table' => $vars['many_table'],
                        'items' => Q::create($vars['foreign_table'])->useValue($vars['foreign_key'])->exec(),
                        'local_field' => $vars['local_field'],
                        'foreign_field' => $vars['foreign_field'],
                        'local_key' => $vars['local_key']
                    );
                }
            }
        }
        for ($i =0 ; $i < $count; $i++) {
            $object = new $model;
            foreach ($object->getStructure('columns') as $column => $params) {
                if ($column == $object->getPKField()){
                    continue;
                }
                if ($value = $this->route->getVar(':options/'.$column)) {

                } else {
                    if (array_key_exists($column, $many_to_one_relation_fields)) {
                        $value = $many_to_one_relation_fields[$column][array_rand($many_to_one_relation_fields[$column],1)];
                    } elseif ($column == 'email') {
                        $value = substr(md5(time().microtime()),0,rand(6,31)).'@'.substr(md5(time().microtime()),0,3).'.com';
                    } else {
                        if (strpos($params['type'],'varchar') !== false){
                            $value = $this->createRandomText($this->route->getVar(':options/'.$column.'-length',255));
                        } elseif(strpos($params['type'],'text') !== false) {
                            $value = $this->createRandomText($this->route->getVar(':options/'.$column.'-length',1000));
                        } elseif(strpos($params['type'],'tinyint') !== false) {
                            $value = rand(0,1);
                        } elseif(strpos($params['type'],'int') !== false) {
                            $value = rand(1,5000);
                        } elseif(strpos($params['type'],'date') !== false) {
                            $array = array('hours','days','months');
                            $str = (rand() > 0.5 ? '-' : '+').rand(1,5).' '.$array[array_rand($array,1)];
                            $value = date('Y-m-d H:i:s',strtotime($str));
                        } elseif(strpos($params['type'],'enum') !== false) {
                            preg_match_all( '/"(.*?)"/', $params['type'], $enum_array );
                            $enum_fields = $enum_array[1];
                            $value = $enum_fields[array_rand($enum_fields,1)];
                        } else {
                            $value = null;
                        }
                    }
                }
                $object->$column = $value;
            }
            if ($object->save()) {
                foreach ($many_to_many_relations as $params) {
                    $insert = array();
                    for ($i = 0; $i < rand(1,count($params['items'])); $i++) {
                        $foreign_id = $params['items'][array_rand($params['items'],1)];
                        $insert[$foreign_id] = array(
                            $params['local_field'] => $object->{$params['local_key']},
                            $params['foreign_field'] => $foreign_id
                        );
                    }
                    if (count($insert)) {
                        Q::create($params['table'])->insert(array_values($insert))->exec();
                    }
                }
                echo $this->colorize('%G+ %nObject was saved'.PHP_EOL);
            } else {
                echo $this->colorize('%R- %nObject was not saved:'.PHP_EOL);
                foreach ($object->getErrors() as $key => $error) {
                    echo $this->colorize($key.': '.PHP_EOL);
                    foreach ($error as $e){
                        echo $this->colorize("\t".$e['message'].PHP_EOL);
                    }
                    echo $this->colorize(PHP_EOL);
                }
            }
        }
        echo $this->colorize('Done'.PHP_EOL);
    }

    private function createRandomText($length) {
        $data = explode(' ','Волна, в согласии с традиционными представлениями, искажает оливин, в итоге приходим к логическому противоречию. Кама синхронизирует взрыв, как и предполагалось. Интерпретация всех изложенных ниже наблюдений предполагает, что еще до начала измерений кварк вертикально уравновешивает эпигенез, в итоге приходим к логическому противоречию. Происхождение пространственно пододвигается под вращательный многочлен, поскольку любое другое поведение нарушало бы изотропность пространства. Курчавая скала аккумулирует тригонометрический бином Ньютона, что позволяет проследить соответствующий денудационный уровень. Представляется логичным, что силовое поле масштабирует квантово-механический график функции, откуда следует доказываемое равенство. Мантия сдвигает стремящийся бозе-конденсат, делая этот типологический таксон районирования носителем важнейших инженерно-геологических характеристик природных условий. Если предварительно подвергнуть объекты длительному вакуумированию, то плазма вырождена. Фронт, исключая очевидный случай, причленяет к себе межядерный интеграл от функции, имеющий конечный разрыв, что известно даже школьникам. Интегрирование по частям позиционирует минимум так, как это могло бы происходить в полупроводнике с широкой запрещенной зоной. Мишень небезынтересно отображает векторный интеграл по бесконечной области, что, однако, не уничтожило доледниковую переуглубленную гидросеть древних долин. Силовое поле, общеизвестно, тормозит сверхпроводник, что свидетельствует о проникновении днепровских льдов в бассейн Дона. Комплекс, общеизвестно, в принципе тормозит внутримолекулярный туффит, что в общем свидетельствует о преобладании тектонических опусканий в это время.');
        $len = rand(0,$length);
        $str = '';
        do {
            $str .= ' '.$data[rand(0,count($data) -1 )];
        } while (mb_strlen($str) < $len);
        return trim($str);
    }

    public function actionRenameGalleries() {
        Q::create('programs_galleries')->update(
            array('title' => 'Photo Gallery')
        )->exec();
        Q::create('programs_videogalleries')->update(
            array('title' => 'Video Gallery')
        )->exec();
        echo 'done!'.PHP_EOL;
    }

    public function actionTestGalleryAdd() {
        $p = new PostMultiple();
        $p->title = 'Test';
        $p->save();

        $p->addGallery(array(
            'title' => 'Gallery Test'
        ));

        $p->addGallery(array(
            'max_photo_height' => 12
        ));

        $galleries = $p->getGalleries();
        $p->updateGallery(array(
            'description' => 'asdasd',
            'id_gallery' => $galleries[0]['id']
        ));

        echo 'done!'.PHP_EOL;
    }

    public function actionCreateTestExcursions() {
        $factories = Factory::loadList();

        $availible_times = array('9:30','10:00','11:30','12:15','12:30','14:00','14:30','15:00','15:15','16:00','17:00');

        foreach ($factories as $factory) {
            if ($factory->id != 2) continue;

            $this_monday = strtotime('last monday + 5 week') + 1;

            for ($i = 0; $i < 7; $i++) {
                $r = rand(0,100) / 100;
                if ($r > 0.80) {
                    continue;
                }
                $day_timestamp = $this_monday + ($i * 86400);

                $random_number = rand(1, count($availible_times)) - rand(0,5);
                if ($random_number < 1) $random_number = 1;

                $random_times_keys = array_rand($availible_times, $random_number);
                if (!is_array($random_times_keys)) {
                    $random_times_keys = array($random_times_keys);
                }
                foreach ($random_times_keys as $random_times_key) {
                    $excursion_datetime = date('Y-m-d ',$day_timestamp).' '.$availible_times[$random_times_key];

                    $e = new Excursion();
                    $e->id_factory = $factory->id;
                    $e->holding_at = $excursion_datetime;
                    $e->is_available = 1;
                    $e->save();
                }
            }
        }

        $this->message('Done!');
        die();
    }

    public function actionCreateStaticPages() {
        $dynamic_structure = slModel::getModel('StaticPage')->getStructure('abilities/dynamic_structure');
        unset($dynamic_structure['field_getter']);
        $slugs = array_keys($dynamic_structure);

        foreach ($slugs as $slug) {
            foreach (MLT::getLanguagesAliases() as $lang) {
                $sp = new StaticPage();
                $sp->lang = $lang;
                $sp->slug = $slug;
                if ($sp->save()) {
                    $this->message($slug.'-'.$lang.' saved');
                } else {
                    $this->error(dumperGet($sp->getErrors()));
                }
            }
        }

        $this->message('Done!');
    }


    public function actionCreateRights() {
        $rights = array(
            'root'                     => 'Суперадминистратор',
            'edit_factories'           => 'Редактировать фабрики',
            'edit_requests'            => 'Редактировать все заявки',
            'edit_requests_kiev'       => 'Редактировать заявки. Киев',
            'edit_requests_vinnytsya'  => 'Редактировать заявки. Винница',
            'edit_requests_kremenchug' => 'Редактировать заявки. Кременчуг',
            'edit_requests_mariupol'   => 'Редактировать заявки. Мариуполь',
            'edit_schedule'            => 'Редактировать расписание',
            'edit_excursions_descriptions' => 'Редактировать описание экскурсий',
            'edit_homepage'            => 'Редактировать главную страницу',
            'edit_mlt'                 => 'Редактировать тексты',
            'edit_email_templates'     => 'Редактировать шаблоны писем',
            'edit_users'               => 'Редактировать пользователей',
            'edit_settings'            => 'Редактировать настройки',
        );

        $existing_rights = Q::create('acl_rights')->select('id')->useValue('id')->exec();
        $rights_to_delete = array();
        foreach ($existing_rights as $right) {
            if (!in_array($right,array_keys($rights))) $rights_to_delete[] = $right;
        }

        if (count($rights_to_delete)) {
            Q::create('acl_rights')->delete()->where(array('id'=>$rights_to_delete))->exec();
            foreach ($rights_to_delete as $right) {
                echo $this->colorize('%R- %n '.$right.' deleted'.PHP_EOL);
            }
        }

        foreach ($rights as $right_alias => $right_title) {
            if (Q::create('acl_rights')->select('COUNT(*) as cnt')
                ->where(array('id'=>$right_alias))->useValue('cnt')->one()->exec()) {
                Q::create('acl_rights')->update(array('title'=>$right_title))
                    ->where(array('id'=>$right_alias))->exec();
                echo $this->colorize('%G+ %n '.$right_alias.' updated'.PHP_EOL);
            } else {
                Q::create('acl_rights')->insert(array(
                    'title' => $right_title,
                    'id'    => $right_alias
                ))->exec();
                echo $this->colorize('%G+ %n '.$right_alias.' created'.PHP_EOL);
            }
        }
    }
}