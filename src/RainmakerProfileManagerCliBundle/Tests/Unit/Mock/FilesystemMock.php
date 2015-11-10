<?php

namespace RainmakerProfileManagerCliBundle\Tests\Unit\Mock;

use RainmakerProfileManagerCliBundle\Util\Filesystem;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;

/**
 * Mocks out RainmakerProfileManagerCliBundle\Util\Filesystem so that instead of interacting with the real filesystem it instead
 * interacts with a mock filesystem provided by vfsStream
 *
 * @package RainmakerProfileManagerCliBundle\Tests\Unit\Mock
 */
class FilesystemMock extends Filesystem
{

  /**
   * @var \org\bovigo\vfs\vfsStreamDirectory $root
   */
  protected $root = null;

  public function __construct()
  {
    $this->root = vfsStream::setup();
  }

  public function copyFromFileSystem($path)
  {
    vfsStream::copyFromFileSystem($path);
  }

  public function dumpFilesystemStructure()
  {
    vfsStream::inspect(new vfsStreamPrintVisitor());
  }

  protected function pathToUrl($path = '')
  {
    //@todo Consider adding hasChild() test and throw exception if test fails?
    if ($this->root->hasChild(ltrim($path, '/'))) {
      return $this->root->getChild(ltrim($path, '/'))->url();
    }

    return $this->root->url() . $path;
  }

  protected function pathToUrlWrapper($paths)
  {
    if (!$paths instanceof \Traversable) {
      $paths = new \ArrayObject(is_array($paths) ? $paths : array($paths));
    }

    $pathsTranslated = array();
    foreach ($paths as $path) {
      $pathsTranslated[] = $this->pathToUrl($path);
    }

    return new \ArrayObject($pathsTranslated);
  }

  public function copy($originFile, $targetFile, $override = false)
  {
    parent::copy($this->pathToUrl($originFile), $this->pathToUrl($targetFile), $override);
  }

  public function mkdir($dirs, $mode = 0777)
  {
    parent::mkdir($this->pathToUrlWrapper($dirs), $mode);
  }

  public function exists($files)
  {
    return parent::exists($this->pathToUrlWrapper($files));
  }

  public function touch($files, $time = null, $atime = null)
  {
    parent::touch($this->pathToUrlWrapper($files), $time, $atime);
  }

  public function remove($files)
  {
    parent::remove($this->pathToUrlWrapper($files));
  }

  public function chmod($files, $mode, $umask = 0000, $recursive = false)
  {
    parent::chmod($this->pathToUrlWrapper($files), $mode, $umask, $recursive);
  }

  public function chown($files, $user, $recursive = false)
  {
    parent::chown($this->pathToUrlWrapper($files), $user, $recursive);
  }

  public function chgrp($files, $group, $recursive = false)
  {
    parent::chgrp($this->pathToUrlWrapper($files), $group, $recursive);
  }

  public function rename($origin, $target, $overwrite = false)
  {
    parent::rename($this->pathToUrl($origin), $this->pathToUrl($target), $overwrite);
  }

  public function symlink($originDir, $targetDir, $copyOnWindows = false)
  {
    parent::symlink($this->pathToUrl($originDir), $this->pathToUrl($targetDir), $copyOnWindows);
  }

  public function makePathRelative($endPath, $startPath)
  {
    parent::makePathRelative($this->pathToUrl($endPath), $this->pathToUrl($startPath));
  }

  public function mirror($originDir, $targetDir, \Traversable $iterator = null, $options = array())
  {
    parent::mirror($this->pathToUrl($originDir), $this->pathToUrl($targetDir), $iterator, $options);
  }

  public function isAbsolutePath($file)
  {
    parent::isAbsolutePath($this->pathToUrl($file));
  }

  public function dumpFile($filename, $content, $mode = 0666)
  {
    parent::dumpFile($this->pathToUrl($filename), $content, $mode);
  }

  public function getFileContents($file)
  {
    return parent::getFileContents($this->pathToUrl($file));
  }

  public function putFileContents($file, $contents)
  {
    return parent::putFileContents($this->pathToUrl($file), $contents);
  }

}
