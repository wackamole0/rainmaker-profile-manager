<?php

namespace RainmakerProfileManagerCliBundle\Entity;

use RainmakerProfileManagerCliBundle\Util\Filesystem;
use RainmakerProfileManagerCliBundle\Util\GitRepo;

/**
 * @package RainmakerProfileManagerCliBundle\Entity
 */
class Profile
{
    public static $profilesLocationBasePath = '/srv/saltstack/profiles';
    public static $saltStateTreeBasePath = '/srv/saltstack/salt';
    public static $pillarDataTreeBasePath = '/srv/saltstack/pillar';
    public static $profileManifestBaseName = 'manifest.json';
    public static $processRunnerClass = null;

    protected $filesystem = null;
    protected $path = null;
    protected $repo = null;
    protected $manifest = null;

    public function __construct($path)
    {
        if (is_null(static::$processRunnerClass)) {
            static::$processRunnerClass = '\RainmakerProfileManagerCliBundle\Util\ProcessRunner';
        }

        $this->path = $path;
        $this->repo = new GitRepo($path);
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

    public function getName()
    {
        return $this->getManifest()->name;
    }

    public function getType()
    {
        return $this->getManifest()->type;
    }

//    public function getUrl() {
//        return $this->getManifest()->;
//    }

    public function getFullPath()
    {
        return $this->path;
    }

    public function getManifestFullPath()
    {
        return $this->getFullPath() . DIRECTORY_SEPARATOR . static::$profileManifestBaseName;
    }

    public function getSaltStateTreeSymlinkFullPath($env = 'base')
    {
        $pathParts = array(static::$saltStateTreeBasePath, $env, 'rainmaker', $this->getManifest()->type);

        if (!in_array($this->getManifest()->type, array('core', 'project', 'branch'))) {
            throw new \RuntimeException('Profile type "' . $this->getManifest()->type . '" is not valid');
        }

        if ('core' != $this->getManifest()->type) {
            $pathParts[] = $this->getManifest()->name;
        }

        return implode(DIRECTORY_SEPARATOR, $pathParts);
    }

    public function getSaltStateTreeSymlinkTargetFullPath()
    {
        return $this->getFullPath() . DIRECTORY_SEPARATOR . 'salt';
    }

    public function getPillarDataTreeSymlinkFullPath($env = 'base')
    {
        $pathParts = array(static::$pillarDataTreeBasePath, $env, 'rainmaker', $this->getManifest()->type);

        if (!in_array($this->getManifest()->type, array('core', 'project', 'branch'))) {
          throw new \RuntimeException('Profile type "' . $this->getManifest()->type . '" is not valid');
        }

        if ('core' != $this->getManifest()->type) {
          $pathParts[] = $this->getManifest()->name;
        }

        return implode(DIRECTORY_SEPARATOR, $pathParts);
    }

    public function getPillarDataTreeSymlinkTargetFullPath()
    {
        return $this->getFullPath() . DIRECTORY_SEPARATOR . 'pillar';
    }

    public function isValid()
    {
        $filesystem = $this->getFilesystem();
        if (!$filesystem->exists($this->getFullPath() . DIRECTORY_SEPARATOR . '/salt')) {
            throw new \RuntimeException("Profile is missing a 'salt' directory");
        }

        if (!$filesystem->exists($this->getFullPath() . DIRECTORY_SEPARATOR . '/pillar')) {
            throw new \RuntimeException("Profile is missing a 'pillar' directory");
        }

        $manifest = $this->getManifest();
        if (empty($manifest->type)) {
            throw new \RuntimeException("Profile manifest is missing a 'type' attribute");
        }

        if (empty($manifest->name)) {
            throw new \RuntimeException("Profile manifest is missing a 'name' attribute");
        }

        if (!isset($manifest->profiles) || count($manifest->profiles) < 1) {
            throw new \RuntimeException("Profile manifest is missing a 'profiles' attribute or the attribute is empty");
        }

        foreach ($manifest->profiles as $profileVersion) {
            if (empty($profileVersion->version)) {
                throw new \RuntimeException("Profile manifest has a profile version with missing 'version' attribute");
            }
        }

        return true;
    }

    public function installPath($basePath = null)
    {
        $pathParts = array(
            !empty($basePath) ? $basePath : static::$profilesLocationBasePath,
            $this->getManifest()->type
        );

        if (!in_array($this->getManifest()->type, array('core', 'project', 'branch'))) {
            throw new \RuntimeException('Profile type "' . $this->getManifest()->type . '" is not valid');
        }

        if ('core' != $this->getManifest()->type) {
            $pathParts[] = $this->getManifest()->name;
        }

        return implode(DIRECTORY_SEPARATOR, $pathParts);
    }

    protected function getManifest()
    {
        if (is_null($this->manifest)) {
            $this->loadManifest();
        }

        return $this->manifest;
    }

    protected function loadManifest()
    {
        $manifestFullPath = $this->path . '/' . static::$profileManifestBaseName;
        if (!$this->getFilesystem()->exists($manifestFullPath)) {
            throw new \RuntimeException('Cannot load manifest at path ' . $manifestFullPath);
        }

        $this->manifest = json_decode($this->getFilesystem()->getFileContents($manifestFullPath));
    }

    public function enable()
    {
        $filesystem = $this->getFilesystem();

        if (!$filesystem->exists($this->getSaltStateTreeSymlinkFullPath())) {
            $filesystem->mkdir(dirname($this->getSaltStateTreeSymlinkFullPath()));
            $filesystem->symlink($this->getSaltStateTreeSymlinkTargetFullPath(), $this->getSaltStateTreeSymlinkFullPath());
        }

        if (!$filesystem->exists($this->getPillarDataTreeSymlinkFullPath())) {
            $filesystem->mkdir(dirname($this->getPillarDataTreeSymlinkFullPath()));
            $filesystem->symlink($this->getPillarDataTreeSymlinkTargetFullPath(), $this->getPillarDataTreeSymlinkFullPath());
        }
    }

    public function disable()
    {
        $filesystem = $this->getFilesystem();

        if ($filesystem->exists($this->getSaltStateTreeSymlinkFullPath())) {
            $filesystem->remove($this->getSaltStateTreeSymlinkFullPath());
        }

        if ($filesystem->exists($this->getPillarDataTreeSymlinkFullPath())) {
            $filesystem->remove($this->getPillarDataTreeSymlinkFullPath());
        }
    }

    public function hasVersion($version)
    {
        foreach ($this->getManifest()->profiles as $profileVersion) {
            if ($profileVersion->version == $version) {
                return true;
            }
        }

        return false;
    }

    public function getSaltTopFileRelativePath($version)
    {
        return $this->getTopFileRelativePath($version);
    }

    public function getPillarTopFileRelativePath($version)
    {
        return $this->getTopFileRelativePath($version);
    }

    protected function getTopFileRelativePath($version)
    {
        if (!$this->hasVersion($version)) {
            throw new \RuntimeException("Profile '" . $this->getName() . "' does not have a version '" . $version .  "'");
        }

        $path_parts = array('rainmaker', $this->getType());
        if ('core' != $this->getType()) {
            $path_parts[] = $this->getName();
        }

        $path_parts[] = 'v' . str_replace('.', '_', $version);

        return implode(DIRECTORY_SEPARATOR, $path_parts);
    }

}
