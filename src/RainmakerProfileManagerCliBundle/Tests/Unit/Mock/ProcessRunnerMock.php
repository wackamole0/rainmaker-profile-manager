<?php

namespace RainmakerProfileManagerCliBundle\Tests\Unit\Mock;

use RainmakerProfileManagerCliBundle\Process\ProcessRunner;
use Symfony\Component\Process\Process;

/**
 * Mocks out RainmakerProfileManagerCliBundle\Process\ProcessRunner so that the process passed to it is never actually executed but
 * still return a successful status to the caller.
 *
 * @package RainmakerProfileManagerCliBundle\Tests\Unit\Mock
 */
class ProcessRunnerMock extends ProcessRunner {

  protected $processOutput = array();

  public function run(Process $process, $returnOutput = true)
  {
    if ($returnOutput) {
      return $this->getProcessOutput($process);
    }
  }

  public function getProcessOutput(Process $process)
  {
    $class = get_class($process);
    if (isset($this->processOutput[$class])) {
      return $this->processOutput[$class];
    }
  }

  public function addProcessOutput($class, $output)
  {
    $this->processOutput[$class] = $output;
  }

}
