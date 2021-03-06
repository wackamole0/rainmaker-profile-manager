<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;
use RainmakerProfileManagerCliBundle\Exception\Manifest\ProfileAlreadyExistsException;

class ProfileAddCommand extends BaseCommand
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
            )
            ->addOption(
                'branch',
                null,
                InputOption::VALUE_REQUIRED,
                'The Git branch to use. The default is the master branch'
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

      $branch = $input->getOption('branch');
      if (empty($branch)) {
          $branch = 'master';
      }

      $masterManifest = new MasterManifest();
      try {
          $masterManifest
              ->load()
              ->installProfileFromUrl($profileUrl, TRUE, $branch);
      }
      catch (ProfileAlreadyExistsException $e) {}
  }

}
