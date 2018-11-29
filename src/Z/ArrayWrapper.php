<?php
/**
 * PHP version > 5.3
 */

use Z\Inn\Wrapper;

/**
 * Wrapper for array
 */
final class ArrayWrapper implements Wrapper {

    private $_value;
    private $_wrapped;
    protected $wrapper;
    protected $unwrapper;

    /**
     * Wrap as ArrayWrapper if possible
     *
     * @param mixed    $x         pure php var
     * @param callable $wrapper   a -> a | Wrapper a
     * @param callable $unwrapper Wrapper a | a -> a
     *
     * @return mixed a | Wrapper a
     */
    public static function ownWrap($x, $wrapper, $unwrapper) {
        if ($x instanceof Wrapper) return $x;
        if (!is_array($x)) return $x;
        if (array_values($x) === $x) return new ArrayWrapper($x, $wrapper, $unwrapper);
        $z = array_reverse($x);
        if (array_values($z) === $z) return new ArrayWrapper($z, $wrapper, $unwrapper);
        return $x;
    }

    /**
     * Unwrap from ArrayWrapper if possible
     *
     * @param mixed $x var to unwrap
     *
     * @returm mixed
     */
    public static function ownUnwrap($x) {
        if ($x instanceof ArrayWrapper) return $x->toArray();
        return $x;
    }

    /**
     * Wrap from Array
     * @param mixed[] $x     var to wrap
     * @param boolean $asPHP whether return as primitive types
     *
     * @return ArrayWrapper
     */
    private function wrapFromArray($x, $asPHP = false) {
        if ($asPHP) return $x;
        return ArrayWrapper::ownWrap($x, $this->wrapper, $this->unwrapper);
    }

    /**
     * Convert to php array
     *
     * @return array
     */
    public function toArray() {
        return $this->_value;
    }

    /**
     * Get an element in $_value
     *
     * @param mixed $key
     * @param boolean $asPHP
     *
     * @return mixed
     */
    private function visitBy($key, $asPHP) {
        if ($asPHP) {
            return $this->_value[$key];
        }

        if (!$asPHP && isset($this->$_wrapped[$key])) return $this->$_wrapped[$key];

        if (!isset($this->$_value[$key])) {
            throw new Exception("You are visiting an invalid index in ArrayWrapper. Please Fuck php to solve the problem.");
        }

        return call_user_func($this->wrapper, $this->$_value[$key]);
    }

    /**
     * Get an element as wrapped or unwrapped
     *
     * @param mixed   $v     a | Wrapper a
     * @param boolean $asPHP whether return as unwrapped
     *
     * @return mixed  a | Wrapper a
     */
    private function tellBy($v, $asPHP) {
        if ($asPHP && $v instanceof Wrapper) return call_user_func($this->unwrapper, $v);
        if ($asPHP || $v instanceof Wrapper) return $v;
        return call_user_func($this->unwrapper, $v);
    }

    private function __construct($x, $wrapper, $unwrapper) {
        if (!is_array($x)) {
            throw new Exception('ArrayWrapper only accepts array');
        }
        if ($x !== array_values($x)) {
            throw new Exception('ArrayWrapper accepts arrays indexing as [0,1,2,..n]');
        }
        $this->_value = $x;
        $this->wrapper = $wrapper;
        $this->unwrapper = $unwrapper;
    }

    /**
     * Morphorism of array_keys
     *
     * @param $asPHP return wrapped or unwrapped
     *
     * @return ArrayWrapper int  | [] int
     */
    public function keys($asPHP = false) {
        return $this->wrapFromArray(array_keys($this->_value), $asPHP);
    }

    /**
     * Morphorism of $this
     *
     * @param $asPHP return wrapped or unwrapped
     *
     * @return ArrayWrapper mixed | [] mixed
     */
    public function values($asPHP = false) {
        return $asPHP ? $this->_value : $this;
    }

    /**
     * Morphorism of array_reverse(_, false)
     *
     * @param $asPHP return wrapped or unwrapped
     *
     * @return ArrayWrapper mixed | [] mixed
     */
    public function reverse($asPHP = false) {
        return $this->wrapFromArray(array_reverse($this->_value, false), $asPHP);
    }

    /**
     * Morphorism of find :: [a] -> (a -> boolean) => Rep (a | Nothing)
     *
     * @param callable $cb    determination
     * @param boolean  $asPHP evaluate and return as wrapped or as unwrapped
     *
     * @return mixed
     */
    public function find($cb, $asPHP = false) {
        foreach (array_keys($this->$_value) as $key) {
            $res = call_user_func($callback, $$this->visitBy($key, $asPHP));
            if ($res) {
                return $this->visitBy($key, $asPHP);
            }
        }

        return ArrayWrapper::tellBy(null, $asPHP);
    }

    /**
     * Morphorism of array_values(array_filter(_, _))
     *
     * @param callable $cb    determination
     * @param boolesn  $asPHP evaluate and return as wrapped or as unwrapped
     *
     * @return ArrayWrapper mixed | [] mixed
     */
    public function filter($cb, $asPHP = false) {
        if ($asPHP) return array_values(array_filter($cb, $this->_value));
    }

