<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Helper\ProfileHelper;

class ProfileUpdateCommand extends Command
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
        $profileName = $this->askForProfileName($input, $output);
      }
      else {
        $output->writeln("<error>You must specify the name of the profile you wish to update.</error>");
        return 1;
      }
    }

    if ($updateAll) {
      ProfileHelper::UpdateAllProfiles();
    }
    else {
      ProfileHelper::UpdateProfileWithName($profileName);
    }
  }

  protected function askForProfileName(InputInterface $input, OutputInterface $output)
  {
    $text = 'Enter the name of the profile to update: ';
    return $this->getHelper('question')->ask($input, $output, new Question($text));
  }

}
