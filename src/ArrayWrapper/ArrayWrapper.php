<?php

/**
 * PHP version >=5.3
 */

namespace ArrayWrapper\ArrayWrapper;

use ArrayWrapper\Inn\Wrapper;

class ArrayWrapper implements Wrapper {
    private $_value;
    private $_wrap;
    private $_unwrap;

    function __constructor($v, $w, $u) {
        $this->_value = $v;
        $this->_wrap = $w;
        $this->_unwrap = $u;
    }

    static public function wrap($v, $wrap, $unwrap) {
        if ($v instanceof Wrapper) return $v;
        if (is_array($v) || $v === null) return new ArrayWrapper($v, $wrapm, $unwrap);
        return $v;
    }

    public function unwrap() {
        if ($this->_value === null) return null;
        return array_map(
            $this->_unwrap,
            $this->_value
        );
    }

    private function mkWrap($x) {
        if ($x instanceof Wrapper) return $x;
        return call_user_func($this->_wrap, $x);
    }

    private function mkUnwrap($x) {
        if ($x instanceof Wrapper) return call_user_func($this->_unwrap, $x);
        return $x;
    }

    public function isNull() {
        return $this->_value === null;
    }


    public function find($cb, $asPHP = false) {
        if ($this->_value === null) return $asPHP ? null : $this;

        foreach ($this->_value as &$x) {
            if (!$asPHP && ! ($x instanceof Wrapper)) {
                $x = $this->mkWrap($x);
            }
            $z = $x;
            if ($asPHP) $z = $this->mkUnwrap($z);
            if ($cb($z)) return $z;
        }

        return $asPHP ? null : $this->mkWrap(null);
    }
}
