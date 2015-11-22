<?php

namespace RainmakerProfileManagerCliBundle\Util;

/**
 * A wrapper class for interacting with the filesystem. This subclasses the Symfony 2 Filesystem class and adds
 * support for reading and writing files.
 *
 * @package RainmakerProfileManagerCliBundle\Util
 */
class Filesystem extends \Symfony\Component\Filesystem\Filesystem
{

    /**
     * @param string $file
     * @return string
     */
    public function getFileContents($file)
    {
        return file_get_contents($file);
    }

    /**
     * @param string $file
     * @param string $contents
     * @return int The function returns the number of bytes that were written to the file, or false on failure.
     */
    public function putFileContents($file, $contents)
    {
        return file_put_contents($file, $contents);
    }

    public function makeTempDir()
    {
        $tempfile = tempnam(sys_get_temp_dir(), '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }
        mkdir($tempfile);
        if (is_dir($tempfile)) {
            return $tempfile;
        }

        return NULL;
    }

}
