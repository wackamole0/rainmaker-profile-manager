<?php

namespace RainmakerProfileManagerCliBundle\Entity;

/**
 * @package RainmakerProfileManagerCliBundle\Entity
 */
class MasterManifest
{

  protected static $profilesLocationBasePath = '/srv/saltstack/profiles';

  protected $manifestFilename = '/srv/saltstack/profiles/manifest.json';
  protected $fh;
  protected $decodedJson;

  public static function LoadManifest($lock = false)
  {
    $manifest = new MasterManifest();
    $manifest->load($lock);
    $manifest->normalise();
    return $manifest;
  }

  public function __constructor()
  {
    $this->decodedJson = new \stdClass();
  }

  public function getProfiles()
  {
    return $this->decodedJson->profiles;
  }

  public function load($lock, $filename = null)
  {
    if (!empty($filename)) {
      $this->manifestFilename = $filename;
    }

    $fh = fopen($this->manifestFilename, 'r+');
    if ($lock) {
      flock($fh, LOCK_EX);
      $this->fh = $fh;
    }
    $content = fread($fh, filesize($this->manifestFilename));
    $this->decodedJson = json_decode($content);
    if (!$lock) {
      fclose($fh);
    }

    return $this;
  }

  public function update($unlock = false)
  {
    $fh = $this->fh;
    if (!$fh) {
      $fh = fopen($this->manifestFilename, 'r+');
    }

    ftruncate($fh, 0);
    fseek($fh, 0);
    fwrite($fh, json_encode($this->decodedJson));

    if ($this->fh) {
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
    if ($this->fh) {
      fclose($this->fh);
    }

    return $this;
  }

  public function normalise()
  {
    if (!isset($this->decodedJson->profiles)) {
      $this->decodedJson->profiles = array();
    }
  }

  public function profilePresent($profileManifest)
  {
    foreach ($this->decodedJson->profiles as $profile) {
      if ($profile->name == $profileManifest->getName() && $profile->type == $profileManifest->getType()) {
        return true;
      }
    }

    return false;
  }

  public function addProfile(ProfileManifest $profileManifest)
  {
    if ($this->profilePresent($profileManifest)) {
      throw new \RuntimeException('Profile already installed');
    }

    $this->decodedJson->profiles[] = $profileManifest->masterMetadata();
    $this->sortProfiles();

    return $this;
  }

  public function removeProfile(ProfileManifest $profileManifest)
  {
    if (!$this->profilePresent($profileManifest)) {
      throw new \RuntimeException('Profile is not installed');
    }

    $this->decodedJson->profiles[] = $profileManifest->masterMetadata();
    foreach ($this->decodedJson->profiles as $index => $profile) {
      if ($profile->name == $profileManifest->getName()) {
        unset($this->decodedJson->profiles[$index]);
      }
    }
    $this->sortProfiles();

    return $this;
  }

  public static function ProfilePathFromProfile($profile)
  {
    $path = static::$profilesLocationBasePath;
    if ('core' == $profile->type) {
      $path .= '/core';
    }
    else {
      $path .= '/' . $profile->type . '/' . $profile->name;
    }

    return $path;
  }

  public function getFilesystemPathsOfProfiles()
  {
    $paths = array();

    foreach ($this->getProfiles() as $profile) {
      $paths[] = $this->ProfilePathFromProfile($profile);
    }

    return $paths;
  }

  public function getProfileByName($profileName)
  {
    foreach ($this->getProfiles() as $profile) {
      if ($profile->name == $profileName) {
        return $profile;
      }
    }

    return null;
  }

  public function loadProfileManifestForProfileWithName($profileName)
  {
    return ProfileManifest::LoadProfileManifest($this->ProfilePathFromProfile($this->getProfileByName($profileName)));
  }

  protected function sortProfiles()
  {
    usort($this->decodedJson->profiles, array($this, 'cmpMasterManifestProfiles'));
  }

  protected function cmpMasterManifestProfiles($profile1, $profile2)
  {
    return strcmp($profile1->name, $profile2->name);
  }

}
