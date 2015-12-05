<?php

namespace RainmakerProfileManagerCliBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class BaseCommand extends Command
{

    protected static $environments = array(
        'prod' => 'base',
        'testbed' => 'testbed',
        'profile-builder' => 'profile-builder',
        'builder' => 'builder'
    );

    /**
     * Environment utility methods
     */


    protected function getEnvironment()
    {
        return $this->getApplication()->getKernel()->getEnvironment();
    }

    protected function getSaltStackEnvironment(InputInterface $input = null)
    {
        if (!is_null($input)) {
            $saltEnv = $input->getOption('salt-environment');
            if (!empty($saltEnv) && isset(static::$environments[$saltEnv])) {
                return static::$environments[$saltEnv];
            }
        }

        if (!isset(static::$environments[$this->getEnvironment()])) {
            throw new \RuntimeException("The environment '" . $this->getEnvironment() . "' is not valid");
        }

        return static::$environments[$this->getEnvironment()];
    }


    /**
     * Question utility methods
     */


    protected function askForProfileGitUrl(InputInterface $input, OutputInterface $output, $text = null)
    {
        $text = !empty($text) ? $text : 'Enter the Git URL of the profile: ';
        return $this->getHelper('question')->ask($input, $output, new Question($text));
    }

    protected function askForProfileName(InputInterface $input, OutputInterface $output, $text = null)
    {
        $text = !empty($text) ? $text : 'Enter the profile name: ';
        return $this->getHelper('question')->ask($input, $output, new Question($text));
    }

    protected function askForProfileNameToUpdate(InputInterface $input, OutputInterface $output, $text = null)
    {
        $text = !empty($text) ? $text : 'Enter the name of the profile to update: ';
        return $this->askForProfileName($input, $output, $text);
    }

    protected function askForProfileNameToRemove(InputInterface $input, OutputInterface $output, $text = null)
    {
        $text = !empty($text) ? $text : 'Enter the name of the profile to remove: ';
        return $this->askForProfileName($input, $output, $text);
    }

    protected function askForProfileVersion(InputInterface $input, OutputInterface $output, $text = null)
    {
        $text = !empty($text) ? $text : 'Enter the profile version: ';
        return $this->getHelper('question')->ask($input, $output, new Question($text));
    }

    protected function askForNodeMinionId(InputInterface $input, OutputInterface $output, $text = null)
    {
        $text = !empty($text) ? $text : 'Enter the Salt minion id: ';
        return $this->getHelper('question')->ask($input, $output, new Question($text));
    }

}
