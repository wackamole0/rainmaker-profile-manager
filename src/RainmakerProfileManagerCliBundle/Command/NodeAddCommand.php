<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class NodeAddCommand extends Command
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
      $masterManifest
          ->load()
          ->addNode($minionId, $profile, $version);
    }

    protected function askForNodeMinionId(InputInterface $input, OutputInterface $output)
    {
        $text = 'Enter the Salt minion id: ';
        return $this->getHelper('question')->ask($input, $output, new Question($text));
    }

    protected function askForProfileName(InputInterface $input, OutputInterface $output)
    {
        $text = 'Enter the profile name: ';
        return $this->getHelper('question')->ask($input, $output, new Question($text));
    }

    protected function askForProfileVersion(InputInterface $input, OutputInterface $output)
    {
        $text = 'Enter the profile version: ';
        return $this->getHelper('question')->ask($input, $output, new Question($text));
    }

}
