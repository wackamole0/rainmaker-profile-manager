<?php

namespace RainmakerProfileManagerCliBundle\Util;

use RainmakerProfileManagerCliBundle\Entity\Profile;

/**
 * @package RainmakerProfileManagerCliBundle\Util
 */
class ProfileInstaller
{
    public static $gitRepoClass = null;

    protected $filesystem = null;
    protected $url;
    protected $profileBasePath;
    protected $branch;
    protected $tmpDirectory = null;

    /**
     * @var Profile
     */
    protected $profile = null;

    public function __construct($url, $path, $branch = 'master')
    {
        if (is_null(static::$gitRepoClass)) {
            static::$gitRepoClass = '\RainmakerProfileManagerCliBundle\Util\GitRepo';
        }

        $this->url = $url;
        $this->profileBasePath = $path;
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

    public function download()
    {
        $filesystem = $this->getFilesystem();
        $this->tmpDirectory = $filesystem->makeTempDir();
        $this->cloneRepo($this->tmpDirectory);
        $this->profile = new Profile($this->tmpDirectory);
        $this->profile->setFilesystem($this->getFilesystem());

        try {
            $this->profile->isValid();
        }
        catch (\RuntimeException $e) {
            throw new \RuntimeException('Profile is not valid', 0, $e);
        }

        return $this;
    }

    public function verify()
    {
        return $this;
    }

    /**
     * @return GitRepo
     */
    public function install()
    {
        $filesystem = $this->getFilesystem();
        $filesystem->mkdir($this->profileInstallPath());
        $filesystem->mirror($this->tmpDirectory, $this->profileInstallPath());
        $filesystem->remove($this->tmpDirectory);

        $profile = new Profile($this->profileInstallPath());
        $profile->setFilesystem($this->getFilesystem());
        return $profile;
    }

    protected function profileInstallPath()
    {
        return $this->profile->installPath($this->profileBasePath);
    }

    protected function cloneRepo($path)
    {
        $class = static::$gitRepoClass;
        return $class::CloneRepo($this->url, $path, $this->branch, $this->getFilesystem());
    }

}
