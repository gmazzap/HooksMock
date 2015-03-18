<?php
/*
 * This file is part of the HooksMock package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (! class_exists('HooksMockTestStubClass')) {
    class HooksMockTestStubClass
    {
        public static function stubStatic()
        {
            return true;
        }

        public function stub()
        {
            return true;
        }
    }
}

if (! function_exists('__return_false')) {
    function __return_false()
    {
        return false;
    }
}

if (! function_exists('__return_true')) {
    function __return_true()
    {
        return true;
    }
}

if (! function_exists('__return_empty_string')) {
    function __return_empty_string()
    {
        return '';
    }
}
