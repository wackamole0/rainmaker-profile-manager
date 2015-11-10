<?php

namespace RainmakerProfileManagerCliBundle\Tests\Unit;

use RainmakerProfileManagerCliBundle\Tests\AbstractUnitTest;
use RainmakerProfileManagerCliBundle\Tests\Unit\Mock\FilesystemMock;

/**
 * Unit tests \Rainmaker\Task\Project\Create
 *
 * @package RainmakerProfileManagerCliBundle\Tests\Unit
 */
class CreateTest extends AbstractUnitTest
{

  /**
   * Test building the Rainmaker profiles, Salt top files and Pillar top files from the "master manifest".
   */
  public function testBuildFromManifest()
  {
    $filesystemMock = $this->createFilesystemMock();

  }

  protected function createFilesystemMock()
  {
    $fs = new FilesystemMock();
    $fs->copyFromFileSystem(__DIR__ . '/../fsMocks');

    return $fs;
  }

}
