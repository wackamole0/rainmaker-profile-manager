<?php

namespace RainmakerProfileManagerCliBundle\Tests\Unit\Mock;

use RainmakerProfileManagerCliBundle\Util\GitRepo;

/**
 * Mocks out RainmakerProfileManagerCliBundle\Util\GitRepo
 *
 * @package RainmakerProfileManagerCliBundle\Tests\Unit\Mock
 */
class GitRepoMock extends GitRepo {

    public static $profilesRepo = array();
    public static $profilesWithUpdates = array();

    protected $commitsBehind = 0;

    public function cloneIt($url, $branch = 'master')
    {
        $this->url = $url;
        $this->branch = $branch;

        $this->createMockGitRepo();
    }

    public function hasAvailableUpdates($fetchUpdates = true)
    {
        if ($fetchUpdates) {
            $this->fetchUpdates();
        }

        return $this->commitsBehind > 0;
    }

    public function fetchUpdates()
    {
        if (isset(static::$profilesWithUpdates[$this->getUrl()])) {
            $this->commitsBehind = static::$profilesWithUpdates[$this->getUrl()];
        }
    }

    public function update($fetchUpdates = true)
    {
        if ($fetchUpdates) {
            $this->fetchUpdates();
        }

        $this->commitsBehind = 0;
    }

    protected function createMockGitRepo()
    {
        $this->getFilesystem()->mkdir($this->path);
        $this->getFilesystem()->mkdir($this->path . DIRECTORY_SEPARATOR . '.git');
        $this->getFilesystem()->putFileContents($this->path . DIRECTORY_SEPARATOR . 'manifest.json', static::$profilesRepo[$this->url]);
        $this->getFilesystem()->mkdir($this->path . DIRECTORY_SEPARATOR . 'salt');
        $this->getFilesystem()->mkdir($this->path . DIRECTORY_SEPARATOR . 'pillar');
    }

}