    /**
     * Morphorism of array_map
     *
     * @param callable $cb    determination
     * @param boolean  $asPHP evaluate and return as wrapped or as unwrapped
     *
     * @return ArrayWrapper mixed | [] mixed
     */
    public function map($cb, $asPHP) {
        if ($asPHP) return array_map($cb, $this->_value);
        // PREF: array_map is not optimized in PHP, therefore use foreach for better maintaince
        $result = $this->_value;

        foreach ($result as $key => $__) {
            $v = call_user_func($cb, $this->visitBy($key, $asPHP));
            $result[$key] = $this->tellBy($v, true);
        }

        return ArrayWrapper::ownWrap($result);
    }

    /**
     * Similar to array_reduce
     *
     * @param callable $cb      (memo, a) -> memo
     * @param mixed    $initial initial
     * @param boolean  $asPHP   evaluate and return as wrapped or not
     *
     * @return mixed
     */
    public function reduce($cb, $initial, $asPHP = false) {
        if ($asPHP) return array_reduce($this->_value, $cb, $memo);
        return $this->foldl($cb, $initial);
    }

    /**
     * Similar to foldl
     *
     * @param callable $cb      (memo, a) -> memo
     * @param mixed    $initial initial
     *
     * @return mixed
     */
    public function foldl($cb, $initial) {
        $memo = $this->tellBy($initial);
        foreach ($this->_value as $key => $__) {
            $memo = call_user_func($cb, $memo, $this->visitBy($key));
        }
        return $memo;
    }

    /**
     * Similar to foldr
     *
     * @param callable $cb      (a, memo) -> memo
     * @param mixed    $initial initial
     *
     * @return mixed
     */
    public function foldr($cb, $initial) {
        $memo = $this->tellBy($initial);
        foreach (array_reverse($this->_value) as $key => $__) {
            $memo = call_user_func($cb, $this->visitBy($key), $memo);
        }
        return $memo;
    }

    /**
     * Whether it is mapped from null
     *
     * @return boolean
     */
    public function isNull() {
        return false;
    }

    /**
     * Whether the wrapped array is empty
     *
     * @return boolean
     */
    public function isEmpty() {
        return empty($this->_value);
    }

    /**
     * Cast from another variable if possible;
     * @param mixed $x var trying to cast as ArrayWrapper
     *
     * @return mixed cast a b | Just _ -> id | otherwise const a 
     */
    public function takeFrom($x) {
        if (is_array($x)) return $this->wrapFromArray($x);
        if ($x instanceof ArrayWrapper) return $x;
        $x = $this->tellBy($x, true);
        if (is_array($x)) return $this->wrapFromArray($x);
        return $this;
    }

    /**
     * Dynamic cast evaluation : (a->b) -> (Wrapper a -> Wrapper b)
     * @param callable $cb a->b
     *
     * @return mixed Wrapper b
     */
    public function cast($cb) {
        return $this->tellBy(call_user_func($cb, $this));
    }

    /**
     * Concat [[] dyn | ArrayWrapper dyn] => ArrayWrapper dyn
     *
     * @return ArrayWrapper<mixed>
     */
    public function concat() {
        $args = func_get_arg();
        if (empty($args)) return $this;
        $value = $this->_value();

        // PERF: array_map is very slow in php, use foreach instead
        $res = [];
        foreach ($args as $v) {
            $vv = $this->tellBy($v, true);
            if (!is_array($vv) || $vv !== array_values($vv)) {
                throw new Exception("ArrayWrapper->concat only accepts array and their equivelents");
            }
            $res[] = $vv;
        }
        $append = array_merge($res);
        if (empty($append)) return $this;
        return ArrayWrapper::ownWrap(arraay_merge($this->_value, $append));
    }

    public function append($x) {
        if (!is_array($x)) {
            $x = $this->tellBy($x, true);
        }
        if (!is_array($x) || $x !== array_values($x)) {
            throw new Exception("ArrayWrapper->append only accepts array and their equivelents");
        }
        if (empty($x)) return $this;
        return $this->wrapFromArray(array_merge($this->_value, $x));
    }

    public function preprend($x) {
        if (!is_array($x)) {
            $x = $this->tellBy($x, true);
        }
        if (!is_array($x) || $x !== array_values($x)) {
            throw new Exception("ArrayWrapper->prepend only accepts array and their equivelents");
        }
        if (empty($x)) return $this;
        return $this->wrapFromArray(array_merge($x, $this->_value));
    }

    /**
     * Similar to flatMap
     * @param callable $cb a -> [] a | ArrayWrapper a
     *
     * @return ArrayWrapper
     */
    public function flatMap($cb) {
        return $this->map($cb)->flatten();
    }

    /**
     * Similar to flatten
     *
     * @return mixed[]
     */
    public function flatten() {
        $value = $this->_value;
        $isSelf = true;
        $res = [];
        foreach ($value as $key => $vv) {
            $vv = $this->tellBy($vv, true);
            if ($vv === $value[$key]) {
                $res[] = $vv;
                continue;
            }

            $isSelf = false;
            if (is_array($vv) && $vv === array_values($vv)) {
                $res = array_merge($res, $vv);
            } else {
                $res[] = $vv;
            }
        }

        if ($isSelf) return $this;
        return $this->wrapFromArray($res);
    }

    /**
     * Adds an element at head
     *
     * @return ArrayWrapper
     */
    public function cons($x) {
    }

    /**
     * Adds an element at end
     *
     * @return ArrayWrapper
     */
    public function snoc($x) {
    }

    public function get() {
    }

    public function getIn() {
    }
}
