<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Helper\ProfileHelper;

class ProfileShowUpdatesCommand extends Command
{

  protected function configure()
  {
    $this
      ->setName('profile:show-updates')
      ->setDescription('List profiles and show if updates are available');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $output->writeln(ProfileHelper::ShowAvailableProfileUpdates());
  }

}
