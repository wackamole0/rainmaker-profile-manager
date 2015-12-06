<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class ProfileListCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('profile:list')
            ->setDescription('List profiles');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $masterManifest = new MasterManifest();
        $text = $masterManifest
            ->load()
            ->showAvailableUpdates(false);
        $output->writeln($text);
    }

}
