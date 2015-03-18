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

if (! function_exists('assertActionAdded')) {
    function assertActionAdded($hook = '', $callback = null, $priority = null, $args_num = null)
    {
        HooksMock::assertActionAdded($hook, $callback, $priority, $args_num);
    }
}

if (! function_exists('assertFilterAdded')) {
    function assertFilterAdded($hook = '', $callback = null, $priority = null, $args_num = null)
    {
        HooksMock::assertFilterAdded($hook, $callback, $priority, $args_num);
    }
}

if (! function_exists('assertActionFired')) {
    function assertActionFired($hook = '', $args = null)
    {
        HooksMock::assertActionFired($hook, $args);
    }
}

if (! function_exists('assertFilterFired')) {
    function assertFilterFired($hook = '', $args = null)
    {
        HooksMock::assertFilterFired($hook, $args);
    }
}
