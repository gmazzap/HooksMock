<?php
/*
 * This file is part of the HooksMock package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Brain\HooksMock\HooksMock;

if (! function_exists('_wp_filter_build_unique_id')) {
    function _wp_filter_build_unique_id($tag = null, $function = null, $priority = null)
    {
        HooksMock::callbackUniqueId($function);
    }
}

if (! function_exists('add_action')) {
    function add_action()
    {
        HooksMock::addHook('action', func_get_args());
    }
}

if (! function_exists('add_filter')) {
    function add_filter()
    {
        HooksMock::addHook('filter', func_get_args());
    }
}

if (! function_exists('remove_action')) {
    function remove_action()
    {
        HooksMock::removeHook('action', func_get_args());
    }
}

if (! function_exists('remove_filter')) {
    function remove_filter()
    {
        HooksMock::removeHook('filter', func_get_args());
    }
}

if (! function_exists('do_action')) {
    function do_action()
    {
        return HooksMock::fireHook('action', func_get_args());
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters()
    {
        return HooksMock::fireHook('filter', func_get_args());
    }
}

if (! function_exists('did_action')) {
    function did_action($action = '')
    {
        return HooksMock::hasActionFired($action);
    }
}

if ( ! function_exists('has_action')) {
    function has_action()
    {
        return call_user_func_array(['Brain\HooksMock\HooksMock', 'hasAction'], func_get_args());
    }
}
