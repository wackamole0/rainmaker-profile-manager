<?php

namespace RainmakerProfileManagerCliBundle\Entity;

/**
 * @package RainmakerProfileManagerCliBundle\Entity
 */
class ProfileManifest
{

  protected $installLocationBasePath = '/srv/saltstack/profiles';
  protected $saltBasePath = '/srv/saltstack/salt/base/rainmaker';
  protected $pillarBasePath = '/srv/saltstack/pillar/base/rainmaker';
  protected $profilePath;
  protected $decodedJson;

  public static function LoadProfileManifest($profilePath)
  {
    return new ProfileManifest($profilePath);
  }

  public function __construct($profilePath)
  {
    $this->profilePath = $profilePath;
    $contents = file_get_contents($profilePath . '/manifest.json');
    $this->decodedJson = json_decode($contents);
  }

  public function getName()
  {
    return $this->decodedJson->name;
  }

  public function getType()
  {
    return $this->decodedJson->type;
  }

  public function getRepoUrl()
  {
    return $this->decodedJson->repoUrl;
  }

  public function isValid()
  {
    return true;
  }

  public function installLocation()
  {
    if (empty($this->decodedJson->type)) {
      throw new \RuntimeException('Manifest profile type property is missing');
    }

    if (!in_array($this->decodedJson->type, array('core', 'project', 'branch'))) {
      throw new \RuntimeException('Manifest profile type property is not valid');
    }

    if (empty($this->decodedJson->name)) {
      throw new \RuntimeException('Manifest profile name property is missing');
    }

    $path = $this->installLocationBasePath;
    if ('core' == $this->decodedJson->type) {
      $path .= '/core';
    }
    else {
      $path .= '/' . $this->decodedJson->type . '/' . $this->decodedJson->name;
    }

    return $path;
  }

  public function saltSymlinkOriginPath()
  {
    return $this->installLocation() . '/salt';
  }

  public function saltSymlinkTargetPath()
  {
    if ('core' == $this->decodedJson->type) {
      return $this->saltBasePath . '/core';
    }
    else {
      return $this->saltBasePath . '/' . $this->decodedJson->type . '/' . $this->decodedJson->name;
    }
  }

  public function pillarSymlinkOriginPath()
  {
    return $this->installLocation() . '/pillar';
  }

  public function pillarSymlinkTargetPath()
  {
    if ('core' == $this->decodedJson->type) {
      return $this->pillarBasePath . '/core';
    }
    else {
      return $this->pillarBasePath . '/' . $this->decodedJson->type . '/' . $this->decodedJson->name;
    }
  }

  public function masterMetadata()
  {
    $meta = new \stdClass();
    $meta->name = $this->getName();
    $meta->type = $this->getType();
    $meta->url = $this->getRepoUrl();
    return $meta;
  }

  public function hasVersion($version)
  {
    foreach ($this->decodedJson->profiles as $profile) {
      if ($profile->version == $version) {
        return true;
      }
    }

    return false;
  }

  public function getTopFilePath($version)
  {
    return 'rainmaker'
      . '/' . $this->getType()
      . '/' . $this->getName()
      . '/v' . str_replace('.', '_', $version);
  }
}
