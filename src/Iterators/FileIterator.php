<?php


namespace App\Iterators;


use App\Iterators\Interfaces\IteratorInterface;

class FileIterator implements IteratorInterface
{
    /**
     * @var false|resource
     */
    private $file;

    /**
     * @param string $path
     * @return void
     */
    public function openFile(string $path): void
    {
        $this->file = fopen($path, 'r');
    }

    /**
     * @return \Generator
     */
    public function iterate(): \Generator
    {
        while(!feof($this->file)) {
            yield fgets($this->file);
        }
        fclose($this->file);
    }

}