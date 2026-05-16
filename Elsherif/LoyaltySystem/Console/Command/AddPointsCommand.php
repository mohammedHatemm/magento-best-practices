<?php
/**
 * CLI Command to Add Points
 * php bin/magento loyalty:points:add 1 100
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;

class AddPointsCommand extends Command
{
    private $pointsManagement;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        string $name = null
    ) {
        $this->pointsManagement = $pointsManagement;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('loyalty:points:add')
            ->setDescription('Add points to customer')
            ->addArgument('customer_id', InputArgument::REQUIRED, 'Customer ID')
            ->addArgument('points', InputArgument::REQUIRED, 'Points to add');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $customerId = (int) $input->getArgument('customer_id');
        $points = (int) $input->getArgument('points');

        try {
            $this->pointsManagement->addPoints(
                $customerId,
                $points,
                'admin_adjust',
                null,
                null,
                'Added via CLI'
            );

            $balance = $this->pointsManagement->getBalance($customerId);

            $output->writeln("<info>Success! Added {$points} points to customer {$customerId}</info>");
            $output->writeln("<info>New balance: {$balance->getPoints()} points</info>");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Error: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }
}
