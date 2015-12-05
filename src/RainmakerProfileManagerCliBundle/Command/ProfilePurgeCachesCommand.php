<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class ProfilePurgeCachesCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('profile:purge-caches')
            ->setDescription('Purge all downloaded profile root filesystems from the caches');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
      $masterManifest = new MasterManifest();
      $masterManifest
          ->load()
          ->purgeCaches();
  }

}
