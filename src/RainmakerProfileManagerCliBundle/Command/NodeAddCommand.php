<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;
use RainmakerProfileManagerCliBundle\Exception\Manifest\NodeAlreadyExistsException;

class NodeAddCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('node:add')
            ->setDescription('Add a node into Salt top file')
            ->addArgument(
                'node',
                InputArgument::OPTIONAL,
                'The unique Salt minion id'
            )
            ->addArgument(
                'profile',
                InputArgument::OPTIONAL,
                'The name of the profile'
            )
            ->addArgument(
                'profile-version',
                InputArgument::OPTIONAL,
                'The version of the profile'
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

        $profile = $input->getArgument('profile');
        if (empty($profile)) {
            if ($input->isInteractive()) {
                $profile = $this->askForProfileName($input, $output);
            }
            else {
                $output->writeln("<error>You must specify the profile name.</error>");
                return 1;
            }
        }

        $version = $input->getArgument('profile-version');
        if (empty($version)) {
            if ($input->isInteractive()) {
                $version = $this->askForProfileVersion($input, $output);
            }
            else {
                $output->writeln("<error>You must specify the profile version.</error>");
                return 1;
            }
        }

        $masterManifest = new MasterManifest();
        try {
            $masterManifest
                ->load()
                ->addNode($minionId, $profile, $version, $this->getSaltStackEnvironment($input));
        }
        catch (NodeAlreadyExistsException $e) {}
    }

}
