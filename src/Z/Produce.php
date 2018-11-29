<?php

function producePrim($wrap, $unwrap) {
    return function ($cb) use ($wrap, $unwrap) {
        $args = array_map($wrap, func_get_args());
        $result = $cb($args);
        return call_user_func($unwrap, $result);
    };
}
