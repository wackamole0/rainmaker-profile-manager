<?php

namespace RainmakerProfileManagerCliBundle\Helper;

use Symfony\Component\Process\Process;

/**
 * @package RainmakerProfileManagerCliBundle\Helper
 */
class GitHelper
{

  public static function CloneRepository($url, $path)
  {
    $process = new Process('git clone ' . $url . ' . ', $path);
    $process->mustRun();
  }

  public static function FetchUpdates($path)
  {
    $process = new Process('git fetch origin master', $path);
    $process->mustRun();
  }

  public static function CheckProfileForAvailableUpdates($path)
  {
    $process = new Process('git rev-list --count --right-only master...origin/master', $path);
    $process->mustRun();
    return (int)trim($process->getOutput());
  }

  public static function UpdateProfile($path)
  {
    static::FetchUpdates($path);
    $process = new Process('git merge --ff origin/master', $path);
    $process->mustRun();
  }

}
