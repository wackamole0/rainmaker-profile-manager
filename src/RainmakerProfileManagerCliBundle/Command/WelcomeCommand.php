<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WelcomeCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('welcome')
            ->setDescription('Welcome to the Rainmaker Profile Manager');
    }

    public function isEnabled()
    {
        global $argv;
        return !isset($argv[1]) || $argv[1] != 'list';
    }

    public function isLocal()
    {
        return TRUE;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Welcome to the Rainmaker Profile Manager!");
        $output->writeln("Type <info>rprofmgr list</info> to see all available commands.");
    }

}
