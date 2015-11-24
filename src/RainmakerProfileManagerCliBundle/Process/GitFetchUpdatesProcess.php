<?php

namespace RainmakerProfileManagerCliBundle\Process;

use Symfony\Component\Process\Process;

/**
 * .
 *
 * @package Rainmaker\Process
 * @return void
 */
class GitFetchUpdatesProcess extends Process {

    public function __construct($path, $branch = 'master')
    {
        parent::__construct("git fetch origin $branch", $path);
    }

}
