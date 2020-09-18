<?php


namespace App\Service\Reader;


use App\Exceptions\LogParsingErrorException;
use App\Iterators\Interfaces\IteratorInterface;

class FileLogReader
{

    /**
     * @var RecordParser $parser
     */
    protected RecordParser $parser;

    /**
     * Вообще для сбора ошибок, наверное, было бы неплохо создать свой объект, а не собирать массив
     * @var array $errors
     */
    protected array $errors = [];

    /**
     * FileLogReader constructor.
     */
    public function __construct()
    {
        $this->parser = new RecordParser();
    }

    /**
     * @param IteratorInterface $iterator
     * @return array
     */
    public function read(IteratorInterface $iterator): array
    {
        foreach ($iterator->iterate() as $key => $item) {
            try {
                $this->parser->parsingRecord($item);
            } catch (LogParsingErrorException $e) {
                $this->errors[] = "Строка № {$key}: {$e->getMessage()}";
            }
        }

        return $this->parser->getResult();
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

}