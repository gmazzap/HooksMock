<?php
/*
 * This file is part of the HooksMock package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\HooksMock;

use InvalidArgumentException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package HooksMock
 */
class HooksMock
{
    static $hooks = ['actions' => [], 'filters' => []];
    static $hooks_done = ['actions' => [], 'filters' => []];

    /**
     * Reset static arrays
     */
    public static function tearDown()
    {
        static::$hooks = ['actions' => [], 'filters' => []];
        static::$hooks_done = ['actions' => [], 'filters' => []];
    }

    /**
     * Emulate add_action() and add_filter() depending on $type param.
     *
     * @param  string                    $type Type of the hook, 'action' or 'filter'
     * @param  array                     $args Arguments passed to add_action() or add_filter()
     * @throws \InvalidArgumentException
     * @return void
     */
    public static function addHook($type = '', array $args = [])
    {
        if (! in_array($type, ['action', 'filter'], true)) {
            $type = 'action';
        }
        $target = $type === 'filter' ? 'filters' : 'actions';
        $hook = array_shift($args);
        if (empty($hook) || ! is_string($hook)) {
            $msg = ' Error on adding '.$type.': invalid hook';
            throw new InvalidArgumentException($msg);
        }
        $cb = array_shift($args);
        if (! is_callable($cb)) {
            $msg = ' Error on adding '.$type.': given callback for the hook '.$hook
                .' is not a valid callback.';
            throw new InvalidArgumentException($msg);
        }
        $priority = array_shift($args) ?: 10;
        $num_args = array_shift($args) ?: 1;
        if (! isset(HooksMock::$hooks[$target][$hook])) {
            static::$hooks[$target][$hook] = [];
        }
        if (! isset(static::$hooks[$target][$hook][$priority])) {
            static::$hooks[$target][$hook][$priority] = [];
        }
        $id = static::callbackUniqueId($cb);
        static::$hooks[$target][$hook][$priority][$id] = ['cb' => $cb, 'num_args' => $num_args];
    }

    /**
     * Emulate remove_action() or remove_filter() depending on $type param.
     *
     * @param  string                    $type Type of the hook, 'action' or 'filter'
     * @param  array                     $args Arguments passed to remove_action() or remove_filter()
     * @throws \InvalidArgumentException
     * @return void
     */
    public static function removeHook($type = '', array $args = [])
    {
        if (! in_array($type, ['action', 'filter'], true)) {
            $type = 'action';
        }
        $target = $type === 'filter' ? 'filters' : 'actions';
        $hook = array_shift($args);
        if (empty($hook) || ! is_string($hook)) {
            $msg = ' Error on removing '.$type.': invalid hook';
            throw new InvalidArgumentException($msg);
        }
        $cb = array_shift($args);
        if (! is_callable($cb)) {
            $msg = ' Error on removing '.$type.': given callback for the hook '.$hook
                .' is not a valid callback.';
            throw new InvalidArgumentException($msg);
        }
        $id = static::callbackUniqueId($cb);
        $priority = array_shift($args) ?: 10;
        $num_args = ! empty($args) ? array_shift($args) : -1;
        if (! array_key_exists($hook, HooksMock::$hooks[$target])) {
            return;
        }
        if (! array_key_exists($priority, HooksMock::$hooks[$target][$hook])) {
            return;
        }
        if (array_key_exists($id, HooksMock::$hooks[$target][$hook][$priority])) {
            $data = HooksMock::$hooks[$target][$hook][$priority][$id];
            $data['num_args'] = isset($data['num_args']) ? (int) $data['num_args'] : 1;
            if ((int) $num_args > 0 && $data['num_args'] !== (int) $num_args) {
                return;
            }
            unset(HooksMock::$hooks[$target][$hook][$priority][$id]);
            if (empty(HooksMock::$hooks[$target][$hook][$priority])) {
                unset(HooksMock::$hooks[$target][$hook][$priority]);
            }
            if (empty(HooksMock::$hooks[$target][$hook])) {
                unset(HooksMock::$hooks[$target][$hook]);
            }
        }
    }

    /**
     * Emulate do_action() or apply_filters() depending on $type param.
     *
     * @param  string                    $type Type of the hook, 'action' or 'filter'
     * @param  array                     $args Arguments passed to do_action() or apply_filters()
     * @throws \InvalidArgumentException
     * @return array                     3 items array, 1st is the type, 2nd the hook fired, 3rd
     *                                        the arguments
     */
    public static function fireHook($type = '', array $args = [])
    {
        if (! in_array($type, ['action', 'filter'], true)) {
            $type = 'action';
        }
        $target = $type === 'filter' ? 'filters' : 'actions';
        if (empty($args) || ! is_array($args)) {
            $msg = ' Error on adding '.$type.': invalid arguments.';
            throw new InvalidArgumentException($msg);
        }
        $args = array_values($args);
        $hook = array_shift($args);
        if (empty($hook) || ! is_string($hook)) {
            $msg = ' Error on adding '.$type.': invalid hook';
            throw new InvalidArgumentException($msg);
        }
        static::$hooks_done[$target][$hook][] = $args;

        return [$type, $hook, $args];
    }

