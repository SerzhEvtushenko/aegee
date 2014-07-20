<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 04.07.12 16:38
 */
/**
 * Multi language opertor for whole system
 * Operate with global language switching and force switch in components such as Model etc.
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class MLT {

    static private $_session_storage = null;

    static private $_models_auto_translate = false;

    static public function initialize() {
        if ($data = Sl::getProjectConfig('mlt')) {

            if (is_null(self::$_session_storage)) {
                if (!isset($_SESSION['__mlt'])) {
                    $_SESSION['__mlt'] = array(
                        'default_language'      => empty($data['default']) ? $data['languages'][0] : $data['default'],
                        'languages'             => $data['languages']
                    );
                    $_SESSION['__mlt']['active_language'] = $_SESSION['__mlt']['default_language'];
                }

                self::$_session_storage = &$_SESSION['__mlt'];
                self::$_models_auto_translate = isset($data['models_auto_translate']) ? $data['models_auto_translate'] : true;

            }

        }
    }

    static public function getLanguagesAliases() {
        return self::$_session_storage['languages'];
    }

    static public function setActiveLanguage($lang) {
        if (!in_array($lang, self::$_session_storage['languages'])) {
            throw new slBaseException('Trying to set undefined language: '.$lang);
        }
        self::$_session_storage['active_language'] = $lang;
    }

    static public function getActiveLanguage() {
        return self::$_session_storage['active_language'];
    }

    static public function isLanguageAvailable($langs) {
        if (!is_array($langs)) {
            $langs = array($langs);
        }
        foreach($langs as $lang) {
            if (!in_array($lang, self::$_session_storage['languages'])) return false;
        }
        return true;
    }

    static public function setModelsAutoTranslate($status) {
        self::$_models_auto_translate = $status;
    }

    static public function getModelsAutoTranslate() {
        return self::$_models_auto_translate;
    }

    static public function prepareMLTTemplatesCache($from) {
        $cache_dir      = self::getTemplatesCacheDir();
        slLocator::makeWritable($cache_dir);

        $files          = slLocator::getInstance()->in($from, true)->find();
        foreach($files as $key=>$file) {
            $new_name = substr($file, strlen($from)+1);
            if (is_dir($file)) {
                slLocator::makeWritable($cache_dir . $new_name);
            } else {
                file_put_contents($cache_dir . $new_name, self::processTemplate(file_get_contents($file)));
            }
        }
    }

    static public function processTemplate($content) {
        $content = preg_replace_callback('#\{\{.*\}\}#isU', array('MLT', 'onMltToken'), $content);
        return $content;
    }

    static public function onMltToken($replace) {
        $tag = substr($replace[0], 2, -2);
        return MltPhrase::getTranslate($tag, MLT::getActiveLanguage());
    }

    static public function getTemplatesCacheDir() {
        return SL::getDirCache() . 'templates/'  . slRouter::getCurrentApplicationName() . '/' . self::getActiveLanguage() . '/';
    }

}
