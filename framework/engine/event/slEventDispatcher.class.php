<?php
/**
 * @package SolveProject
 * @subpackage Event
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 25.11.2009 13:13:49
 */

/**
 * Global Event Dispatcher. Operate with events and listeners
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slEventDispatcher {

    /**
     * @var array of listeners per event
     */
    static private $listeners = array();

    /**
     * Private constructor for prevent object creating
     * @access private
     * @return void
     */
    private function __construct() {
    }

    /**
     * Attach EventListener to listen event with name $name
     * @static
     * @param string Name of event listen for
     * @param callable $listener Listener for event
     * @return void
     */
    static public function addEventListener($eventName, $listener) {
        if (!isset(self::$listeners[$eventName])) self::$listeners[$eventName] = array();
        array_unshift(self::$listeners[$eventName], $listener);
    }

    /**
     * Detach slEvent listener from event with name $name , of from All events if true specified as name
     * @static
     * @param string $eventName slEvent name for remove or true if all
     * @param callable $listener
     * @return bool is listener was found
     */
    static public function removeEventListener($eventName, $listener) {
        if (TRUE !== $eventName) {
            foreach(self::$listeners[$eventName] as $key=>$item) {
                if ($item == $listener) {
                    unset(self::$listeners[$eventName][$key]);
                    return true;
                }
            }
            return false;
        }
        foreach(self::$listeners as $il=>$list) {
            foreach($list as $ii=>$item) {
                if ($item == $listener) {
                    unset(self::$listeners[$il][$ii]);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Return true if event has listeners
     * @static
     * @param string $eventName
     * @return boolean true if any listeners for event with specified name
     */
    static public function hasListeners($eventName) {
        return !empty(self::$listeners[$eventName]);
    }

    /**
     * Remove all listeners for specified event
     * @static
     * @param $eventName
     * @return bool
     */
    static public function removeListeners($eventName) {
        if (!self::hasListeners($eventName)) return false;
    }

    /**
     * Dispatch event to all listeners for this event name
     * Break cycle if $event->stopPropagation() was called in any listener
     *
     * @static
     * @param slEvent $event
     * @return boolean false if no listeners found, true if one or more listeners called
     */
    static public function dispatchEvent(slEvent &$event) {
        if (!self::hasListeners($event->getName())) return false;

        $event->setState(slEventState::FIRED);
        foreach(self::$listeners[$event->getName()] as $listener) {
            if (is_array($listener)) {
                call_user_func($listener, $event);
            } else {
                $method = 'on'.slInflector::camelCase($event->getName());
                if (method_exists($listener,$method)) {
                    $listener->$method($event);
                }
            }
            if ($event->isStopped()) return false;

        }
        $event->setState(slEventState::FINISHED);
        return true;
    }

}