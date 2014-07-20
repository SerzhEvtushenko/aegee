<?php
/**
 * @package SolveProject
 * @subpackage Debug
 * created 30.10.2009 14:04:09
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Debug tool
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slDebug {

     const
       LEVEL_NONE       = 'debug_level_none',
       LEVEL_ALL        = 'debug_level_all';

     private static $level           = self::LEVEL_NONE;

    /**
     * Print exception information and backtrace
     * @param $e Exception
     */
    static public function exceptionHandler(Exception $e) {
        if (is_callable(array($e, 'postAction'))) {
            $e->postAction($e->getMessage(), $e->getCode());
        }
        if (!SL::getProjectConfig('dev_mode')) {
            SL::log('Excpetion: ' . $e->getMessage(), slLoggerNamespace::SYSTEM_NAMESPACE);
            die();
        }
        if (slRouter::getCurrentMode() == slRouter::MODE_WEB) {
            echo '<pre>';
            echo '<div style="background:#c00;color:white;font-weight:bold;padding:5px;text-align:center;">'.get_class($e).'::' . $e->getMessage() . '</div>';
            echo $e->getTraceAsString();
            echo '</pre>';
        } else {
            echo "\n".'Exception::'.$e->getMessage()."\n";
        }
        die();
    }

    static public function errorHandler($code, $message, $file, $line, $context) {
        if (SL::getProjectConfig('dev_mode')) {
            echo "<pre>";
            echo 'E#  line '.$line.': '. $message . ' in '.$file.'<br/><br/>';
            debug_print_backtrace();
            die();
        } else {
            SL::log('E#'.$code.' '. $message . ' in '.$file.' on line '.$line);
        }
    }

    /**
     * Print dev toolbar to browser if need
     * @static
     */
    static public function showDevTools() {
        $html = <<<TEXT
</pre>
<div id="dev-tools-wrapper" style="position:fixed;top:0;right:0;background:#333 url(/images/dev_tools/timer.png) no-repeat 4px 5px;color:#eee;padding:2px 5px 5px 20px;font-size:12px;cursor:pointer;width:40px;z-index:10000;">__LOAD__</div>
<div id="dev-tools-bar" style="position:fixed;display:none;top:0;right:0;background:#333;color:#999;font-size:12px;text-shadow:none !important;padding:5px;z-index:10000;">
    <div id="loading-indicator" style="position: absolute; right: 52px; visibility: hidden">Loading...</div>
    <div style="float:right;font-size:10px;text-underline:true;cursor:pointer;margin-left:50px;" id="dev-tools-close-button">close</div>
    <div style="float:left;cursor:pointer;color:#666;margin-left:10px;" id="switcher-console">Console</div>
    <div style="float:left;cursor:pointer;color:#666;margin-left:10px;" id="switcher-cp">CP</div>
    <div style="float:left;cursor:pointer;margin-left:10px;" id="switcher-db">DB</div>
    <div style="float:left;cursor:pointer;color:#666;margin-left:10px;" id="switcher-log">Logs</div>
    <div style="clear:both;"></div>
    <div style="width:550px;margin-top:10px;">
        <div id="container-cp" style="display:none;">__CP__</div>
        <div id="container-log" style="display:none;">__LOG__</div>
        <div id="container-console" style="display:none;">
            <div id="console-display" style="height:250px;border:1px solid #555;color:#666;padding:5px;overflow:auto;font-family:monospace"></div>
            <div id="console-input-wrapper" style="margin-top:5px;">
                <input type="text" id="console-input" style="width:540px;background:#333;border:1px solid #555;color:#666;padding:5px;"/>
            </div>
        </div>
        <div id="container-db">__DB__</div>
    </div>
</div>
TEXT;
        $html = str_replace('__LOAD__', substr(slProfiler::getTotalExecutionTime(), 0, 7), $html);
        $html = str_replace('__DB__', nl2br(slProfiler::getHtmlTimers('db')), $html);
        $html = str_replace('__CP__', slProfiler::getHtmlCheckpoints(), $html);
        echo $html;
        echo '<script type="text/javascript" src="/js/dev_tools/dev_tools.js"></script>';
    }

}