    /**
     * Check if an action hook is added. Optionally check a specific callback and and priority.
     *
     * @param  string   $hook     Hook to check
     * @param  callable $callback Callback to check
     * @param  int      $priority Priority to check
     * @return boolean
     */
    public static function hasAction($hook = '', callable $callback = null, $priority = null)
    {
        return static::hasHook('action', $hook, $callback, $priority);
    }

    /**
     * Check if an filter hook is added. Optionally check a specific callback and and priority.
     *
     * @param  string   $hook     Hook to check
     * @param  callable $callback Callback to check
     * @param  int      $priority Priority to check
     * @return boolean
     */
    public static function hasFilter($hook = '', callable $callback = null, $priority = null)
    {
        return static::hasHook('filter', $hook, $callback, $priority);
    }

    /**
     * Check if an hook is was fired.
     *
     * @param  string  $type Type of hook, 'action' or 'filter'
     * @param  string  $hook Filter hook to check
     * @return boolean
     */
    public static function hasHookFired($type = 'action', $hook = null)
    {
        if (! in_array($type, ['action', 'filter'], true)) {
            $type = 'action';
        }
        $target = $type === 'filter' ? 'filters' : 'actions';
        if (empty($hook) || ! is_string($hook)) {
            $msg = __METHOD__.' needs a valid hook to check.';
            throw new InvalidArgumentException($msg);
        }

        return array_key_exists($hook, static::$hooks_done[$target]);
    }

    /**
     * Check if an action hook is was fired.
     *
     * @param  string  $hook Filter hook to check
     * @return boolean
     */
    public static function hasActionFired($hook = null)
    {
        return static::hasHookFired('action', $hook);
    }

    /**
     * Check if a filter hook is was fired.
     *
     * @param  string  $hook Filter hook to check
     * @return boolean
     */
    public static function hasFilterFired($hook = null)
    {
        return static::hasHookFired('filter', $hook);
    }

    /**
     * Check if an action is added and throws a exceptions otherwise.
     *
     * @param string   $hook   Action hook to check
     * @param callable $cb     Callback to check
     * @param int      $pri    Priority to check
     * @param int      $n_args Number of accepted arguments to check
     */
    public static function assertActionAdded($hook = '', $cb = null, $pri = null, $n_args = null)
    {
        static::assertHookAdded('action', $hook, $cb, $pri, $n_args);
    }

    /**
     * Check if a filter is added and throws an exceptions otherwise.
     *
     * @param string   $hook     Filter hook to check
     * @param callable $callback Callback to check
     * @param int      $priority Priority to check
     * @param int      $numArgs  Number of accepted arguments to check
     */
    public static function assertFilterAdded(
        $hook = '',
        callable $callback = null,
        $priority = null,
        $numArgs = null
    ) {
        static::assertHookAdded('filter', $hook, $callback, $priority, $numArgs);
    }

    /**
     * Check if an action was fired. Optionally checks if given callback was fired on given action.
     * Throws an exception if assertion is wrong.
     *
     * @param string $hook Action hook to check
     * @param array  $args Arguments to check
     */
    public static function assertActionFired($hook = null, array $args = [])
    {
        static::assertHookFired('action', $hook, $args);
    }

    /**
     * Check if a filter was fired. Optionally checks if given callback was fired on given filter.
     * Throws an exception if assertion is wrong.
     *
     * @param string $hook Filter hook to check
     * @param array  $args Arguments to check
     */
    public static function assertFilterFired($hook = null, $args = [])
    {
        static::assertHookFired('filter', $hook, $args);
    }

