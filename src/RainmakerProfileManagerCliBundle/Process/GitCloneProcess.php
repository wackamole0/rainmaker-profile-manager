<?php

namespace RainmakerProfileManagerCliBundle\Process;

use Symfony\Component\Process\Process;

/**
 * .
 *
 * @package Rainmaker\Process
 * @return void
 */
class GitCloneProcess extends Process {

    public function __construct($url, $path)
    {
        parent::__construct('git clone ' . $url . ' . ', $path);
    }

}
