<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CountryService;
use App\Service\CsvProcessor;
use App\Service\DataTransformer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'transform',
    description: 'Transform CSV data to JSON format with data validation and cleaning'
)]
class TransformCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'input',
                'i',
                InputOption::VALUE_REQUIRED,
                'Input CSV file path',
                'input.csv'
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'Output JSON file path',
                'output.json'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputFile = $input->getOption('input');
        $outputFile = $input->getOption('output');

        $io->title('CSV to JSON Data Transformer');

        try {
            // Initialize services
            $countryService = new CountryService();
            $dataTransformer = new DataTransformer($countryService);
            $csvProcessor = new CsvProcessor($dataTransformer);

            $io->section('Processing CSV file...');
            $io->text("Input file: {$inputFile}");
            $io->text("Output file: {$outputFile}");

            // Process the CSV file
            $dataGenerator = $csvProcessor->processFile($inputFile);

            $io->section('Writing JSON output...');
            $csvProcessor->writeToJson($dataGenerator, $outputFile);

            $io->success('Data transformation completed successfully!');
            $io->text("Output written to: {$outputFile}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
