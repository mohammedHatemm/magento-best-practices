<?php
/**
 * CLI Command to Expire Points Manually
 * php bin/magento loyalty:points:expire
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Elsherif\LoyaltySystem\Cron\ExpirePoints;

class ExpirePointsCommand extends Command
{
    private $expirePoints;

    public function __construct(
        ExpirePoints $expirePoints,
        string $name = null
    ) {
        $this->expirePoints = $expirePoints;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('loyalty:points:expire')
            ->setDescription('Manually expire old loyalty points');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln("<info>Starting points expiration...</info>");
            
            $this->expirePoints->execute();
            
            $output->writeln("<info>Points expiration completed successfully!</info>");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Error: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }
}
