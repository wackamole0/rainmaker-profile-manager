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

    public function cloneIt($url, $branch = 'master')
    {
        $this->url = $url;
        $this->branch = $branch;

        $this->createMockGitRepo();
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
