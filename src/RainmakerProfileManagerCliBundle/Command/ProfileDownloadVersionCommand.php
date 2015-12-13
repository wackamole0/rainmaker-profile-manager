<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class ProfileDownloadVersionCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('profile:download-version')
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
            )
            ->addOption(
                'overwrite',
                null,
                InputOption::VALUE_NONE,
                'If the root filesystem archive has been downloaded and cached it should be overwritten'
            )
            ->addOption(
                'download-host',
                null,
                InputOption::VALUE_REQUIRED,
                'Overwrite the download host for this profile specified in the profile manifest'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profileName = trim($input->getArgument('name'));
        $version = trim($input->getOption('profile-version'));
        $overwrite = $input->getOption('profile-version') == true;
        $downloadHost = trim($input->getOption('download-host'));

        $masterManifest = new MasterManifest();
        $masterManifest->load();

        if (empty($version)) {
            $profile = $masterManifest->getProfile($profileName);
            $version = $profile->getLatestVersion();
        }

        $masterManifest->downloadProfileRootFs($profileName, $version, $overwrite, $downloadHost);

        $output->writeln('Profile version root filesystem has been downloaded');
    }

}
