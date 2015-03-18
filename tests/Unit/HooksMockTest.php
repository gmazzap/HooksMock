<?php
/*
 * This file is part of the HooksMock package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\HooksMock\Tests;

use Brain\HooksMock\HooksMock;
use PHPUnit_Framework_TestCase;
use HooksMockTestStubClass;

class HooksMockTest extends PHPUnit_Framework_TestCase
{
    public static function doNothing()
    {
    }

    public function tearDown()
    {
        parent::tearDown();
        HooksMock::tearDown();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddHookFailsIfEmptyHook()
    {
        add_action();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddHookFailsIfBadHook()
    {
        add_action(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddHookFailsIfBadCallback()
    {
        add_action('foo', 'this_callback_does_not_exists', 20, 3);
    }

    public function testAddHookOnAddAction()
    {
        $stub = new HooksMockTestStubClass();
        add_action('foo', '__return_true', 20, 3);
        add_action('bar', '__return_empty_string', 10, 4);
        add_action('foo', [$this, __FUNCTION__], 10);
        add_action('foo', [$stub, 'stub'], 30);
        $thisCallbackId = HooksMock::callbackUniqueId([$this, __FUNCTION__]);
        $stubCallbackId = HooksMock::callbackUniqueId([$stub, 'stub']);
        $actions = [
            'foo' => [
                20 => [
                    '__return_true' => ['cb' => '__return_true', 'num_args' => 3],
                ],
                10 => [
                    $thisCallbackId => ['cb' => [$this, __FUNCTION__], 'num_args' => 1],
                ],
                30 => [
                    $stubCallbackId => ['cb' => [$stub, 'stub'], 'num_args' => 1],
                ],
            ],
            'bar' => [
                10 => [
                    '__return_empty_string' => ['cb' => '__return_empty_string', 'num_args' => 4],
                ],
            ],
        ];
        assertEquals(['actions' => $actions, 'filters' => []], HooksMock::$hooks);
    }

    public function testAddHookOnAddFilter()
    {
        $stub = new HooksMockTestStubClass();
        add_filter('foo', '__return_true', 20, 3);
        add_filter('bar', '__return_empty_string', 10, 4);
        add_filter('foo', [$this, __FUNCTION__], 10);
        add_filter('foo', [$stub, 'stub'], 30);
        $thisCallbackId = HooksMock::callbackUniqueId([$this, __FUNCTION__]);
        $stubCallbackId = HooksMock::callbackUniqueId([$stub, 'stub']);
        $filters = [
            'foo' => [
                20 => [
                    '__return_true' => ['cb' => '__return_true', 'num_args' => 3],
                ],
                10 => [
                    $thisCallbackId => ['cb' => [$this, __FUNCTION__], 'num_args' => 1],
                ],
                30 => [
                    $stubCallbackId => ['cb' => [$stub, 'stub'], 'num_args' => 1],
                ],
            ],
            'bar' => [
                10 => [
                    '__return_empty_string' => ['cb' => '__return_empty_string', 'num_args' => 4],
                ],
            ],
        ];
        assertEquals(['actions' => [], 'filters' => $filters], HooksMock::$hooks);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveHookFailsIfEmptyHook()
    {
        remove_action();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveHookFailsIfBadHook()
    {
        remove_action(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveHookFailsIfBadCallback()
    {
        remove_action('foo', 'this_callback_does_not_exists', 20, 3);
    }

    public function testRemoveHook()
    {
        $stub = new HooksMockTestStubClass();
        add_filter('foo', '__return_true', 20, 3);
        add_action('bar', '__return_empty_string', 10, 4);
        add_filter('foo', [$this, __FUNCTION__], 30);
        add_filter('foo', [$stub, 'stub'], 50);
        remove_filter('foo', '__return_true', 20, 3);
        remove_action('bar', '__return_empty_string');
        remove_filter('foo', [$this, __FUNCTION__], 30, 1);
        remove_filter('foo', [$stub, 'stub'], 50, 1);
        assertEquals(['actions' => [], 'filters' => []], HooksMock::$hooks);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFireHookFailsIfEmptyHook()
    {
        do_action();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFireHookFailsIfBadHook()
    {
        apply_filters(true);
    }

    public function testFireHookOnDoAction()
    {
        do_action('hook1', 'foo', ['foo', 'bar'], true);
        do_action('hook1', true);
        do_action('hook2', ['foo', 'bar']);
        do_action('hook3');
        do_action('hook3', ['foo', 'bar']);
        $actions = [
            'hook1' => [
                ['foo', ['foo', 'bar'], true],
                [true],
            ],
            'hook2' => [
                [['foo', 'bar']],
            ],
            'hook3' => [
                [],
                [['foo', 'bar']],
            ],
        ];
        assertEquals(['actions' => $actions, 'filters' => []], HooksMock::$hooks_done);
    }

    public function testFireHookOnApplyFilters()
    {
        apply_filters('hook1', 'actual', 'foo', ['foo', 'bar'], true);
        apply_filters('hook1', 'actual', true);
        apply_filters('hook2', 'actual', ['foo', 'bar']);
        apply_filters('hook3', 'actual');
        apply_filters('hook3', 'actual', ['foo', 'bar']);
        $filters = [
            'hook1' => [
                ['actual', 'foo', ['foo', 'bar'], true],
                ['actual', true],
            ],
            'hook2' => [
                ['actual', ['foo', 'bar']],
            ],
            'hook3' => [
                ['actual'],
                ['actual', ['foo', 'bar']],
            ],
        ];
        assertEquals(['actions' => [], 'filters' => $filters], HooksMock::$hooks_done);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testCallbackUniqueIdFailsIfBadCallback()
    {
        HooksMock::callbackUniqueId('foo');
    }

    public function testCallbackUniqueId()
    {
        $stub = new HooksMockTestStubClass();
        $static = __CLASS__.'::doNothing';
        $dynamic = spl_object_hash($stub).'stub';
        $func = function ($foo = 1) {
            return $foo;
        };
        assertEquals('__return_false', HooksMock::callbackUniqueId('__return_false'));
        assertEquals($static, HooksMock::callbackUniqueId([__CLASS__, 'doNothing']));
        assertEquals($dynamic, HooksMock::callbackUniqueId([$stub, 'stub']));
        assertEquals(spl_object_hash($func), HooksMock::callbackUniqueId($func));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasHookFailsIfBadHook()
    {
        HooksMock::hasHook('action', true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasHookFailsIfBadCallable()
    {
        HooksMock::hasHook('action', 'foo', 'foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasHookFailsIfBadPriority()
    {
        HooksMock::hasHook('action', 'foo', '__return_false', 'foo');
    }

    public function testHasHook()
    {
        $stub = new \HooksMockTestStubClass();
        add_filter('foo', '__return_true', 20, 3);
        add_action('bar', '__return_empty_string', 10, 4);
        add_filter('foo', [__CLASS__, 'doNothing'], 30);
        add_filter('bar', [$stub, 'stub'], 10, 2);
        assertTrue(HooksMock::hasHook('filter', 'foo', '__return_true', 20));
        assertTrue(HooksMock::hasHook('action', 'bar', '__return_empty_string'));
        assertTrue(HooksMock::hasHook('filter', 'foo', [__CLASS__, 'doNothing'], 30));
        assertTrue(HooksMock::hasHook('filter', 'bar', [$stub, 'stub'], 10));
        assertFalse(HooksMock::hasHook('filter', 'foo', '__return_true', 30));
        assertFalse(HooksMock::hasHook('action', 'bar', '__return_true'));
        assertFalse(HooksMock::hasHook('action', 'baz', '__return_true'));
        assertFalse(HooksMock::hasHook('filter', 'bar', ['\HooksMockTestStubClass', 'stubStatic']));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasHookFiredIfBadHook()
    {
        HooksMock::hasHookFired('action', true);
    }

    public function testHasHookFired()
    {
        do_action('hook1', 'foo', ['foo', 'bar'], true);
        apply_filters('hook2', 'foo', ['foo', 'bar'], true);
        assertTrue(HooksMock::hasHookFired('action', 'hook1'));
        assertFalse(HooksMock::hasHookFired('action', 'hook2'));
        assertTrue(HooksMock::hasHookFired('filter', 'hook2'));
        assertFalse(HooksMock::hasHookFired('filter', 'hook1'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAssertHookAddedIfBadHook()
    {
        HooksMock::assertHookAdded('action', true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAssertHookAddedIfBadCallback()
    {
        HooksMock::assertHookAdded('action', 'foo', 2);
    }

    /**
     * @expectedException \Brain\HooksMock\HookException
     */
    public function testAssertHookAddedThrowIfNoHook()
    {
        HooksMock::assertHookAdded('action', 'foo', '__return_true');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAssertHookAddedIfBadPriority()
    {
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => ['cb' => ['__return_true'], 'num_args' => 1],
                ],
            ],
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => ['cb' => '__return_empty_string', 'num_args' => 2],
                ],
            ],
        ];
        HooksMock::$hooks = ['actions' => $actions, 'filters' => $filters];
        HooksMock::assertHookAdded('action', 'foo', '__return_true', 'foo');
    }

    /**
     * @expectedException \Brain\HooksMock\HookException
     */
    public function testAssertHookAddedThrowIfNoPriority()
    {
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => ['cb' => ['__return_true'], 'num_args' => 1],
                ],
            ],
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => ['cb' => '__return_empty_string', 'num_args' => 2],
                ],
            ],
        ];
        HooksMock::$hooks = ['actions' => $actions, 'filters' => $filters];
        HooksMock::assertHookAdded('filter', 'bar', '__return_empty_string');
    }

    /**
     * @expectedException \Brain\HooksMock\HookException
     */
    public function testAssertHookAddedThrowIfNoCallback()
    {
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => ['cb' => ['__return_true'], 'num_args' => 1],
                ],
            ],
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => ['cb' => '__return_empty_string', 'num_args' => 2],
                ],
            ],
        ];
        HooksMock::$hooks = ['actions' => $actions, 'filters' => $filters];
        HooksMock::assertHookAdded('action', 'foo', '__return_empty_string', 10);
    }

    public function testAssertHookAddedNotThrowIfNoArgs()
    {
        $stub = new HooksMockTestStubClass();
        $stubId = HooksMock::callbackUniqueId([$stub, 'stub']);
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => ['cb' => '__return_true', 'num_args' => 1],
                ],
                20 => [
                    $stubId => ['cb' => [$stub, 'stub'], 'num_args' => 3],
                ],
            ],
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => ['cb' => '__return_empty_string', 'num_args' => 2],
                ],
            ],
        ];
        HooksMock::$hooks = ['actions' => $actions, 'filters' => $filters];
        HooksMock::assertHookAdded('action', 'foo', '__return_true', 10);
        HooksMock::assertHookAdded('action', 'foo', [$stub, 'stub'], 20);
        HooksMock::assertHookAdded('filter', 'bar', '__return_empty_string', 20);
    }

    /**
     * @expectedException \Brain\HooksMock\HookException
     */
    public function testAssertHookAddedThrowIfWrongArgs()
    {
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => ['cb' => ['__return_true'], 'num_args' => 1],
                ],
            ],
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => ['cb' => '__return_empty_string', 'num_args' => 2],
                ],
            ],
        ];
        HooksMock::$hooks = ['actions' => $actions, 'filters' => $filters];
        HooksMock::assertHookAdded('action', 'foo', '__return_true', 10, 2);
    }

    public function testAssertHookAddedNotThrow()
    {
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => ['cb' => ['__return_true'], 'num_args' => 1],
                ],
            ],
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => ['cb' => '__return_empty_string', 'num_args' => 2],
                ],
            ],
        ];
        HooksMock::$hooks = ['actions' => $actions, 'filters' => $filters];
        HooksMock::assertHookAdded('action', 'foo', '__return_true', 10, 1);
        HooksMock::assertHookAdded('filter', 'bar', '__return_empty_string', 20, 2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAssertHookFiredIfBadHook()
    {
        HooksMock::assertHookFired('action', true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAssertHookFiredIfBadArgs()
    {
        HooksMock::assertHookFired('action', 'foo', 2);
    }

    /**
     * @expectedException \Brain\HooksMock\HookException
     */
    public function testAssertHookAddedThrowIfNWrongAction()
    {
        $actions = [
            'hook1' => [
                ['foo', ['foo', 'bar'], true],
                [true],
            ],
            'hook2' => [
                [['foo', 'bar']],
            ],
        ];
        $filters = [
            'hook3' => [
                ['actual'],
                ['actual', ['foo', 'bar']],
            ],
        ];
        HooksMock::$hooks_done = ['actions' => $actions, 'filters' => $filters];
        HooksMock::assertHookFired('action', 'hook3');
    }

    /**
     * @expectedException \Brain\HooksMock\HookException
     */
    public function testAssertHookAddedThrowIfNWrongFilter()
    {
        $actions = [
            'hook1' => [
                ['foo', ['foo', 'bar'], true],
                [true],
            ],
            'hook2' => [
                [['foo', 'bar']],
            ],
        ];
        $filters = [
            'hook3' => [
                ['actual'],
                ['actual', ['foo', 'bar']],
            ],
        ];
        HooksMock::$hooks_done = ['actions' => $actions, 'filters' => $filters];
        HooksMock::assertHookFired('filter', 'hook2');
    }

    /**
     * @expectedException \Brain\HooksMock\HookException
     */
    public function testAssertHookAddedThrowIfNWrongActionArgs()
    {
        $actions = [
            'hook1' => [
                ['foo', ['foo', 'bar'], true],
                [true],
            ],
            'hook2' => [
                [['foo', 'bar']],
            ],
        ];
        $filters = [
            'hook3' => [
                ['actual'],
                ['actual', ['foo', 'bar']],
            ],
        ];
        HooksMock::$hooks_done = ['actions' => $actions, 'filters' => $filters];
        HooksMock::assertHookFired('action', 'hook1', ['foo']);
    }

    /**
     * @expectedException \Brain\HooksMock\HookException
     */
    public function testAssertHookAddedThrowIfNWrongFilterArgs()
    {
        $actions = [
            'hook1' => [
                ['foo', ['foo', 'bar'], true],
                [true],
            ],
            'hook2' => [
                [['foo', 'bar']],
            ],
        ];
        $filters = [
            'hook3' => [
                ['actual'],
                ['actual', ['foo', 'bar']],
            ],
        ];
        HooksMock::$hooks_done = ['actions' => $actions, 'filters' => $filters];
        HooksMock::assertHookFired('filter', 'hook3', ['actual', 'foo', 'bar']);
    }

    public function testAssertHookFiredNotThrow()
    {
        $actions = [
            'hook1' => [
                ['foo', ['foo', 'bar'], true],
                [true],
            ],
            'hook2' => [
                [['foo', 'bar']],
            ],
        ];
        $filters = [
            'hook3' => [
                ['actual'],
                ['actual', ['foo', 'bar']],
            ],
        ];
        HooksMock::$hooks_done = ['actions' => $actions, 'filters' => $filters];
        HooksMock::assertHookFired('action', 'hook1', ['foo', ['foo', 'bar'], true]);
        HooksMock::assertHookFired('action', 'hook1', [true]);
        HooksMock::assertHookFired('filter', 'hook3', ['actual']);
        HooksMock::assertHookFired('filter', 'hook3', ['actual', ['foo', 'bar']]);
    }
}
