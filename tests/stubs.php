<?php
if ( ! class_exists( 'HooksMockTestStubClass' ) ) {

    class HooksMockTestStubClass {

        public static function stubStatic() {
            return TRUE;
        }

        public function stub() {
            return TRUE;
        }

    }
}

if ( ! function_exists( '__return_false' ) ) {

    function __return_false() {
        return FALSE;
    }

}

if ( ! function_exists( '__return_true' ) ) {

    function __return_true() {
        return TRUE;
    }

}

if ( ! function_exists( '__return_empty_string' ) ) {

    function __return_empty_string() {
        return '';
    }

}