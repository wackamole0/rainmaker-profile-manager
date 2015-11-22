<?php

namespace RainmakerProfileManagerCliBundle\Entity;

use RainmakerProfileManagerCliBundle\Util\Filesystem;
use RainmakerProfileManagerCliBundle\Util\ProfileInstaller;

/**
 * @package RainmakerProfileManagerCliBundle\Entity
 */
class MasterManifest
{

    public static $saltstackBasePath = '/srv/saltstack';
    public static $profilesLocationBasePath = '/srv/saltstack/profiles';
    public static $profileManifestBaseName = 'manifest.json';
    public static $profileInstallerClass = null;

    /**
     * @var \RainmakerProfileManagerCliBundle\Util\Filesystem
     */
    protected $filesystem = null;
    protected $manifestFilename = '/srv/saltstack/profiles/manifest.json';
    protected $data = null;

    public static function LoadManifest($lock = false)
    {
        $manifest = new MasterManifest();
        $manifest->load($lock);
        $manifest->normalise();
        return $manifest;
    }

    public function __construct()
    {
        if (is_null(static::$profileInstallerClass)) {
            static::$profileInstallerClass = '\RainmakerProfileManagerCliBundle\Util\ProfileInstaller';
        }

        $this->data = new \stdClass();
    }

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

    /**
     * @return ProfileMeta[]
     */
    protected function getProfilesMetaData()
    {
        $profileMetas = array();
        foreach ($this->data->profiles as $profileInfo) {
            $profileMeta = new ProfileMeta();
            $profileMeta->populate($profileInfo);
            $profileMetas[] = $profileMeta;
        }
        return $profileMetas;
    }

    /**
     * @return Profile[]
     */
    public function getProfiles()
    {
        $profiles = array();
        foreach ($this->getProfilesMetaData() as $profileMeta) {
            $profiles[] = $this->loadProfileFromMeta($profileMeta);
        }

        return $profiles;
    }

    public function getProfile($profileName)
    {
        foreach ($this->getProfiles() as $profile) {
            if ($profile->getName() == $profileName) {
                return $profile;
            }
        }

        return null;
    }

    protected function loadProfileFromMeta(ProfileMeta $profileMeta)
    {
        $profile = new Profile($this->profileFullPath($profileMeta));
        $profile->setFilesystem($this->getFilesystem());
        return $profile;
    }

    public function load($filename = null)
    {
        if (!empty($filename)) {
            $this->manifestFilename = $filename;
        }

        $content = $this->getFilesystem()->getFileContents($this->manifestFilename);
        $this->data = json_decode($content);
        $this->normalise();

        return $this;
    }

    public function install()
    {
        foreach ($this->getProfilesMetaData() as $profileMeta) {
            $installedProfile = $this->installIfMissing($profileMeta);

            $installedProfile->enable();
        }

        $saltTopFile = $this->getSaltTopFile();
        $pillarTopFile = $this->getPillarTopFile();

        foreach ($this->getNodes() as $node) {
            $saltTopFile->addOrUpdate($node);
            $pillarTopFile->addOrUpdate($node);
            $saltTopFile->save();
            $pillarTopFile->save();
        }
    }

    protected function installIfMissing(ProfileMeta $profileMeta)
    {
        if (!$this->profilePresent($profileMeta)) {
            return $this->installProfile($profileMeta);
        }

        return $this->loadProfileFromMeta($profileMeta);
    }

    /**
     * @param $profileMeta
     * @return Profile
     */
    protected function installProfile(ProfileMeta $profileMeta)
    {
        $class = static::$profileInstallerClass;
        $profileInstaller = new $class($profileMeta->getUrl(), static::$profilesLocationBasePath);
        $profile = $profileInstaller
            ->setFilesystem($this->getFilesystem())
            ->download()
            ->verify()
            ->install();

        return $profile;
    }

