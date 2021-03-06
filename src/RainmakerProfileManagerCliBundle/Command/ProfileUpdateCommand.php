<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class ProfileUpdateCommand extends BaseCommand
{

    protected function configure()
    {
        $this
          ->setName('profile:update')
          ->setDescription('Update all profiles or a specified profile')
          ->addArgument(
              'name',
              InputArgument::OPTIONAL,
              'The name of the profile to update'
          )
          ->addOption(
              'all',
              'a',
              InputOption::VALUE_NONE,
              'Update all profiles'
          );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updateAll = $input->getOption('all');
        $profileName = $input->getArgument('name');
        if (!$updateAll && empty($profileName)) {
            if ($input->isInteractive()) {
                $profileName = $this->askForProfileNameToUpdate($input, $output);
            }
            else {
                $output->writeln("<error>You must specify the name of the profile you wish to update.</error>");
                return 1;
            }
        }

        $masterManifest = new MasterManifest();
        $masterManifest
            ->load();

        if ($updateAll) {
            $masterManifest->updateAllProfiles();
        }
        else {
            $masterManifest->updateProfileWithName($profileName);
        }
    }

}
