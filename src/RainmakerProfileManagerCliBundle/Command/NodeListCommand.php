<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

class NodeListCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('node:list')
            ->setDescription('List nodes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $masterManifest = new MasterManifest();
        $text = $masterManifest
            ->load()
            ->listNodes();
        $output->writeln($text);
    }

}
