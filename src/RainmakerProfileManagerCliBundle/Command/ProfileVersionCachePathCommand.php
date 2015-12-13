<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class ProfileVersionCachePathCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('profile:version-cache-path')
            ->setDescription('Return the LXC cache full path of a specified profile version if it has been downloaded and cached')
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

        $output->writeln($masterManifest->getLxcCacheProfileRootFsFullPath($profileName, $version));
    }

}
