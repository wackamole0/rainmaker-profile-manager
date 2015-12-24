<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class ProfileVersionMetadataCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('profile:version-metadata')
            ->setDescription('Return the metadata (such as mounts and exports) of a specified version of a profile')
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
        $masterManifest->load();

        if (empty($version)) {
            $profile = $masterManifest->getProfile($profileName);
            $version = $profile->getLatestVersion();
        }

        $output->writeln($masterManifest->getProfileVersionMountsAndExports($profileName, $version));
    }

}
