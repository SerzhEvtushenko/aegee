<?php
/*
 * @package SolveProject
 * @subpackage Debug
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 25.10.2009 21:06:22
 */

/**
 * Operate with logs
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slLogger {

    /**
     * @var array contains all logs for current user request
     */
    private $_logs = array();

    /**
     * @var array data that was flushed to the disk
     */
    private $_flushed = array();

    /**
     * Add message to log
     *
     * @param $message
     * @param string $namespace
     * @return slLogger
     */
    public function add($message, $namespace = slLoggerNamespace::USER_NAMESPACE) {
        if (empty($this->_logs[$namespace])) $this->_logs[$namespace] = array();
        $this->_logs[$namespace][] = array(
            'datetime'  => date('d.m.Y H:i:s'),
            'message'   => $message,
            'namespace' => $namespace
        );
        return $this;
    }

    /**
     * Flush buffer to the files on disk
     * @param mixed $namespaces
     */
    public function flushBuffer($namespaces = null) {
        if ($namespaces) {
            if (!is_array($namespaces)) {
                $namespaces = explode(',', $namespaces);
            }
        } else {
            $namespaces = array_keys($this->_logs);
        }
        $log_folder = SL::getDirLog();
        if (!is_dir($log_folder)) {
            mkdir($log_folder, 0777, true);
            chmod($log_folder, 0777);
        }
        foreach($namespaces as $namespace) {
            $h = fopen($log_folder . $namespace . '.txt', 'a+');

            if (empty($this->_flushed[$namespace])) $this->_flushed[$namespace] = array();

            foreach($this->_logs[$namespace] as $s) {
                $this->_flushed[$namespace][] = $s;
                fputs($h, $s['datetime']. ' ' . $s['message'] . "\n");
            }

            $this->_logs[$namespace] = array();

            fclose($h);
        }
    }

    /**
     * Return logs of specified namespace
     * @param string $namespace
     * @return array
     */
    public function get($namespace) {
        return !empty($this->_logs[$namespace]) ? $this->_logs[$namespace] : array();
    }

    /**
     * Return combined all messages for current process
     * @param string $namespace
     * @param string $search
     * @return array
     */
    public function getAll($namespace = null, $search = null) {
        $data = array();
        if ($namespace) {
            $storages = array($this->_logs[$namespace]);
        } else {
            $storages = $this->_logs;
        }

        foreach($storages as $logs) {
            foreach($logs as $log) {
                if (!$search || (strpos($log['message'], $search) !== false)) {
                    $data[] = $log;
                }
            }
        }

        usort($data, array($this, 'sortLog'));
        return $data;
    }

    /**
     * flush buffers to the disk
     */
    public function __destruct() {
        $this->flushBuffer();
    }

    /**
     * internal sort function
     * @param $a
     * @param $b
     * @return int
     */
    private function sortLog($a, $b) {
        return (strcmp($a, $b));
    }

}