    /**
     * @param $url
     * @return Profile
     */
    public function installProfileFromUrl($url, $activate = true, $branch = 'master')
    {
        $class = static::$profileInstallerClass;
        $profileInstaller = new $class($url, static::$profilesLocationBasePath, $branch);
        $profile = $profileInstaller
            ->setFilesystem($this->getFilesystem())
            ->download()
            ->verify()
            ->install();

        $this->addProfileToManifest($profile, $url);

        if ($activate) {
            $profile->enable();
        }

        return $profile;
    }

    public function removeProfileByName($profileName)
    {
        if (!$this->profileWithNamePresent($profileName)) {
            throw new \RuntimeException("Cannot uninstall profile with name '$profileName' as it is not installed");
        }

        $profile = $this->getProfile($profileName);
        $profile->disable();

        $filesystem = $this->getFilesystem();
        if ($filesystem->exists($profile->getFullPath())) {
            $filesystem->remove($profile->getFullPath());
        }

        $this->removeProfileFromManifest($profile);

        return $this;
    }

    protected function profilePresent(ProfileMeta $profileMeta)
    {
        return $this->getFilesystem()->exists($this->profileManifestFullPath($profileMeta));
    }

    public function profileWithUrlPresent($url)
    {
        foreach ($this->getProfilesMetaData() as $profileMeta) {
            if ($profileMeta->getUrl() == $url) {
                return true;
            }
        }

        return false;
    }

    public function profileWithNamePresent($profileName)
    {
        foreach ($this->getProfilesMetaData() as $profileMeta) {
            if ($profileMeta->getName() == $profileName) {
                return true;
            }
        }

        return false;
    }

    protected function profileFullPath(ProfileMeta $profileMeta)
    {
        $pathParts = array(static::$profilesLocationBasePath, $profileMeta->getType());
        if (!in_array($profileMeta->getType(), array('core', 'project', 'branch'))) {
            throw new \RuntimeException('Profile type "' . $profileMeta->getType() . '" is not valid');
        }

        if ('core' != $profileMeta->getType()) {
            $pathParts[] = $profileMeta->getName();
        }

        return implode(DIRECTORY_SEPARATOR, $pathParts);
    }

    protected function profileManifestFullPath(ProfileMeta $profileMeta)
    {
        $pathParts = array(static::$profilesLocationBasePath, $profileMeta->getType());
        if (!in_array($profileMeta->getType(), array('core', 'project', 'branch'))) {
            throw new \RuntimeException('Profile type "' . $profileMeta->getType() . '" is not valid');
        }

        if ('core' != $profileMeta->getType()) {
            $pathParts[] = $profileMeta->getName();
        }

        $pathParts[] = static::$profileManifestBaseName;

        return implode(DIRECTORY_SEPARATOR, $pathParts);
    }

    protected function addProfileToManifest(Profile $profile, $url, $branch = 'master')
    {
        $metadata = new \stdClass();
        $metadata->name = $profile->getName();
        $metadata->type = $profile->getType();
        $metadata->branch = $branch;
        $metadata->url = $url;

        $this->data->profiles[] = $metadata;
        $this->sortProfiles();

        return $this;
    }

    protected function removeProfileFromManifest(Profile $profile)
    {
        foreach ($this->data->profiles as $index => $profileInfo) {
            if ($profile->getName() == $profileInfo->name) {
                unset($this->data->profiles[$index]);
            }
        }

        $this->sortProfiles();

        return $this;
    }

    protected function normalise()
    {
        if (!isset($this->data->profiles)) {
            $this->data->profiles = array();
        }
    }

    protected function sortProfiles()
    {
        usort($this->data->profiles, array($this, 'cmpMasterManifestProfiles'));
    }

    protected function cmpMasterManifestProfiles($profile1, $profile2)
    {
        return strcmp($profile1->name, $profile2->name);
    }

    public function getSaltStateTopFileFullPath($env = 'base')
    {
        return implode(DIRECTORY_SEPARATOR, array(static::$saltstackBasePath, 'salt', $env, 'top.sls'));
    }

