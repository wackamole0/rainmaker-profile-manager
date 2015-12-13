<?php

namespace RainmakerProfileManagerCliBundle\Tests\Unit\Mock;

use RainmakerProfileManagerCliBundle\Util\ProfileRootFsDownloader;

/**
 * Mocks out RainmakerProfileManagerCliBundle\Util\ProfileRootFsDownloader
 *
 * @package RainmakerProfileManagerCliBundle\Tests\Unit\Mock
 */
class ProfileRootFsDownloaderMock extends ProfileRootFsDownloader {

    protected function performDownload()
    {
        $this->getFilesystem()->mkdir($this->tmpDirectory);
        $this->getFilesystem()->touch($this->tmpDirectory . DIRECTORY_SEPARATOR . 'rootfs.tgz');
    }

}
