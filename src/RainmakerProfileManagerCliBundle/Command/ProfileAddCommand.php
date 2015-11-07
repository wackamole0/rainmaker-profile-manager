<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Helper\ProfileHelper;

class ProfileAddCommand extends Command
{

  protected function configure()
  {
    $this
      ->setName('profile:add')
      ->setDescription('Add a profile')
      ->addArgument(
        'url',
        InputArgument::OPTIONAL,
        'The Git repository URL of the profile'
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $profileUrl = $input->getArgument('url');
    if (empty($profileUrl)) {
      if ($input->isInteractive()) {
        $profileUrl = $this->askForProfileGitUrl($input, $output);
      }
      else {
        $output->writeln("<error>You must specify the Git URL for the profile.</error>");
        return 1;
      }
    }

    ProfileHelper::AddProfile($profileUrl);
  }

  protected function askForProfileGitUrl(InputInterface $input, OutputInterface $output)
  {
    $text = 'Enter the Git URL of the profile: ';
    return $this->getHelper('question')->ask($input, $output, new Question($text));
  }

}
