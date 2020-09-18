<?php


namespace App\Service\Reader;


interface ParserRecordInterface
{
    /**
     * @param string $record
     */
    public function parsingRecord(string $record): void;

    /**
     * @return array
     */
    public function getResult(): array;
}