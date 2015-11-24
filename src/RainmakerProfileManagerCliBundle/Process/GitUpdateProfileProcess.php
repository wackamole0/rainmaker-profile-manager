<?php

namespace RainmakerProfileManagerCliBundle\Process;

use Symfony\Component\Process\Process;

/**
 * .
 *
 * @package Rainmaker\Process
 * @return void
 */
class GitUpdateProfileProcess extends Process {

    public function __construct($path, $branch = 'master')
    {
        parent::__construct("git merge --ff origin/$branch", $path);
    }

}
