<?php

namespace RainmakerProfileManagerCliBundle\Util;

use RainmakerProfileManagerCliBundle\Process\DownloadProfileRootFsProcess;

/**
 * @package RainmakerProfileManagerCliBundle\Util
 */
class ProfileRootFsDownloader
{
    protected $filesystem = null;
    protected $tmpDirectory = null;
    protected $rootFsUrl = null;
    protected $cacheFullPath = null;

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

    public function download($rootFsUrl, $cacheFullPath)
    {
        $this->rootFsUrl = $rootFsUrl;
        $this->cacheFullPath = $cacheFullPath;
        $filesystem = $this->getFilesystem();
        $this->tmpDirectory = $filesystem->makeTempDir();

        $this->performDownload();
        if ($filesystem->exists($this->cacheFullPath)) {
            $filesystem->remove($this->cacheFullPath);
        }
        else {
            $filesystem->mkdir(dirname($cacheFullPath));
        }
        $filesystem->rename($this->tmpDirectory . DIRECTORY_SEPARATOR . 'rootfs.tgz', $this->cacheFullPath);
        $filesystem->remove($this->tmpDirectory);
    }

    protected function performDownload()
    {
        $process = new DownloadProfileRootFsProcess($this->rootFsUrl, $this->tmpDirectory . DIRECTORY_SEPARATOR . 'rootfs.tgz');
        $process->mustRun();
    }

}
