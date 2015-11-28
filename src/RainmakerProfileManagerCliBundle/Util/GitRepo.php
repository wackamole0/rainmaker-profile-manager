<?php

namespace RainmakerProfileManagerCliBundle\Util;
use RainmakerProfileManagerCliBundle\Process\GitCloneProcess;
use RainmakerProfileManagerCliBundle\Process\GitCountCommitsBehind;
use RainmakerProfileManagerCliBundle\Process\GitFetchUpdatesProcess;
use RainmakerProfileManagerCliBundle\Process\GitUpdateProfileProcess;

/**
 * @package RainmakerProfileManagerCliBundle\Util
 */
class GitRepo
{

    public static $processRunnerClass = null;

    protected $filesystem = null;
    protected $path = null;
    protected $url = null;
    protected $branch = null;

    /**
     * @param string $url
     * @param string $path
     * @param string $branch
     *
     * @return GitRepo
     */
    public static function CloneRepo($url, $path, $branch = 'master', $filesystem = null)
    {
        $class = get_called_class();
        $repo = new $class($path, $url);
        if (!is_null($filesystem)) {
            $repo->setFilesystem($filesystem);
        }
        $repo->cloneIt($url, $branch);
        return $repo;
    }

    public function __construct($path, $url, $branch = 'master')
    {
        if (is_null(static::$processRunnerClass)) {
            static::$processRunnerClass = '\RainmakerProfileManagerCliBundle\Process\ProcessRunner';
        }

        $this->path = $path;
        $this->url = $url;
        $this->branch = $branch;
    }

    public function getFilesystem()
    {
        if (is_null($this->filesystem)) {
            $this->filesystem = new Filesystem();
        }
        return $this->filesystem;
    }

    public function setFilesystem($fs)
    {
        $this->filesystem = $fs;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function cloneIt($url, $branch = 'master')
    {
        $this->url = $url;
        $this->branch = $branch;

        $process = new GitCloneProcess($url, $this->getPath(), $branch);
        $process->mustRun();
    }

    public function hasAvailableUpdates($fetchUpdates = true)
    {
        if ($fetchUpdates) {
            $this->fetchUpdates();
        }

        $process = new GitCountCommitsBehind($this->getPath(), $this->getBranch());
        $process->mustRun();
        return (int)trim($process->getOutput()) > 0;
    }

    public function fetchUpdates()
    {
        $process = new GitFetchUpdatesProcess($this->getPath(), $this->getBranch());
        $process->mustRun();
    }

    public function update($fetchUpdates = true)
    {
        if ($fetchUpdates) {
            $this->fetchUpdates();
        }

        $process = new GitUpdateProfileProcess($this->getPath(), $this->getBranch());
        $process->mustRun();
    }
}
