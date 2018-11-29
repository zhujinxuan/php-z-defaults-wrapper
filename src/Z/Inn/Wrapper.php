<?php
/**
 * PHP version > 5.3
 */

namespace Z\Inn\Wrapper;

interface Wrapper {
    /**
     * As x -> Wrapper x | x
     * @param mixed $x the var for wrapping
     *
     * @return mixed
     */
    public static function ownWrap($x);

    /**
     * As Wrapper x | x -> x
     * @param mixed $x the potentially wrapped var
     *
     * @return mixed
     */
    public static function unwrap($x);

    /**
     * If it is null
     */
    public function isNull();

    /**
     * Cast to another type with evaluation
     *
     * @param callable $cb    callback for $this
     * @param boolean  $asPHP fuck php
     *
     * @return null;
     */
    public function cast($cb);

    /**
     * Cast an variable to its own wrapper if possible
     * -- If $elem can be mapped isomorphism via primitive types, takeFrom converts wrap
     *
     * @param mixed $elem a | Wrapper a | (Similar Wrapper) a
     *
     * @return mixed a | (Self Wrapper a)
     */
    public function takeFrom($elem);

    /**
     * For nest structure; U' a@(x:xs) = U' (map flatten a)
     * For naive structure; C a = C (flatten a)
     * For primitive types; flatten = id
     */
    public function flatten();

    /**
     * Provide poor language's anamorphism
     */
    public function flatMap();
}
