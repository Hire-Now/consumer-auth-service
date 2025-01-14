<?php

namespace App\Infrastructure\Console;

use App\Domain\Entities\Consumer;
use App\Domain\Ports\Outbound\ConsumerRepositoryPort;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Support\Str;

class CreateApiConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:consumer:create {name} {--description=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new API consumer';

    public function __construct(private readonly ConsumerRepositoryPort $consumerRepository)
    {
        parent::__construct($this->signature);
    }

    protected function configure()
    {
        $this->setDescription('Create a new API consumer')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the API consumer')
            ->addOption('description', null, InputOption::VALUE_OPTIONAL, 'The description of the API consumer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $description = $input->getOption('description');

        $clientId = Str::uuid()->toString();
        $clientSecret = bin2hex(random_bytes(16));

        $this->consumerRepository->create(new Consumer(
            null,
            $name,
            $clientId,
            password_hash($clientSecret, PASSWORD_BCRYPT),
            $description,
            true,
            Carbon::now(),
            Carbon::now(),
            Carbon::now()
        ));

        $output->writeln("API Consumer created successfully!");
        $output->writeln("Client ID: $clientId");
        $output->writeln("Client Secret: $clientSecret (store it securely)");

        return Command::SUCCESS;
    }
}