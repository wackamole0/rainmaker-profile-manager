<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use RainmakerProfileManagerCliBundle\Helper\TopFileHelper;

class NodeRemoveCommand extends Command
{

  protected function configure()
  {
    $this
      ->setName('node:remove')
      ->setDescription('Remove a node from the Salt top file')
      ->addArgument(
        'node',
        InputArgument::OPTIONAL,
        'The unique Salt minion id'
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $minionId = $input->getArgument('node');
    if (empty($minionId)) {
      if ($input->isInteractive()) {
        $minionId = $this->askForNodeMinionId($input, $output);
      }
      else {
        $output->writeln("<error>You must specify the Salt minion id.</error>");
        return 1;
      }
    }

    TopFileHelper::RemoveNode($minionId);
  }

  protected function askForNodeMinionId(InputInterface $input, OutputInterface $output)
  {
    $text = 'Enter the Salt minion id: ';
    return $this->getHelper('question')->ask($input, $output, new Question($text));
  }

  protected function askForProfileName(InputInterface $input, OutputInterface $output)
  {
    $text = 'Enter the profile name: ';
    return $this->getHelper('question')->ask($input, $output, new Question($text));
  }

  protected function askForProfileVersion(InputInterface $input, OutputInterface $output)
  {
    $text = 'Enter the profile version: ';
    return $this->getHelper('question')->ask($input, $output, new Question($text));
  }

}