    /**
     * Equivalent to _wp_filter_build_unique_id() generate an unique id for a given callback
     *
     * @param  callable                  $callback Callback to generate the unique id from
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function callbackUniqueId(callable $callback = null)
    {
        if (is_string($callback)) {
            return $callback;
        }
        if (is_object($callback)) {
            $callback = [$callback, ''];
        } else {
            $callback = (array) $callback;
        }
        if (is_object($callback[0])) {
            return spl_object_hash($callback[0]).$callback[1];
        } else {
            if (is_string($callback[0])) {
                return $callback[0].'::'.$callback[1];
            }
        }
    }

    /**
     * @param  string                    $type     Type of hook, 'action' or 'filter'
     * @param  string                    $hook     Hook to check
     * @param  callable                  $callback Callback to check
     * @param  int                       $priority Priority to check
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public static function hasHook($type = '', $hook = '', $callback = null, $priority = null)
    {
        if (! in_array($type, ['action', 'filter'], true)) {
            $type = 'action';
        }
        $target = $type === 'filter' ? 'filters' : 'actions';
        if (empty($hook) || ! is_string($hook)) {
            $msg = ' Error on checking '.$type.': invalid hook';
            throw new InvalidArgumentException($msg);
        }
        $id = "{$hook} {$type}";
        if (! is_null($callback) && ! is_callable($callback)) {
            $msg = ' Error on checking '.$id.': the one given is not a valid callback.';
            throw new InvalidArgumentException($msg);
        }
        if (! is_null($priority) && (! is_numeric($priority) || (int) $priority < 0)) {
            $msg = ' Error on checking '.$id.': the one given is not a valid prioriry.';
            throw new InvalidArgumentException($msg);
        }
        if (! array_key_exists($hook, static::$hooks[$target])) {
            return false;
        }
        if (is_null($callback)) {
            return true;
        }
        $hooks = static::$hooks[$target][$hook];
        $callbackId = static::callbackUniqueId($callback);
        if (! is_null($priority)) {
            return
                array_key_exists($priority, $hooks)
                && array_key_exists($callbackId, $hooks[$priority]);
        }
        foreach ($hooks as $priority => $callbackData) {
            foreach ($callbackData as $_callbackId => $_callbackData) {
                if ($_callbackId === $callbackId && isset($_callbackData['cb'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  string                    $t  Type of hook, 'action' or 'filter'
     * @param  string                    $h  Action hook to check
     * @param  callable                  $cb Callback to check
     * @param  int                       $p  Priority to check
     * @param  int                       $n  Number of accepted arguments to check
     * @throws \InvalidArgumentException
     * @access protected
     */
    public static function assertHookAdded($t = '', $h = '', $cb = null, $p = null, $n = null)
    {
        if (! in_array($t, ['action', 'filter'], true)) {
            $t = 'action';
        }
        $target = $t === 'filter' ? 'filters' : 'actions';
        if (empty($h) || ! is_string($h)) {
            $msg = __METHOD__.' needs a valid hook to check.';
            throw new InvalidArgumentException($msg);
        }
        $id = "{$h} {$t}";
        if (! is_callable($cb, true)) {
            $msg = 'Use a valid callback to check for '.$id.'.';
            throw new InvalidArgumentException($msg);
        }
        if (! array_key_exists($h, static::$hooks[$target])) {
            $msg = $h.'is not a valid '.$t.' added.';
            throw new HookException($msg);
        }
        $hooks = static::$hooks[$target][$h];
        if (! is_null($p) && ! is_numeric($p)) {
            $msg = $p.'Not numeric priority to check for '.$id;
            throw new InvalidArgumentException($msg);
        }
        if (! is_null($n) && ! is_numeric($n)) {
            $msg = $n.'Not numeric accepted args num to check for '.$id;
            throw new HookException($msg);
        }
        $priority = (int) $p ?: 10;
        $num_args = (int) $n ?: 1;
        if (! isset($hooks[$priority])) {
            $msg = 'Non valid priority '.$priority.' for '.$id;
            if (is_null($p)) {
                $msg = '. Be sure to pass exact priority to '.__METHOD__;
            }
            throw new HookException($msg);
        }
        $callbackId = static::callbackUniqueId($cb);
        if (! array_key_exists($callbackId, (array) $hooks[$priority])) {
            $msg = $n.'Wrong callback for '.$id.' at priority '.$priority;
            throw new HookException($msg);
        }
        if (is_null($n)) {
            return;
        }
        $set_num_args = isset($hooks[$priority][$callbackId]['num_args']);
        if (! $set_num_args || $num_args !== (int) $hooks[$priority][$callbackId]['num_args']) {
            $msg = $num_args.' is a wrong accepted args num for given callback on the '.$id;
            throw new HookException($msg);
        }
    }

    /**
     * @param  string                    $type Type of hook, 'action' or 'filter'
     * @param  string                    $hook Filter hook to check
     * @param  callable                  $args Arguments to check
     * @throws \InvalidArgumentException
     * @access protected
     */
    public static function assertHookFired($type = 'action', $hook = null, $args = null)
    {
        if (! in_array($type, ['action', 'filter'], true)) {
            $type = 'action';
        }
        $target = $type === 'filter' ? 'filters' : 'actions';
        if (empty($hook) || ! is_string($hook)) {
            $msg = __METHOD__.' needs a valid hook to check.';
            throw new InvalidArgumentException($msg);
        }
        $id = "{$hook} {$type}";
        if (! is_null($args) && ! is_array($args)) {
            $msg = 'Invalid arguments to check for fired '.$id.'.';
            throw new InvalidArgumentException($msg);
        }
        if (! array_key_exists($hook, static::$hooks_done[$target])) {
            $msg = $id.' was not fired.';
            throw new HookException($msg);
        }
        if (is_null($args)) {
            return;
        }
        $args = array_values($args);
        if (! in_array($args, static::$hooks_done[$target][$hook], true)) {
            $msg = 'Arguments given were not fired check during '.$id;
            throw new HookException($msg);
        }
    }
}
