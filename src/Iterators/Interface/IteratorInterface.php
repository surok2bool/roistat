<?php


namespace App\Iterators\Interfaces;


interface IteratorInterface
{
    /**
     * @return \Generator
     */
    public function iterate(): \Generator;
}