    public function getPillarDataTopFileFullPath($env = 'base')
    {
        return implode(DIRECTORY_SEPARATOR, array(static::$saltstackBasePath, 'pillar', $env, 'top.sls'));
    }

    /**
     * @return Node[]
     */
    public function getNodes()
    {
        $nodes = array();
        foreach ($this->data->nodes as $nodeInfo) {
            $profile = $this->getProfile($nodeInfo->profile);
            $nodeInfo->type = $profile->getType();

            $node = new Node();
            $node->populate($nodeInfo);
            $nodes[] = $node;
        }

        return $nodes;
    }

    /**
     * @param $id
     * @return Node
     */
    public function getNode($id)
    {
        foreach ($this->getNodes() as $node) {
            if ($node->getId() == $id) {
                return $node;
            }
        }

        return null;
    }

    public function nodeWithIdPresent($id)
    {
        foreach ($this->getNodes() as $node) {
            if ($node->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    public function addNode($id, $profileName, $profileVersion, $environment = 'base')
    {
        if ($this->nodeWithIdPresent($id)) {
            throw new \RuntimeException("Node '$id' already exists");
        }

        if (!$this->profileWithNamePresent($profileName)) {
            throw new \RuntimeException("Profile with name '$profileName' is not present");
        }

        $profile = $this->getProfile($profileName);
        if (!$profile->hasVersion($profileVersion)) {
            throw new \RuntimeException("Profile with name '$profileName' does not have version '$profileVersion' present");
        }

        $nodeMeta = new \stdClass();
        $nodeMeta->id = $id;
        $nodeMeta->profile = $profileName;
        $nodeMeta->version = $profileVersion;
        $nodeMeta->environment = $environment;
        $nodeMeta->type = $profile->getType();

        $node = new Node();
        $node->populate($nodeMeta);

        $saltTopFile = $this->getSaltTopFile();
        $saltTopFile->addOrUpdate($node);
        $saltTopFile->save();

        $pillarTopFile = $this->getPillarTopFile();
        $pillarTopFile->addOrUpdate($node);
        $pillarTopFile->save();

        $this->addNodeToManifest($node);

        return $node;
    }

    public function removeNode($id)
    {
        if (!$this->nodeWithIdPresent($id)) {
            throw new \RuntimeException("Node '$id' does not exist");
        }

        $node = $this->getNode($id);

        $saltTopFile = $this->getSaltTopFile();
        $saltTopFile->remove($node);
        $saltTopFile->save();

        $pillarTopFile = $this->getPillarTopFile();
        $pillarTopFile->remove($node);
        $pillarTopFile->save();

        $this->removeNodeFromManifest($node);

        return $node;
    }

    protected function addNodeToManifest(Node $node)
    {
        $metadata = $node->asMasterManifestMeta();

        $this->data->nodes[] = $metadata;
        $this->sortNodes();

        return $this;
    }

    protected function removeNodeFromManifest(Node $node)
    {
        foreach ($this->data->nodes as $index => $nodeInfo) {
            if ($node->getId() == $nodeInfo->id) {
                unset($this->data->nodes[$index]);
            }
        }

        $this->sortNodes();

        return $this;
    }

    protected function sortNodes()
    {
        usort($this->data->nodes, array($this, 'cmpMasterManifestNodes'));
    }

    protected function cmpMasterManifestNodes($node1, $node2)
    {
        return strcmp($node1->id, $node2->id);
    }

    protected function getSaltTopFile()
    {
        $saltTopFile = new SaltTopFile();
        $saltTopFile
            ->setFilesystem($this->getFilesystem())
            ->load($this->getSaltStateTopFileFullPath());

        return $saltTopFile;
    }

    protected function getPillarTopFile()
    {
        $pillarTopFile = new PillarTopFile();
        $pillarTopFile
            ->setFilesystem($this->getFilesystem())
            ->load($this->getPillarDataTopFileFullPath());

        return $pillarTopFile;
    }
}
