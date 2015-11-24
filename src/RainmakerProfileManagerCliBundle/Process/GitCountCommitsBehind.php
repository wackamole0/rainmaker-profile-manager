<?php

namespace RainmakerProfileManagerCliBundle\Process;

use Symfony\Component\Process\Process;

/**
 * .
 *
 * @package Rainmaker\Process
 * @return void
 */
class GitCountCommitsBehind extends Process {

    public function __construct($path, $branch = 'master')
    {
        parent::__construct("git rev-list --count --right-only $branch...origin/$branch", $path);
    }

}
