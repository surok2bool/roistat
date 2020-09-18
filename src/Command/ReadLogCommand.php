<?php


namespace App\Command;


use App\Handlers\LogFileHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class ReadLogCommand extends Command
{
    /**
     * @var LogFileHandler $fileHandler
     */
    public LogFileHandler $fileHandler;

    /**
     * ReadLogCommand constructor.
     * @param LogFileHandler $fileHandler
     * @param string|null $name
     */
    public function __construct(LogFileHandler $fileHandler, ?string $name = null)
    {
        $this->fileHandler = $fileHandler;
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('app:parse-logs')
            ->setDescription('Команда обрабатывает лог-файл и выдает информацию о нем в виде json')
            ->addArgument('-n', Input\InputArgument::REQUIRED)
        ;
    }

    /**
     * @param Input\InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(Input\InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('-n');

        try {
            $result = $this->fileHandler->readFile($filename);
            $output->writeln(json_encode($result));
            $output->writeln($this->fileHandler->getErrors());
        } catch (FileNotFoundException $exception) {
            $output->writeln($exception->getMessage());
        } catch (AccessDeniedException $exception) {
            $output->writeln($exception->getMessage());
        }

        return 1;
    }
}