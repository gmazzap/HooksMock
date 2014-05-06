<?php
if ( ! function_exists( 'assertActionAdded' ) ) {

    function assertActionAdded( $hook = '', $callback = NULL, $priority = NULL, $args_num = NULL ) {
        Brain\HooksMock\HooksMock::assertActionAdded( $hook, $callback, $priority, $args_num );
    }

}

if ( ! function_exists( 'assertFilterAdded' ) ) {

    function assertFilterAdded( $hook = '', $callback = NULL, $priority = NULL, $args_num = NULL ) {
        Brain\HooksMock\HooksMock::assertFilterAdded( $hook, $callback, $priority, $args_num );
    }

}

if ( ! function_exists( 'assertActionFired' ) ) {

    function assertActionFired( $hook = '', $args = NULL ) {
        Brain\HooksMock\HooksMock::assertActionFired( $hook, $args );
    }

}

if ( ! function_exists( 'assertFilterFired' ) ) {

    function assertFilterFired( $hook = '', $args = NULL ) {
        Brain\HooksMock\HooksMock::assertFilterFired( $hook, $args );
    }

}