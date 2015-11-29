<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class ProfilePresentCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('profile:present')
            ->setDescription('Check to see if a profile is present')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the profile'
            )
            ->addOption(
                'profile-version',
                null,
                InputOption::VALUE_REQUIRED,
                'The version of the profile'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profileName = trim($input->getArgument('name'));
        $version = trim($input->getOption('profile-version'));

        $masterManifest = new MasterManifest();
        $installed = $masterManifest
            ->load()
            ->hasInstalledProfileProfile($profileName, $version);

        $output->writeln($installed ? 'Profile present' : 'Profile not present');
    }

}
