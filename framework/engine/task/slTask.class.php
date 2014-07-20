<?php
/**
 * @package SolveProject
 * @subpackage View
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 20.10.2009 0:58:45
 */

/**
 * Factory storage for slView Engines
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slTask extends slDevController {

    protected $consoleInput     = null;
    protected $consoleOutput    = null;

    protected $colorer = null;

    static $web_colors_conversions = array ( // static so the array doesn't get built
                                      // everytime
            // %y - yellow, and so on... {{{
            '%y' => '<span style="color: yellow;"',
            '%g' => '<span style="color: green;"',
            '%b' => 'color: blue;',
            '%r' => 'color: red;',
            '%p' => 'color: purple;',
            '%m' => 'color: purple;',
            '%c' => 'color: cyan;',
            '%w' => 'color: grey;',
            '%k' => 'color: black;',
            '%n' => '</span>',
            '%Y' => '<span style="color: yellow;">',
            '%G' => '<span style="color: green;">',
            '%B' => 'color: blue',
            '%R' => '<span style="color: red;">',
            '%P' => 'color: purple',
            '%M' => 'color: purple',
            '%C' => 'color: cyan',
            '%W' => 'color: grey',
            '%K' => 'color: black',
            '%N' => 'color: reset',
            '%3' => 'background: yellow;',
            '%2' => 'background: green;',
            '%4' => 'background: blue;',
            '%1' => 'background: red;',
            '%5' => 'background: purple;',
            '%6' => 'background: cyan;',
            '%7' => 'background: grey;',
            '%0' => 'background: black;',
        );

    protected $help_messages = array();

    public function __construct(slRoute $route) {
        parent::__construct($route);
        $this->view->setRenderType(slView::RENDER_NONE);
        if (!method_exists($this, $this->route['action'])) {
            $this->route->set('actionDefault', 'action');
        }
        if ((slRouter::getCurrentMode() != 'console')) {
            self::$web_colors_conversions["\n"] = "<br/>";
            self::$web_colors_conversions["%[Pre]"] = "<pre>";
            self::$web_colors_conversions["%[pre]"] = "</pre>";
        } else {
            self::$web_colors_conversions["%[Pre]"] = "";
            self::$web_colors_conversions["%[pre]"] = "";
        }
        $this->consoleInput     = fopen ("php://stdin","r");
        $this->consoleOutput    = fopen ("php://stdout","w");
        $this->colorer = new Console_Color();
    }

    protected function requireParametersCount($count) {
        if ($this->route->getVarsCount() < $count) {
            $this->paramsError('This task require at least '.$count.' parameters!'."%n");
            die();
        }
    }

    protected function paramsError($message) {
        $method = slInflector::directorize(substr($this->route['action'],6));
        $this->warning($message.PHP_EOL);
        if (isset($this->help_messages[$method])) {
            echo $this->colorize($this->help_messages[$method] . PHP_EOL);
        }
        die();
    }

    public function actionDefault() {
        $class = get_class($this);
        echo $this->colorize('%GHere are methods of task '.$class.":\n%n");
        $methods = get_class_methods($class);
        $result = '';
        foreach($methods as $method) {
            if (($method !== 'actionDefault') && (strpos($method, 'action') === 0)) {
                $method = lcfirst(substr($method, 6));
                $tmp= ' :';
                for($i = 0; $i < strlen($method); $i++) {
                    if (($method[$i] >= 'A' && $method[$i] <= 'Z') && ($method[$i-1] < 'A' || $method[$i-1] > 'Z')) {
                        $tmp .= '-'.(($method[$i+1] < 'A' || $method[$i+1] > 'Z') ? strtolower($method[$i]) : $method[$i]);
                    } else {
                        $tmp .= $method[$i];
                    }
                }
                $result .= $tmp . "\n";
            }
        }
        echo $this->colorize($result);
    }

    public function consolePrompt($title, $default = null) {
        $res = null;
        while(!$res) {
            fputs($this->consoleOutput, $title.($default? '['.$default.']' : '').':');
            $res = fgets($this->consoleInput);
            $res = str_replace("\n", "", $res);
            if ((!$res || ($res == "")) && $default) $res = $default;
        }
        return trim($res);
    }


    public function colorize($str) {
        if (slRouter::getCurrentMode() == 'console') {
            if (DIRECTORY_SEPARATOR == '\\') {
                return str_replace(array_keys(self::$web_colors_conversions), '', $str);
            } else {
                return $this->colorer->convert($str);
            }
        } else {
            return str_replace(array_keys(self::$web_colors_conversions), array_values(self::$web_colors_conversions), $str);
        }
    }

    public function message($str) {
        echo $this->colorize('%GMessage:%n '.$str . "\n");
    }

    public function warning($str) {
        echo $this->colorize('%YWarning:%n '.$str . "\n");
    }

    public function error($str) {
        echo $this->colorize('%RError:%n: '.$str . "\n");
        die(1);
    }

    public function preAction() {
        if ($this->route->getVar(':options/help')) {
            $this->printHelp();
            die();
        }
    }

    /**
     * print table as array to standart output
     * @static
     * @param $data
     * @return bool|string
     */
    static public function printTableFromArray($data) {
        if (!count($data)) return true;
        $html = '<table><tr>';
        $indexes = array_keys($data);
        foreach(array_keys($data[$indexes[0]]) as $key) {
            $html .= '<td>'.$key.'</td>';
        }
        $html .= '</tr>';

        foreach($data as $row) {
            $html .= '<tr>';
            foreach($row as $value) {
                $html .= '<td>'.$value.'</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }
}
