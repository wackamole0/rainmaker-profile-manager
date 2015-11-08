<?php

namespace RainmakerProfileManagerCliBundle\Helper;

use RainmakerProfileManagerCliBundle\Entity\SaltTopFile;
use RainmakerProfileManagerCliBundle\Entity\MasterManifest;

/**
 * @package RainmakerProfileManagerCliBundle\Helper
 */
class TopFileHelper
{

  public static function AddNode($minionId, $profileName, $profileVersion)
  {
    $masterManifest = MasterManifest::LoadManifest();
    $profileManifest = $masterManifest->loadProfileManifestForProfileWithName($profileName);

    if (empty($profileManifest)) {
      throw new \RuntimeException('Profile with name ' . "'$profileName'" . ' is not present');
    }

    if ('core' == $profileManifest->getType()) {
      throw new \RuntimeException('This tool cannot map a node to core profile at this time');
    }

    if (!$profileManifest->hasVersion($profileVersion)) {
      throw new \RuntimeException('Profile with name ' . "'$profileName'" . ' does not have a version ' . "'$profileVersion'");
    }

    $profileTopFilePath = $profileManifest->getTopFilePath($profileVersion);
    $topfile = SaltTopFile::LoadTopFile()->addNode($minionId, array($profileTopFilePath))->update();
  }

  public static function RemoveNode($minionId)
  {
    $topfile = SaltTopFile::LoadTopFile();
    if (!$topfile->hasNode($minionId)) {
      throw new \RuntimeException('Node with minion id ' . "'$minionId'" . ' is not present');
    }

    $topfile->removeNode($minionId)->update();
  }

}
