<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class NodeRemoveCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('node:remove')
            ->setDescription('Remove a node from the Salt top file')
            ->addArgument(
                'node',
                InputArgument::OPTIONAL,
                'The unique Salt minion id'
            )
            ->addOption(
                'salt-environment',
                null,
                InputOption::VALUE_REQUIRED,
                'The Saltstack environment'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $minionId = $input->getArgument('node');
        if (empty($minionId)) {
            if ($input->isInteractive()) {
                $minionId = $this->askForNodeMinionId($input, $output);
            }
            else {
                $output->writeln("<error>You must specify the Salt minion id.</error>");
                return 1;
            }
        }

        $masterManifest = new MasterManifest();
        $masterManifest
            ->load()
            ->removeNode($minionId, $this->getSaltStackEnvironment($input));
    }

}
