<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class ProfileRemoveCommand extends BaseCommand
{

    protected function configure()
    {
        $this
          ->setName('profile:remove')
          ->setDescription('Remove a profile')
          ->addArgument(
              'name',
              InputArgument::OPTIONAL,
              'The name of the profile to update'
          );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profileName = $input->getArgument('name');
        if (empty($profileName)) {
            if ($input->isInteractive()) {
                $profileName = $this->askForProfileNameToRemove($input, $output);
            }
            else {
                $output->writeln("<error>You must specify the name of the profile you wish to remove.</error>");
                return 1;
            }
        }

        $masterManifest = new MasterManifest();
        $masterManifest
            ->load()
            ->removeProfileByName($profileName);
    }

}
