<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class ProfileLatestVersionCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('profile:latest-version')
            ->setDescription('Return the latest version of a profile that is available')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the profile'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profileName = trim($input->getArgument('name'));

        $masterManifest = new MasterManifest();
        $masterManifest->load();

        $output->writeln($masterManifest->getLatestProfileVersionForProfileWithName($profileName));
    }

}
