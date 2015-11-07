<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Parser;

class NodeAddCommand extends Command
{

  protected function configure()
  {
    $this
      ->setName('node:add')
      ->setDescription('Add a node into Salt top file');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $yaml = new Parser();
    $value = $yaml->parse(file_get_contents('/srv/saltstack/salt/base/top.sls'));
    print_r($value);
  }

}
