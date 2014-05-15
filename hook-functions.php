<?php
if ( ! function_exists( '_wp_filter_build_unique_id' ) ) {

    function _wp_filter_build_unique_id( $tag = NULL, $function = NULL, $priority = NULL ) {
        Brain\HooksMock\HooksMock::callbackUniqueId( $function );
    }

}

if ( ! function_exists( 'add_action' ) ) {

    function add_action() {
        Brain\HooksMock\HooksMock::addHook( 'action', func_get_args() );
    }

}

if ( ! function_exists( 'add_filter' ) ) {

    function add_filter() {
        Brain\HooksMock\HooksMock::addHook( 'filter', func_get_args() );
    }

}

if ( ! function_exists( 'remove_action' ) ) {

    function remove_action() {
        Brain\HooksMock\HooksMock::removeHook( 'action', func_get_args() );
    }

}

if ( ! function_exists( 'remove_filter' ) ) {

    function remove_filter() {
        Brain\HooksMock\HooksMock::removeHook( 'filter', func_get_args() );
    }

}

if ( ! function_exists( 'do_action' ) ) {

    function do_action() {
        return Brain\HooksMock\HooksMock::fireHook( 'action', func_get_args() );
    }

}

if ( ! function_exists( 'apply_filters' ) ) {

    function apply_filters() {
        return Brain\HooksMock\HooksMock::fireHook( 'filter', func_get_args() );
    }

}

if ( ! function_exists( 'did_action' ) ) {

    function did_action() {
        return Brain\HooksMock\HooksMock::hasActionFired( func_get_args() );
    }

}
