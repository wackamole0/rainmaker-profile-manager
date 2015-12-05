<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class ManifestInstallCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('manifest:install')
            ->setDescription('Install all profiles and nodes from master manifest');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
      $masterManifest = new MasterManifest();
      $masterManifest
          ->load()
          ->install();
  }

}
