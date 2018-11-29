<?php
/**
 * PHP version > 5.3
 */

use Z\Inn\Wrapper;

/**
 * Map of null
 */
class Nihil extends Wrapper {
    /**
     * Wrap as Nihil if possible
     *
     * @param mixed $x pure php var
     *
     * @return mixed
     */
    public static function ownWrap($x) {
        return $x === null ? new Nihil() : $x;
    }

    /**
     * Unwrap from ArrayWrapper if possible
     *
     * @param mixed $x var to unwrap
     *
     * @returm mixed
     */
    public static function ownUnwrap($x) {
        if ($x instanceof Nihil) return null;
        return $x;
    }

    /**
     * Maintain the Chain interface, like >>= for (Nothing :: Maybe a)
     *
     * @param mixed   $x function name
     * @param mixed[] $y function arguments
     */
    public function __call($x , $y) {
        $funcName = "$x";
        if ($funcName === "isNull") {
            return true;
        }
        return $this;
    }
}
