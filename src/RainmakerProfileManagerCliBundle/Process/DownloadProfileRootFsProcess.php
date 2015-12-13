<?php

namespace RainmakerProfileManagerCliBundle\Process;

use Symfony\Component\Process\Process;

/**
 * .
 *
 * @package RainmakerProfileManagerCliBundle\Process
 * @return void
 */
class DownloadProfileRootFsProcess extends Process {

    public function __construct($url, $path)
    {
        parent::__construct("wget --progress=bar -O $path $url");
        $this->setTimeout(null);
    }

}
