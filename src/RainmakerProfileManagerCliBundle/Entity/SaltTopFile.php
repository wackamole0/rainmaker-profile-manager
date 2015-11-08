<?php

namespace RainmakerProfileManagerCliBundle\Entity;

use Symfony\Component\Yaml\Yaml;

/**
 * @package RainmakerProfileManagerCliBundle\Entity
 */
class SaltTopFile
{
  protected static $topfile = '/srv/saltstack/salt/base/top.sls';

  protected $lockedFh;
  protected $parsedYaml;

  public static function LoadTopFile($lock = false)
  {
    $topfile = new SaltTopFile();
    $topfile->load($lock);
    $topfile->normalise();
    return $topfile;
  }

  public function __constructor()
  {
    $this->parsedYaml = array();
  }

  public function load($lock, $topfile = null)
  {
    if (!empty($topfile)) {
      static::$topfile = $topfile;
    }

    $fh = fopen(static::$topfile, 'r+');
    if ($lock) {
      flock($fh, LOCK_EX);
      $this->lockedFh = $fh;
    }

    $content = fread($fh, filesize(static::$topfile));
    $this->parsedYaml = Yaml::parse($content);

    if (!$lock) {
      fclose($fh);
    }

    return $this;
  }

  public function update($unlock = false)
  {
    $fh = $this->lockedFh;
    if (!$fh) {
      $fh = fopen(static::$topfile, 'r+');
    }

    ftruncate($fh, 0);
    fseek($fh, 0);
    fwrite($fh, Yaml::dump($this->parsedYaml));

    if ($this->lockedFh) {
      if ($unlock) {
        $this->releaseLock();
      }
    }
    else {
      fclose($fh);
    }

    return $this;
  }

  public function releaseLock()
  {
    if ($this->lockedFh) {
      fclose($this->lockedFh);
    }

    return $this;
  }

  protected function normalise()
  {
    if (!isset($this->parsedYaml['base'])) {
      $this->parsedYaml['base'] = array();
    }
  }

  public function hasNode($minionId) {
    return isset($this->parsedYaml['base'][$minionId]);
  }

  public function addNode($minionId, array $profileTopFilePaths)
  {
    $this->parsedYaml['base'][$minionId] = $profileTopFilePaths;

    return $this;
  }

  public function removeNode($minionId)
  {
    unset($this->parsedYaml['base'][$minionId]);

    return $this;
  }

}
