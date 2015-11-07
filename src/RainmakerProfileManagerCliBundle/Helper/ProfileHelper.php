<?php

namespace RainmakerProfileManagerCliBundle\Helper;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;
use RainmakerProfileManagerCliBundle\Entity\ProfileManifest;
use RainmakerProfileManagerCliBundle\Util\Filesystem;

/**
 * @package RainmakerProfileManagerCliBundle\Helper
 */
class ProfileHelper
{

  public static function AddProfile($url)
  {
    $tmpProfilePath = static::DownloadProfileToTemporaryStorage($url);
    $profileManifest = static::LoadProfileManifest($tmpProfilePath);
    static::InstallProfile($tmpProfilePath, $profileManifest);
    static::LoadMasterManifest(true)->addProfile($profileManifest)->update(true);
    $filesystem = new Filesystem();
    $filesystem->remove($tmpProfilePath);
  }

  public static function RemoveProfile($profileName)
  {
    $profile = static::LoadMasterManifest()->getProfileByName($profileName);
    if (empty($profile)) {
      throw new \RuntimeException('A profile with the name ' . "'$profileName'" . ' could not be found');
    }

    $profileManifest = static::LoadMasterManifest()->loadProfileManifestForProfileWithName($profileName);
    static::LoadMasterManifest(true)->removeProfile($profileManifest)->update(true);
    static::UninstallProfile($profileManifest);
  }

  public static function DownloadProfileToTemporaryStorage($url)
  {
    $filesystem = new Filesystem();
    $tmpDir = $filesystem->makeTempDir();
    GitHelper::CloneRepository($url, $tmpDir);

    return $tmpDir;
  }

  public static function LoadProfileManifest($profilePath)
  {
    if (!static::ProfileManifestPresent($profilePath)) {
      throw new \RuntimeException('Profile manifest is missing');
    }

    return ProfileManifest::LoadProfileManifest($profilePath);
  }

  public static function ProfileManifestPresent($profilePath)
  {
    $filesystem = new Filesystem();
    return $filesystem->exists($profilePath . '/manifest.json');
  }

  public static function LoadMasterManifest($lock = false)
  {
    return MasterManifest::LoadManifest($lock);
  }

  public static function InstallProfile($tmpProfilePath, $profileManifest)
  {
    if (!$profileManifest->isValid()) {
      throw new \RuntimeException('Profile is not valid');
    }

    $profileInstallLocation = $profileManifest->installLocation();

    $filesystem = new Filesystem();
    $filesystem->mkdir($profileInstallLocation);
    $filesystem->mirror($tmpProfilePath, $profileInstallLocation);
    $filesystem->symlink($profileManifest->saltSymlinkOriginPath(), $profileManifest->saltSymlinkTargetPath());
    $filesystem->symlink($profileManifest->pillarSymlinkOriginPath(), $profileManifest->pillarSymlinkTargetPath());
  }

  public static function UninstallProfile($profileManifest)
  {
    $filesystem = new Filesystem();

    if ($filesystem->exists($profileManifest->saltSymlinkTargetPath())) {
      $filesystem->remove($profileManifest->saltSymlinkTargetPath());
    }

    if ($filesystem->exists($profileManifest->pillarSymlinkTargetPath())) {
      $filesystem->remove($profileManifest->pillarSymlinkTargetPath());
    }

    if ($filesystem->exists($profileManifest->installLocation())) {
      $filesystem->remove($profileManifest->installLocation());
    }
  }

  public static function ShowAvailableProfileUpdates($updateRepos = true)
  {
    $masterManifest = static::LoadMasterManifest();
    $profiles = $masterManifest->getProfiles();

    if ($updateRepos) {
      foreach ($profiles as $profile) {
        GitHelper::FetchUpdates(MasterManifest::ProfilePathFromProfile($profile));
      }
    }

    $output = str_pad('Profile', 30) .
      ' ' . str_pad('Type', 15)  .
      ' ' . str_pad('Status', 20, ' ', STR_PAD_LEFT) . "\n";
    foreach ($profiles as $profile) {
      $hasUpdates = GitHelper::CheckProfileForAvailableUpdates(MasterManifest::ProfilePathFromProfile($profile));
      $output .= str_pad($profile->name, 30) .
        ' ' . str_pad($profile->type, 15) .
        ' ' . str_pad($hasUpdates ? 'Update available' : 'Up-to-date', 20, ' ', STR_PAD_LEFT) . "\n";
    }

    return $output;
  }

  public static function UpdateProfileWithName($profileName)
  {
    $masterManifest = static::LoadMasterManifest();
    $profile = $masterManifest->getProfileByName($profileName);
    if (empty($profile)) {
      throw new \RuntimeException('A profile with the name ' . "'$profileName'" . ' could not be found');
    }

    $path = $masterManifest->ProfilePathFromProfile($profile);
    GitHelper::UpdateProfile($path);
  }

  public static function UpdateAllProfiles()
  {
    $masterManifest = static::LoadMasterManifest();
    foreach ($masterManifest->getProfiles() as $profile) {
      static::UpdateProfileWithName($profile->name);
    }
  }

}
