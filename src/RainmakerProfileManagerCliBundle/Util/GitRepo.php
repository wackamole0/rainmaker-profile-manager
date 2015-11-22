<?php

namespace RainmakerProfileManagerCliBundle\Util;

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
        $repo = new $class($path);
        if (!is_null($filesystem)) {
            $repo->setFilesystem($filesystem);
        }
        $repo->cloneIt($url, $branch);
        return $repo;
    }

    public function __construct($path)
    {
        if (is_null(static::$processRunnerClass)) {
            static::$processRunnerClass = '\RainmakerProfileManagerCliBundle\Process\ProcessRunner';
        }

        $this->path = $path;
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

    public function cloneIt($url, $branch = 'master')
    {
        $this->url = $url;
        $this->branch = $branch;

        throw new \RuntimeException('Implement ' . __CLASS__ . '::' . __METHOD__);
    }
}
