<?php
/**
 * @package SolveProject
 * @subpackage FTL
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 02.05.2010 0:43:37
 */

/**
 * Force Teamplate Compiler
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class FTL {

    /**
    * all blocks for parser
    */
    private $_blocks        = array();

    private $_helpers       = array();

    private $_template_dir  = null;
    private $_compile_dir   = null;
    private $_cache_dir     = null;
    private $_common_dir    = null;
    private $_plugins_dirs  = array();

    private $_engine_dir    = null;

    private $_tpl_vars      = array();

    private $_config        = array(
        'default_value'     => '',
        'default_var'       => 'null',
        'ldelim'            => '{',
        'ldelime'           => '\{',
        'rdelim'            => '}',
        'rdelime'           => '\}',
        'force_compile'     => false,
        'save_echo'         => true,
        'escape'            => false
    );

    private $_is_literal    = false;

    private $_allowed_functions = array (
        'trim', 'copy'
    );

    public function __construct() {
        $this->_engine_dir = dirname(__FILE__) . '/';
        $this->_common_dir = SL::getDirRoot() . 'common/templates/';
        $block_files = GLOB($this->_engine_dir . 'internal/block.*');
        foreach($block_files as $file_name) {
            $name = substr($file_name, strpos($file_name, 'block')+6, -4);
            include_once $file_name;
            $class_name = 'ftlBlock'.slInflector::camelCase($name);
            $this->_blocks[$name] = new $class_name($this);
        }
        $this->addPluginsDir($this->_engine_dir . 'helpers/');
    }

    public function setVar($var, $value) {
        $this->_tpl_vars[$var] = $value;
    }

    public function getVar($var, $default = null) {
        return $var && array_key_exists($var, $this->_tpl_vars) ? $this->_tpl_vars[$var] : ($default !== null ? $default : null); 
    }

    public function isVarExists($var_name) {
        return array_key_exists($var_name, $this->_tpl_vars);
    }

    public function getTemplateDir() {
        return $this->_template_dir;
    }
    public function setTemplateDir($path) {
        $this->_template_dir = $path;
    }
    public function getCompileDir() {
        return $this->_compile_dir;
    }
    public function setCompileDir($path) {
        $this->_compile_dir = $path;
    }
    public function getCacheDir() {
        return $this->_cache_dir;
    }
    public function setCacheDir($path) {
        $this->_cache_dir = $path;
    }
    public function getPluginsDirs() {
        return $this->_plugins_dirs;
    }
    public function addPluginsDir($path) {
        if (!is_dir($path)) return false;
        $files = GLOB($path . '*.php');
        foreach($files as $file_path) {
            $file_name = substr($file_path, strrpos($file_path, '/')+1);
            $type = substr($file_name, 0, strpos($file_name, '.'));
            $name = substr($file_name, strlen($type)+1, -4);

            $class_name = 'ftl'. ucfirst($type) . slInflector::camelCase($name);
            include_once $file_path;
            if (!class_exists($class_name)) {
                vd($class_name, file_get_contents($file_path));
            }
            if ($type == 'helper') {
                $this->_helpers[$name] = new $class_name($this);
            } elseif($type == 'block') {
                $this->_blocks[$name] = new $class_name($this);
            }

        }
        $this->_plugins_dirs[] = $path;
    }

    /**
     * Set or Get config $var
     * @param mixed $var
     * @return 
     */
    public function config($var) {
        if (func_num_args() > 1) {
            $this->_config[$var] = func_get_arg(1);
        }
        return $this->_config[$var];
    }

    public function fetch($template, $variables = array(), $params = array()) {
        if ($variables) {
            $this->_tpl_vars = array_merge($this->_tpl_vars, $variables);
        }
        $source = null;
        $source_path = (!empty($params['is_common']) ? $this->_common_dir : $this->_template_dir) . $template;
        if (is_file($source_path)) {
            $store_path = $this->_compile_dir . str_replace('/', '_', $template) . '.' . filectime($source_path) . '.php';
            if ($this->_config['force_compile'] || !is_file($store_path)) {
                $old_files = GLOB($this->_compile_dir . str_replace('/', '_', $template) . '.*');
                foreach($old_files as $file) if (is_file($file)) @unlink($file);

                $source = file_get_contents($source_path);
                $content = $this->_compileTemplate($template, $source);
                file_put_contents($store_path, $content);
            }
        } else {
            throw new slViewException('File not found in:'.$this->_template_dir . $template);
        }
        ob_start();
        include $store_path;
        $output = ob_get_clean();
        return $output;
    }

    public function getCompiledPath($template, $params = array()) {
        $store_path = null;
        $source_path = (!empty($params['is_common']) ? $this->_common_dir : $this->_template_dir) . $template;
        if (is_file($source_path)) {
            $store_path = $this->_compile_dir . str_replace('/', '_', $template) . '.' . filectime($source_path) . '.php';
            if ($this->_config['force_compile'] || !is_file($store_path)) {
                $old_files = GLOB($this->_compile_dir . str_replace('/', '_', $template) . '.*');
                foreach($old_files as $file) @unlink($file);

                $source = file_get_contents($source_path);
                $content = $this->_compileTemplate($template, $source);
                file_put_contents($store_path, $content);
            }
        }
        return $store_path;
    }

    private function _compileTemplate($template, &$source) {
        if (strpos($source, $this->_config['ldelim']) === false) return $source;
        
        $content = file_get_contents($this->_engine_dir . '_template.php');
        $content = str_replace('__FROM__', $template, $content);
        $content = str_replace('__DATE__', date('d/m/Y H:i:s'), $content);

        $content .= '$__lv = &$this->_tpl_vars;' . "\n";
        $content .= '?>' . "\n";

        // removing comments from template {* *}
        $source = preg_replace('#'.$this->_config['ldelime'] .'\*.*\*' . $this->_config['rdelime'] .'#sU', '', $source);

        $source = preg_replace_callback('#'.$this->_config['ldelime']. '.*' . $this->_config['rdelime']. '#smU', array($this, '_tagToken'), $source);

//        vd(htmlspecialchars($source));
        $content .= $source;
        
        return $content;
    }

    private function _tagToken($tag) {
        if (is_array($tag)) $tag = array_pop($tag);
        $res = '';

        $blocks = array();
        if ($tag == '{literal}') {
            $this->_is_literal = true;
            return '';
        } elseif ($tag == '{/literal}') {
            $this->_is_literal = false;
            return '';
        }
        if ($this->_is_literal) return $tag;

        if ($tag[1] == '$') {
            $params = $this->parseParams(mb_substr($tag, 1, -1));
            $res = '';
            $after_var = '';
            $isset_echo = false;
            if (count($params) > 1) {
                foreach($params as $token) {
                    if ($token == '-isset') {
                        $isset_echo = true;
                        continue;
                    }
                    // check for "." in the end
                    if ($token[mb_strlen($token)-1] == '.') {
                        $token  = mb_substr($token, 0, -1);
                        $after_var = ' . ';
                    }
                    if (strpos($token, '$') !== false) {
                        $res .= $this->_replaceVarCallback(mb_substr($token, 1));
                    } else {
                        $res .= $token;
                    }

                    $res .= $after_var;
                    $after_var = '';
                }
                $res = '<?php '. ($isset_echo ? 'if (isset(' .$res.')) ' : '') . 'echo (' . $res  . ');' . ' ?>';
            } else {
                $res = $this->_varToken($tag);
            }
        } elseif ($tag[1] == '/') {
            $res = $this->_blockEndToken($tag);
        } elseif (preg_match('#'.$this->_config['ldelime']. '(' .(implode('|', array_keys($this->_blocks))) . ')(\s+.*)?' .$this->_config['rdelime'].'#Us', $tag, $blocks)) {
            $block_name = $blocks[1];
            $res = $this->_blockBeginToken($tag, $block_name);
        } else {
            $res = $this->_helperToken($tag);
        }

        return $res;
    }

    private function _varToken($tag) {
        $res = '<?php ';
        $var_name = $this->_replaceVarCallback(substr($tag, 2, -1));
        if ($this->_config['escape'] && ($var_name[0] == '$')) {
            $var_value = 'htmlentities('.$var_name.', 2, \'utf-8\')';
        } else {
            $var_value = $var_name;
        }
        if ($this->_config['save_echo'] && ($var_name[0] == '$')) {
            $res .= 'isset('.$var_value.') ? $this->saveEcho('.$var_value.') : ""';
        } else {
            $res .= 'echo '. $var_value;
        }
        $res .= '; ?>';
        return $res;
    }

    private function _blockBeginToken($tag, $block_name) {
        return $this->_blocks[$block_name]->process($tag);
    }

    private function _blockEndToken($tag) {
        $block_name = substr($tag, 2, -1);
        return $this->_blocks[$block_name]->processEnd($tag);
    }

    private function _helperToken($tag) {
        $params = array();
        if (($pos1 = strpos($tag, ' ')) !== false) {
            $params_to_parse = substr($tag, strpos($tag, ' '), -1);
            $params = $this->parseParams($params_to_parse);
            $helper_name = substr($tag, 1, $pos1-1);
        } else {
            $helper_name = substr($tag, 1, -1);
        }
        if (!isset($this->_helpers[$helper_name])) {
            if (is_callable($helper_name)) {
                $res = '<?php echo '.$helper_name . '(';
                if (!empty($params)) {
                    foreach($params as $param) {
                        $res .=  dumpAsString($param) . ',';
                    }
                    $res = substr($res, 0, -1);
                }
                $res .= '); ?>';
                return $res;
            }
            throw new Exception('Helper not found: '.$helper_name);
        }
        $need_invoke = false;
        if ($helper_name == 'use_css' || $helper_name == 'use_js') {
            $need_invoke = true;
        }
        foreach($params as $key=>$value) {
//            vd($value, '!@#');
            $this->replaceVars($value);
//            vd($value);
            if (!$this->_helpers[$helper_name]->isInline()) {
                if (strpos($value, '$') !== false) {
                    $need_invoke = true;
                }
            }
            $params[$key] = $value;
        }
        if ($need_invoke) {
            $res = '<?php $this->invokeHelper(\''.$helper_name.'\', '.dumpAsString($params).'); ?>';
        } else {
            $res = $this->_helpers[$helper_name]->process($params); 
        }
        return $res;
    }

    public function replaceVars(&$source) {
        $source = preg_replace_callback('#\$([\w\.]+)#', array($this, '_replaceVarCallback'), $source);
    }

    private function _replaceVarCallback($tag) {
        if (is_array($tag)) $tag = array_pop($tag);
        $res = '';
        $end = '';
        $modifiers = false;

        if (strpos($tag, '|') !== false) {
            $modifiers = explode('|', $tag);
            $tag = $modifiers[0];
            unset($modifiers[0]);
            array_reverse($modifiers);
        }
        $res .= '$__lv';
        $simple_var = true;

        // if it's inline var in ()
        if (($brace_pos = strpos($tag, ')')) !== false) {
            vd('Unkonown tag:' . $tag);
            $end = substr($tag, $brace_pos);
            $tag = substr($tag, 0, $brace_pos);
            vd($tag, $end);
        }

        if (strpos($tag, '[') !== false) {
            $tag = '$'.$tag;
            $tag = preg_replace('#(\$\w+)\.(\w+)#', '$1[\'$2\']', $tag);

            $tag = substr($tag, 1);
            $simple_var = false;
            $this->replaceVars($tag);

            $brace_pos = strpos($tag, '[');
            $res .= '[\'' . substr($tag, 0, $brace_pos) .'\']' .substr($tag, $brace_pos) . '';
        } elseif (strpos($tag, '.') !== false) {
            $simple_var = false;
            $parts = explode('.', $tag);
            foreach($parts as $sub_var) {
                $res .= '[\''.$sub_var.'\']';
            }
        }

        if ($simple_var) $res .= '[\''.$tag.'\']';

        if ($modifiers) {
            foreach($modifiers as $mod) {
                $params = array();
                if (($i = strpos($mod, ':')) !== false) {
                    $params = explode(':', substr($mod, $i+1));
                    $mod = substr($mod, 0, $i);
                    foreach($params as $key=>$param) {
                        if (strpos($param, '$') !== false) {
                            $params[$key] = $this->_replaceVarCallback(substr($param, 1));
                        }
                    }
                }
                if (function_exists($mod)) {
                    $res = $mod . '(' . $res . ',';
                    foreach($params as $param) {
                        $res .= $param .',';
                    }
                    $res = substr($res, 0, -1);
                    $res .= ')';
                }
            }
        }
        return $res;
    }

    public function parseParams($tag) {
        $params = array();
        /**
         * from=$users item=item
         * from = $users item=item
         * from =$users item=item
         * from = array(1, 2, 3) key = item
         * from = "us er s" item=item
         * from = user item=item
         * $var = ($t < 2 ? 2 : $t)
         */

        $tmp = array();
//        $tag = ' $item._created_at "H:i, d F"';
//        $tag = '"H:i, d F"';
//        {that a=$this->a}



        $pattern =
                '#(\s?(\".*\")?[\w\$\./]*)' // left part

                .'(\s*=\s*
                    (
                    [\w\$\.\[\]\-\>]*  (\(.*\))?  (\".*\")?
                    )
                )?
                #ismx';



        $open_scopes    = array('('=>')', '['=>']');
        $close_scopes   = array(')'=>'(', ']'=>'[');
        $quotes         = array('"', "'");
        $spaces         = array(' ', "\n");
        $pre_skip_characters = array('.');

//        $test_tag = '   from=$users["people"] item=item'; $tag = $test_tag;
//        $test_tag = 'default $user.skype ""'; $tag = $test_tag;
//        $test_tag = ' file="list/_elements/filters/". ( $field_info.type ).".tpl"'; $tag = $test_tag;

//        $tag = $this->_trimAll($tag);
        $tag = trim($tag);
        $length = mb_strlen($tag);
        $current_scope  = null;
        $parse_mode     = 'value';
        $space_mode     = false;
        $skip_all       = false;

        $params         = array();
        $key    = '';
        $value  = '';

        $parts = array();
        $stack              = array();
        $stack_pointer      = -1;
        $gather_value       = false;
        $skip_spaces        = false;

        $quotes_mode        = false;
        $quotes_stack       = array();
        $quotes_pointer     = -1;

        for($i = 0; $i < $length; $i++) {
            $ch = mb_substr($tag, $i, 1);

//                vd($tag, $ch, $i, $skip_spaces, $quotes_mode, '!@#');
//                vd($ch, $quotes_mode, $space_mode, '!@#');
            if (in_array($ch, $spaces)) {
                if ($skip_spaces && !$quotes_mode) continue;

                if (empty($stack) && !$quotes_mode) {
                    if (!$space_mode) {
                        $space_mode = true;
                    } else {

                    }
                    continue;
                }
            }

            if (in_array($ch, $quotes)) {
                if (!empty($quotes_stack) && ($quotes_stack[$quotes_pointer] == $ch)) {
                    unset($quotes_stack[$quotes_pointer]);
                    $quotes_pointer--;
                    $quotes_mode = false;
                } else {
                    $quotes_pointer++;
                    $quotes_stack[$quotes_pointer] = $ch;
                    $quotes_mode = true;
                }
                /**
                 * We cut the quotes from the begin and the end of the parameter
                 */
//                if ($quotes_pointer == 0 || empty($quotes_stack)) continue;
//                vd($i, $ch, $quotes_stack, $quotes_mode, $parts, '!@#');
            }

            if (array_key_exists($ch, $open_scopes) && !$quotes_mode) {
                $stack_pointer++;
                $stack[$stack_pointer] = $ch;
            } elseif(array_key_exists($ch, $close_scopes) && !$quotes_mode) {
                if ($stack_pointer > -1 && ($stack[$stack_pointer] == $close_scopes[$ch])) {
                    unset($stack[$stack_pointer]);
                    $stack_pointer--;
                } else {
//                    die('Syntax error, unexpected "'.$ch.'" in tag '.$tag);
                }
            // if stack not empty - just add space to the current item
            } elseif (empty($stack)) {
                if ($ch == '=') {
                    $key = $value;
                    $value = '';
                    $gather_value = true;
                    $space_mode = false;
                    $skip_spaces = false;
                    continue;
                } elseif(in_array($ch, $pre_skip_characters)) {
                    $skip_spaces = true;
                } else {

                    $skip_spaces = false;
                    if ($space_mode) {

                        if (!empty($value)) {
                            if ($gather_value) {
                                if (!empty($key)) {
                                    $params[$key] = $this->_trimAll($value);
                                } else {
                                    $params[] = $this->_trimAll($value);
                                }
                                $value = '';
                                $key = '';
                            } else {
                                $params[] = $this->_trimAll($value);
                                $value = '';
                                $key = '';
                            }

                        }
                        $space_mode = false;
                    }
                }
            }
            $$parse_mode .= $ch;
        }
        if (!empty($value)) {
            $value = $this->_trimAll($value);
            $key ? $params[$key] = $value : $params[] = $value;
        }

        if (isset($test_tag)) vd( $key, $value, $params);










/************** version with space_mode fails on "   from = $users" ****
        for($i = 0; $i < $length; $i++) {
            $ch = mb_substr($tag, $i, 1);

//            while (in_array($ch, $spaces) && empty($stack)) {
//                $i++;
//                if ($i = $length) break;
//                $ch = mb_substr($tag, $i, 1);
//            }


            if (array_key_exists($ch, $open_scopes)) {
                $stack_pointer++;
                $stack[$stack_pointer] = $ch;
            } elseif(array_key_exists($ch, $close_scopes)) {
                if ($stack_pointer > -1 && ($stack[$stack_pointer] == $close_scopes[$ch])) {
                    unset($stack[$stack_pointer]);
                    $stack_pointer--;
                } else {
                    die('Syntax error, unexpected '.$ch.'.');
                }
            // if stack not empty - just add space to the current item
            } elseif (empty($stack)) {
                if (in_array($ch, $spaces)) {
                    // skip all spaces
                    // need new var $s_ch (space char) for do not touching our $ch
                    $s_ch = $ch;
                    $j = $i;
                    while (in_array($s_ch, $spaces)) {
                        $j++;
                        if ($j > $length) break;
                        $s_ch = mb_substr($tag, $j, 1);
                    }
                    $i = $j-1;
                    $space_mode = true;
                } else {
                    $s_ch = $i+1 < $length ? mb_substr($tag, $i+1, 1) : null;
                }
                // need separate if for process "=" after spaces
                if ($s_ch == "=") {
                    $s_ch = '';
                    $space_mode = false;
                    if (empty($key)) {
                        $key = $value . (in_array($ch, $spaces) ? '' : $ch);
                        $value = '';
                        $i++;
                        continue;
                    } else {
                        $params[$key] = $value;
                        $key = '';
                        $value = '';
                        continue;
                    }
                } elseif ($space_mode) {
                    $space_mode = false;
                    $params[$key] = $value;
                    $key = '';
                    $value = '';

                    continue;
                }

            } else {

            }
            $$parse_mode .= $ch;
        }
        if (!empty($value)) {
            $key ? $params[$key] = $value: $params[] = $value;
        }

        vd($tag, $params, $stack, 'key:['.$key.']', 'value:['.$value.']', '----------');
*/
        preg_match_all($pattern, $tag, $tmp);
        //        preg_match_all('#((\".*\")?[\w\$\./]*)?#ism', $tag, $tmp);
//        preg_match_all('#[\w\$\./]+(\s*=\s*[\w\$\.]*(\(.*\))?(\".*\")?(\\\'.*\\\')?)?#ism', $tag, $tmp);
//        vd($tmp);
/*********************** PREG_MATH FOREACH **********************
        foreach($tmp[0] as $key=>$item) {
            if (isset($tmp[1][$key])) {
                if (!empty($tmp[4][$key])) {
                    $params[trim($tmp[1][$key])] = $tmp[4][$key];
                } else {
                    $val = trim($tmp[1][$key]);
                    if (!$val) continue;
                    if (($val[0] == '"') && ($val[strlen($val)-1] == '"')) {
                        $val = substr($val, 1, -1);
                    }
                    $params[] = $val;
                }
            } else {
                $params[] = $item;
            }
//            $item = trim($item);
//            if (isset($tmp[1]))
//            if (strpos($item, '=') !== false) {
//                $item = explode('=', $item);
//                $val = trim($item[1]);
//                if (($val[0] == '"') && ($val[strlen($val)-1] == '"')) {
//                    $val = substr($val, 1, -1);
//                }
//                $params[trim($item[0])] = $val;
//            } else {
//                $params[] = $item;
//            }
        }
*/
        return $params;
    }

    private function _trimAll($tag) {
        $tag = trim($tag);
        $lenght = mb_strlen($tag);
        $start = mb_substr($tag, 0, 1);
        $end = mb_substr($tag, $lenght-1, 1);
        if (($start == $end) && (in_array($start, array('"', "'")))) {
            $tag = mb_substr($tag, 1, $lenght-2);
        }
        return $tag;
    }

    public function invokeHelper($helper, $params = array()) {
        echo $this->_helpers[$helper]->process($params);
    }

    public function saveEcho($var_value) {
        echo is_bool($var_value) ? ($var_value ? "true" : "false") : $var_value;
    }

}