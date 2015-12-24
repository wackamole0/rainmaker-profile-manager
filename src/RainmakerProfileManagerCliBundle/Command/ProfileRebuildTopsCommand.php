<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class ProfileRebuildTopsCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('profile:rebuild-tops')
            ->setDescription('Rebuild Salt and Pillar top files')
            ->addOption(
                'salt-environment',
                null,
                InputOption::VALUE_REQUIRED,
                'The Saltstack environment'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $masterManifest = new MasterManifest();
        $masterManifest
            ->load()
            ->rebuildTopFiles($input->getOption('salt-environment'));
    }

}
