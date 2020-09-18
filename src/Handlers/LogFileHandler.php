<?php


namespace App\Handlers;


use App\Iterators\FileIterator;
use App\Service\Reader\FileLogReader;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class LogFileHandler
{
    /**
     * @var Finder $finder
     */
    protected Finder $finder;

    /**
     * @var string $projectDir
     */
    protected string $projectDir;

    /**
     * @var string $filename
     */
    protected string $filename;

    /**
     * @var FileLogReader $reader
     */
    protected FileLogReader $reader;

    /**
     * @var string
     */
    protected string $filePath;

    /**
     * @var string
     */
    protected string $fileDir;

    /**
     * LogFileHandler constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->finder = new Finder();
        $this->reader = new FileLogReader();
        $this->projectDir = $kernel->getProjectDir();
    }

    /**
     * @param string $filename
     * @return array
     */
    public function readFile(string $filename): array
    {
        $this->prepareFilename($filename);

        $this->checkFile();

        $fileIterator = new FileIterator();

        $fileIterator->openFile($this->fileDir . $this->filename);

        return $this->reader->read($fileIterator);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->reader->getErrors();
    }

    /**
     * @return void
     */
    protected function checkFile(): void
    {
        $files = $this->finder->files()->name($this->filename)->depth('== 0')->in($this->fileDir);

        if ($files->count() < 1) {
            throw new FileNotFoundException("Запрашиваемый файл {$this->filename} не найден");
        }

        foreach ($files as $file) {
            if (!$file->isReadable()) {
                throw new AccessDeniedException("Доступ к файлу {$file->getFilename()} запрещен");
            }
        }
    }


    /**
     * @param string $filename
     * @return void
     */
    protected function prepareFilename(string $filename): void
    {
        $arr = explode('/', $filename);
        if (count($arr) > 1) {
            $this->filename = $arr[count($arr) - 1];
            unset($arr[count($arr) - 1]);
            $this->filePath = implode('/', $arr);
        } else {
            $this->filename = $filename;
            $this->filePath = 'logs';
        }

        $this->setFileDir();
    }

    /**
     * @return void
     */
    protected function setFileDir(): void
    {
        $this->fileDir = "{$this->projectDir}/{$this->filePath}/";
    }
}