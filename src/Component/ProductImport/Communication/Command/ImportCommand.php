<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Communication\Command;

use App\Component\ProductImport\Business\ProductImportBusinessFascade;
use App\Component\ProductImport\DTO\FilePathValueObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:importCsv',
    description: 'Import CSV File',
    hidden: false,
    aliases: ['app:addCsv']
)]

class ImportCommand extends Command
{

    public function __construct(
        private readonly ProductImportBusinessFascade $productImportBusinessFascade
    )
    {
        parent::__construct('Name');
    }

    protected function configure()
    {
        $this->setHelp('Allows to import Data from a CSV-File');
        $this->addArgument('filePath', InputArgument::REQUIRED, 'The Path of the CSV File');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('filePath');

        $this->productImportBusinessFascade->import(new FilePathValueObject($filePath));
        $output->writeln('Import executed');

        return Command::SUCCESS;
    }



}