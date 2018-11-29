<?php
/**
 * PHP version > 5.3
 * Experiment Class
 */

namespace Z\Inn\UnlikePHPFunction;

/**
 * Experiment Class for FP
 */
interface UnlikePHPFunction {
    /**
     * Evaluate function given $a
     * -- There is no function for (arr a b) -> a -> b;
     * -- however I have to provide it in php before I implement a Arrow.Fail
     * @param mixed $a The argument
     *
     * @return mixed
     */
    public function apoi($a);
}
