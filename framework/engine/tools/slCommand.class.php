<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rekvizit
 * Date: 26/02/2013
 * Time: 00:13
 * To change this template use File | Settings | File Templates.
 */

class slCommand {

    private $command = '';
    public $output = null;
    public $return_value = null;

    private $is_executed = false;

    static private $is_exec_enabled = null;
    static private function isExecEnabled() {
        if (!is_null(self::$is_exec_enabled)) return self::$is_exec_enabled;
        $disabled_functions = explode(', ', ini_get('disable_functions'));
        return self::$is_exec_enabled = !in_array('exec', $disabled_functions);
    }

    public function __construct($command) {
        $this->command = $command;
    }

    public function exec() {
        if ($this->is_executed) return $this;

        if (!self::isExecEnabled()) {
            throw new Exception('exec() function is disabled');
        }

        exec($this->command, $this->output, $this->return_value);

        if ($this->return_value != 0) {
            $message = ($this->return_value == 127)
                ? 'Command "'.$this->command.'" was not found'
                : 'Some error occurred executing "'.$this->command.'"';
            throw new Exception($message);
        }

        $this->is_executed = true;

        return $this;
    }

    static public function execute($command) {
        $cmd = new self($command);
        return $cmd->exec();
    }

}