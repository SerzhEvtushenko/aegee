<?php
/**
 * @package SolveProject
 * @subpackage View
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 21.01.12, 23:58
 */

/**
 * Profile your application
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slProfiler {

    /**
     * @var array store user checkpoints
     */
    static private $_checkpoints        = array();

    /**
     * @var array store user timers
     */
    static private $_timers             = array();

    /**
     * Register checkpoint
     * @static
     * @param string $name for checkpoint
     */
    static public function checkPoint($name = null) {
        self::$_checkpoints[] = array(
            'name'      => $name ? $name : 'checkpoint',
            'started'   => microtime(true)
        );
    }

    /**
     * Register timer start in some category
     * @static
     * @param string $name timer name
     * @param string $category
     */
    static public function startTimer($name, $category = 'default') {
        self::$_timers[$category][$name] = array(
            'started'           => microtime(true),
            'duration'          => 0,
            'name'              => $name,
            'paused'            => false,
            'paused_duration'   => 0,
            'is_active'         => true
        );
    }

    /**
     * Pause specified timer
     * @static
     * @param $name
     * @param string $category
     */
    static public function pauseTimer($name, $category = 'default') {
        self::$_timers[$category][$name]['paused'] = microtime(true);
    }

    /**
     * Resume specified timer
     * @static
     * @param $name
     * @param string $category
     * @throws Exception
     */
    static public function resumeTimer($name, $category = 'default') {
        if (!self::$_timers[$category][$name]['paused']) {
            throw new Exception('Timer '.$name.' in category '.$category.' is not paused');
        }
        self::$_timers[$category][$name]['paused_duration'] = microtime(true) - self::$_timers[$category][$name]['paused'];
        self::$_timers[$category][$name]['paused'] = false;
    }

    /**
     * Stop timer and save its duration
     * @static
     * @param string $name
     * @param string $category
     * @return bool
     */
    static public function stopTimer($name, $category = 'default') {
        if (!isset(self::$_timers[$category][$name]) || !self::$_timers[$category][$name]['is_active']) return false;
        
        self::$_timers[$category][$name]['duration'] = microtime(true) - self::$_timers[$category][$name]['started'] - self::$_timers[$category][$name]['paused_duration'];
        self::$_timers[$category][$name]['is_active'] = false;
    }

    /**
     * Return all timers from specified category
     * @static
     * @param string $category
     * @return array
     */
    static public function getTimers($category = 'default') {
        return $category ? (isset(self::$_timers[$category]) ? self::$_timers[$category] : array()) : self::$_timers;
    }

    /**
     * Return all checkpoints
     * @static
     * @return array
     */
    static public function getCheckPoints() {
        return self::$_checkpoints;
    }

    /**
     * Return difference between first and last checkpoints
     * @static
     * @return mixed
     */
    static public function getTotalExecutionTime() {
        return self::$_checkpoints[count(self::$_checkpoints) - 1]['started'] - self::$_checkpoints[0]['started'];
    }

    /**
     * Return HTML report for timers
     * @static
     * @param string $category
     * @return string
     */
    static public function getHtmlTimers($category = 'default') {
        $timers = self::getTimers($category);
        $res = '';
        $i = 0;
        $total = 0;
        foreach($timers as $timer) {
           $res .= round($timer['duration'], 6)
                   . ' | '.$timer['name']
                   . "\n";
            $total += $timer['duration'];
        }

        $res .= "\n" . 'Total: '.substr($total, 0, 7). "\n";
        
        return $res;
        
        $res = <<<TEXT
<table style="border:1px solid #555;width:100%;color:#999;font-size:11px;" rules="all">
<tr>
<th>&nbsp;N&nbsp;</th><th>started</th><th>query</th><th>duration</th>
</tr>
TEXT;
        $i = 0;
        $total = 0;
        foreach($timers as $timer) {
            $dt = date('H:i:s', $timer['started']);
            $dt .= '.'.substr(($timer['started'] - strtotime($dt)), 2, 5) . '';

            $res .= '<tr>';
            $res .= '<td>'.(++$i).'</td>'
                    .'<td>'.$dt.'</td>'
                    .'<td>'.$timer['name'].'</td>'
                    .'<td>'.round($timer['duration'], 6).'</td>';
            $res .= '</tr>';
            $total += $timer['duration'];
        }

        $res .= '<tr><td colspan="3"><b>Total:</b></td><td><b>'.substr($total, 0, 7).'</b></td></tr></table>';
        return $res;
    }

    /**
     * Return HTML report for checkpoints
     * @static
     * @return string
     */
    static public function getHtmlCheckpoints() {
        $res = <<<TEXT
<table style="border:1px solid #555;width:100%;color:#999;font-size:11px;" rules="all">
<tr>
<th>&nbsp;N&nbsp;</th><th>started</th><th>checkpoint</th><th>from prev check</th>
</tr>
TEXT;
        $i = 0;
        $total = 0;
        $tmp = array_keys(self::$_checkpoints);
        $old_time = self::$_checkpoints[$tmp[0]]['started'];

        foreach(self::$_checkpoints as $info) {
            $duration = $info['started'] - $old_time;
            $dt = date('H:i:s', $info['started']);
            $dt .= '.'.substr(($info['started'] - strtotime($dt)), 2, 5) . '';
            $res .= '<tr>';
            $res .= '<td>'.(++$i).'</td>'
                    .'<td>'.($dt) .'</td>'
                    .'<td>'.$info['name'].'</td>'
                    .'<td>'.round($duration, 6).'</td>';
            $res .= '</tr>';
            $total += $duration;
            $old_time = $info['started'];
        }

        $res .= '<tr><td colspan="3"><b>Total:</b></td><td><b>'.substr($total, 0, 7).'</b></td></tr></table>';
        return $res;
    }


}
