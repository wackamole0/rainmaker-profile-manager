<?php

namespace RainmakerProfileManagerCliBundle\Tests\Unit;

use RainmakerProfileManagerCliBundle\Tests\AbstractUnitTest;
use RainmakerProfileManagerCliBundle\Tests\Unit\Mock\FilesystemMock;
use RainmakerProfileManagerCliBundle\Tests\Unit\Mock\ProcessRunnerMock;

use RainmakerProfileManagerCliBundle\Entity\MasterManifest;
use RainmakerProfileManagerCliBundle\Entity\Profile;
use RainmakerProfileManagerCliBundle\Util\Filesystem;
use RainmakerProfileManagerCliBundle\Util\GitRepo;
use RainmakerProfileManagerCliBundle\Util\ProfileInstaller;
use RainmakerProfileManagerCliBundle\Tests\Unit\Mock\GitRepoMock;

use Symfony\Component\Yaml\Yaml;

/**
 * Unit tests \Rainmaker\Task\Project\Create
 *
 * @package RainmakerProfileManagerCliBundle\Tests\Unit
 */
class CreateTest extends AbstractUnitTest
{

    /**
     * @var FilesystemMock
     */
    protected $filesystemMock;

    /**
     * @var MasterManifest
     */
    protected $masterManifest;

    protected $profileUrl;

    protected $profileManifest;

    /**
     * @var Profile
     */
    protected $profile;

    /**
     * Test building the Rainmaker profiles, Salt top files and Pillar top files from the "master manifest".
     */
    public function testBuildFromManifest()
    {
        $this->createMockMasterManifestInstallation();

        //$this->filesystemMock->dumpFilesystemStructure();

        foreach ($this->masterManifest->getProfiles() as $profile) {
            $this->assertTrue($this->filesystemMock->exists($profile->getFullPath() . '/.git'));
            $this->assertTrue($this->filesystemMock->exists($profile->getFullPath() . '/manifest.json'));
            $this->assertTrue($this->filesystemMock->exists($profile->getSaltStateTreeSymlinkFullPath()));
            $this->assertTrue($this->filesystemMock->exists($profile->getPillarDataTreeSymlinkFullPath()));
        }

        $saltYaml = Yaml::parse($this->filesystemMock->getFileContents($this->masterManifest->getSaltStateTopFileFullPath()));
        $pillarYaml = Yaml::parse($this->filesystemMock->getFileContents($this->masterManifest->getPillarDataTopFileFullPath()));

        foreach ($this->masterManifest->getNodes() as $node) {
            $this->assertEquals($node->getProfiles(), $saltYaml['base'][$node->getId()]);
            $this->assertEquals($node->getProfiles(), $pillarYaml['base'][$node->getId()]);
        }
    }

    /**
     * Test installing a new branch profile.
     */
    public function testInstallNewBranchProfile()
    {
        $this->createMockMasterManifestInstallation();
        $this->installMockBranchProfile();

        // Check the profile is installed on the filesystem correctly.
        $this->assertTrue($this->filesystemMock->exists($this->profile->getFullPath() . '/.git'));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getFullPath() . '/manifest.json'));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getSaltStateTreeSymlinkFullPath()));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getPillarDataTreeSymlinkFullPath()));

        // Check here that the profile has been added to the master manifest.
        $this->assertTrue($this->masterManifest->profileWithUrlPresent($this->profileUrl));

        // Reload manifest and test for profile to ensure profile changes were persisted correctly.
        $this->masterManifest = $this->loadMasterManifest();
        $this->assertTrue($this->masterManifest->profileWithUrlPresent($this->profileUrl));
    }

    /**
     * Test installing a new core profile to empty manifest.
     */
    public function testInstallNewCoreProfileToEmptyManifest()
    {
        //$this->createMockMasterManifestInstallation();
        $this->installMockCoreProfile();

        // Check the profile is installed on the filesystem correctly.
        $this->assertTrue($this->filesystemMock->exists($this->profile->getFullPath() . '/.git'));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getFullPath() . '/manifest.json'));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getSaltStateTreeSymlinkFullPath()));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getPillarDataTreeSymlinkFullPath()));

        // Check here that the profile has been added to the master manifest.
        $this->assertTrue($this->masterManifest->profileWithUrlPresent($this->profileUrl));

        // Reload manifest and test for profile to ensure profile changes were persisted correctly.
        $this->masterManifest = $this->loadMasterManifest();
        $this->assertTrue($this->masterManifest->profileWithUrlPresent($this->profileUrl));
    }

    /**
     * Test installing a new branch profile to empty manifest.
     */
    public function testInstallNewBranchProfileToEmptyManifest()
    {
        //$this->createMockMasterManifestInstallation();
        $this->installMockBranchProfile();

        // Check the profile is installed on the filesystem correctly.
        $this->assertTrue($this->filesystemMock->exists($this->profile->getFullPath() . '/.git'));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getFullPath() . '/manifest.json'));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getSaltStateTreeSymlinkFullPath()));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getPillarDataTreeSymlinkFullPath()));

        // Check here that the profile has been added to the master manifest.
        $this->assertTrue($this->masterManifest->profileWithUrlPresent($this->profileUrl));

        // Reload manifest and test for profile to ensure profile changes were persisted correctly.
        $this->masterManifest = $this->loadMasterManifest();
        $this->assertTrue($this->masterManifest->profileWithUrlPresent($this->profileUrl));
    }

    /**
     * Test uninstalling an existing branch profile.
     */
    public function testUninstallExistingProfile()
    {
        $this->createMockMasterManifestInstallation();
        $this->installMockBranchProfile();

        $this->masterManifest->removeProfileByName($this->profile->getName());

        // Check the profile has been uninstalled from the filesystem correctly.
        $this->assertFalse($this->filesystemMock->exists($this->profile->getSaltStateTreeSymlinkFullPath()));
        $this->assertFalse($this->filesystemMock->exists($this->profile->getPillarDataTreeSymlinkFullPath()));
        $this->assertFalse($this->filesystemMock->exists($this->profile->getFullPath()));

        // Check here that the profile has been removed from the master manifest.
        $this->assertFalse($this->masterManifest->profileWithUrlPresent($this->profileUrl));
        $this->assertFalse($this->masterManifest->profileWithUrlPresent($this->profile->getName()));

        // Reload manifest and test for profile to ensure profile changes were persisted correctly.
        $this->masterManifest = $this->loadMasterManifest();
        $this->assertFalse($this->masterManifest->profileWithUrlPresent($this->profileUrl));
        $this->assertFalse($this->masterManifest->profileWithUrlPresent($this->profile->getName()));
    }

    /**
     * Test add a new node.
     */
    public function testAddNewNode()
    {
        $this->createMockMasterManifestInstallation();

        $profile = $this->masterManifest->getProfile('rainmaker/default-project');
        $node = $this->masterManifest->addNode('testnode', $profile->getName(), '1.0');

        $this->assertTrue($this->masterManifest->nodeWithIdPresent('testnode'));

        $saltYaml = Yaml::parse($this->filesystemMock->getFileContents($this->masterManifest->getSaltStateTopFileFullPath()));
        $pillarYaml = Yaml::parse($this->filesystemMock->getFileContents($this->masterManifest->getPillarDataTopFileFullPath()));

        $this->assertEquals(array($profile->getSaltTopFileRelativePath('1.0')), $saltYaml['base'][$node->getId()]);
        $this->assertEquals(array($profile->getPillarTopFileRelativePath('1.0')), $pillarYaml['base'][$node->getId()]);

        // Reload manifest and test for profile to ensure profile changes were persisted correctly.
        $this->masterManifest = $this->loadMasterManifest();
        $this->assertTrue($this->masterManifest->nodeWithIdPresent('testnode'));
    }

    /**
     * Test remove an existing node.
     */
    public function testRemoveExistingNode()
    {
        $this->createMockMasterManifestInstallation();

        $profile = $this->masterManifest->getProfile('rainmaker/default-project');
        $node = $this->masterManifest->addNode('testnode', $profile->getName(), '1.0');

        $saltYaml = Yaml::parse($this->filesystemMock->getFileContents($this->masterManifest->getSaltStateTopFileFullPath()));
        $pillarYaml = Yaml::parse($this->filesystemMock->getFileContents($this->masterManifest->getPillarDataTopFileFullPath()));

        $this->assertEquals(array($profile->getSaltTopFileRelativePath('1.0')), $saltYaml['base'][$node->getId()]);
        $this->assertEquals(array($profile->getPillarTopFileRelativePath('1.0')), $pillarYaml['base'][$node->getId()]);

        $this->masterManifest->removeNode('testnode');

        $this->assertFalse($this->masterManifest->nodeWithIdPresent('testnode'));

        $saltYaml = Yaml::parse($this->filesystemMock->getFileContents($this->masterManifest->getSaltStateTopFileFullPath()));
        $pillarYaml = Yaml::parse($this->filesystemMock->getFileContents($this->masterManifest->getPillarDataTopFileFullPath()));

        $this->assertArrayNotHasKey($node->getId(), $saltYaml['base']);
        $this->assertArrayNotHasKey($node->getId(), $pillarYaml['base']);

        // Reload manifest and test for profile to ensure profile changes were persisted correctly.
        $this->masterManifest = $this->loadMasterManifest();
        $this->assertFalse($this->masterManifest->nodeWithIdPresent('testnode'));
    }

    /**
     * Test listing available profile updates.
     */
    public function testListingAvailableProfileUpdates()
    {
        $this->createMockMasterManifestInstallation();
        GitRepoMock::$profilesWithUpdates['https://github.com/wackamole0/rainmaker-default-project-profile.git'] = 1;
        GitRepoMock::$profilesWithUpdates['https://github.com/wackamole0/rainmaker-default-branch-profile.git'] = 2;

        foreach ($this->masterManifest->getProfiles() as $profile) {
            $profile->fetchUpdates();
        }

        $output = $this->masterManifest->showAvailableUpdates();

        $this->assertEquals(
            file_get_contents($this->getPathToTestAcceptanceFilesDirectory() . DIRECTORY_SEPARATOR . 'availableProfileUpdatesListing'),
            $output
        );
    }

    /**
     * Test update profiles.
     */
    public function testUpdatingProfiles()
    {
        $this->createMockMasterManifestInstallation();
        GitRepoMock::$profilesWithUpdates['https://github.com/wackamole0/rainmaker-default-project-profile.git'] = 1;
        GitRepoMock::$profilesWithUpdates['https://github.com/wackamole0/rainmaker-default-branch-profile.git'] = 2;

        foreach ($this->masterManifest->getProfiles() as $profile) {
            $profile->fetchUpdates();
        }

        $profileCore = $this->masterManifest->getProfile('core');
        $profileDefaultProject = $this->masterManifest->getProfile('rainmaker/default-project');
        $profileDefaultBranch = $this->masterManifest->getProfile('rainmaker/default-branch');

        $this->assertFalse($profileCore->hasAvailableUpdates());
        $this->assertTrue($profileDefaultProject->hasAvailableUpdates());
        $this->assertTrue($profileDefaultBranch->hasAvailableUpdates());

        $this->masterManifest->updateAllProfiles();

        GitRepoMock::$profilesWithUpdates = array();

        $profileCore = $this->masterManifest->getProfile('core');
        $profileDefaultProject = $this->masterManifest->getProfile('rainmaker/default-project');
        $profileDefaultBranch = $this->masterManifest->getProfile('rainmaker/default-branch');

        $this->assertFalse($profileCore->hasAvailableUpdates());
        $this->assertFalse($profileDefaultProject->hasAvailableUpdates());
        $this->assertFalse($profileDefaultBranch->hasAvailableUpdates());
    }

    /**
     * Test purging rootfs caches.
     */
    public function testPurgingCaches()
    {
        $this->filesystemMock = $this->createFilesystemMock();

        $this->masterManifest = new MasterManifest();
        $this->masterManifest
            ->setFilesystem($this->filesystemMock)
            ->load();

        $this->fillCachesWithMockData();

        $projectProfileCachePath = MasterManifest::$lxcRootfsCacheFullPath . DIRECTORY_SEPARATOR . 'project';
        $branchProfileCachePath = MasterManifest::$lxcRootfsCacheFullPath . DIRECTORY_SEPARATOR . 'branch';

        $this->assertTrue($this->filesystemMock->exists($projectProfileCachePath . DIRECTORY_SEPARATOR . 'rainmaker/default-project'));
        $this->assertTrue($this->filesystemMock->exists($branchProfileCachePath . DIRECTORY_SEPARATOR . 'rainmaker/default-branch'));
        $this->masterManifest->purgeCaches();
        $this->assertTrue($this->filesystemMock->exists($projectProfileCachePath));
        $this->assertTrue($this->filesystemMock->exists($branchProfileCachePath));
        $this->assertFalse($this->filesystemMock->exists($projectProfileCachePath . DIRECTORY_SEPARATOR . 'rainmaker/default-project'));
        $this->assertFalse($this->filesystemMock->exists($branchProfileCachePath . DIRECTORY_SEPARATOR . 'rainmaker/default-branch'));
    }

    /**
     * Test downloading rootfs of profile version.
     */
    public function testDownloadingRootfsOfProfileVersion()
    {
        throw new \RuntimeException('test body required implementation');
    }

    /**
     * Test retrieving list of post-provisioning scripts to be run in JSON form.
     */
    public function testRetrievalOfPostProvisioningScripts()
    {
        throw new \RuntimeException('test body required implementation');
    }


    //- Utility methods


    protected function getPathToTestAcceptanceFilesDirectory()
    {
        return $this->getPathToTestAcceptanceFilesBaseDirectory() . '/masterManifest';
    }

    protected function createFilesystemMock()
    {
        $fs = new FilesystemMock();
        $fs->copyFromFileSystem(__DIR__ . '/../fsMocks');

        return $fs;
    }

    protected function loadMasterManifest()
    {
        if (empty($this->filesystemMock)) {
            $this->filesystemMock = $this->createFilesystemMock();
        }

        $this->masterManifest = new MasterManifest();
        $this->masterManifest
            ->setFilesystem($this->filesystemMock)
            ->load();

        return $this->masterManifest;
    }

    protected function createMockMasterManifestInstallation()
    {
        if (empty($this->filesystemMock)) {
            $this->filesystemMock = $this->createFilesystemMock();
        }

        $this->filesystemMock->putFileContents(
            '/srv/saltstack/profiles/manifest.json',
            file_get_contents(implode(DIRECTORY_SEPARATOR,
                array(
                    $this->getPathToTestAcceptanceFilesDirectory(),
                    'buildFromManifest',
                    'manifest.json'
                )
            ))
        );

        GitRepo::$processRunnerClass = '\RainmakerProfileManagerCliBundle\Tests\Unit\Mock\ProcessRunnerMock';
        ProfileInstaller::$gitRepoClass = '\RainmakerProfileManagerCliBundle\Tests\Unit\Mock\GitRepoMock';
        Profile::$gitRepoClass = '\RainmakerProfileManagerCliBundle\Tests\Unit\Mock\GitRepoMock';

        GitRepoMock::$profilesRepo = array();

        $profileManifest = $this->generateMockProfileManifest(array(
            'type' => 'core',
            'name' => 'core',
            'profiles' => array(
                array(
                    'version' => '1.0'
                )
            )
        ));
        GitRepoMock::$profilesRepo['https://github.com/wackamole0/rainmaker-salt-core.git'] = json_encode($profileManifest, JSON_PRETTY_PRINT);

        $profileManifest = $this->generateMockProfileManifest(array(
            'type' => 'project',
            'name' => 'rainmaker/default-project',
            'profiles' => array(
                array(
                    'version' => '1.0'
                )
            )
        ));
        GitRepoMock::$profilesRepo['https://github.com/wackamole0/rainmaker-default-project-profile.git'] = json_encode($profileManifest, JSON_PRETTY_PRINT);

        $profileManifest = $this->generateMockProfileManifest(array(
            'type' => 'branch',
            'name' => 'rainmaker/default-branch',
            'profiles' => array(
                array(
                    'version' => '1.0'
                )
            )
        ));
        GitRepoMock::$profilesRepo['https://github.com/wackamole0/rainmaker-default-branch-profile.git'] = json_encode($profileManifest, JSON_PRETTY_PRINT);

        $profileManifest = $this->generateMockProfileManifest(array(
            'type' => 'branch',
            'name' => 'rainmaker/drupal-classic',
            'profiles' => array(
                array(
                    'version' => '1.0'
                )
            )
        ));
        GitRepoMock::$profilesRepo['https://github.com/wackamole0/rainmaker-drupal-classic-profile.git'] = json_encode($profileManifest, JSON_PRETTY_PRINT);

        if (empty($this->masterManifest)) {
            $this->masterManifest = $this->loadMasterManifest();
        }
        $this->masterManifest->install();
    }

    protected function installMockCoreProfile()
    {
        if (empty($this->filesystemMock)) {
            $this->filesystemMock = $this->createFilesystemMock();
        }

        GitRepo::$processRunnerClass = '\RainmakerProfileManagerCliBundle\Tests\Unit\Mock\ProcessRunnerMock';
        ProfileInstaller::$gitRepoClass = '\RainmakerProfileManagerCliBundle\Tests\Unit\Mock\GitRepoMock';

        GitRepoMock::$profilesRepo = array();

        $this->profileUrl = 'https://github.com/wackamole0/rainmaker-symfony2.git';

        $this->profileManifest = $this->generateMockProfileManifest(array(
            'type' => 'core',
            'name' => 'core',
            'profiles' => array(
                array(
                    'version' => '1.0'
                )
            )
        ));
        GitRepoMock::$profilesRepo[$this->profileUrl] = json_encode($this->profileManifest, JSON_PRETTY_PRINT);

        if (empty($this->masterManifest)) {
            $this->masterManifest = new MasterManifest();
            $this->masterManifest
                ->setFilesystem($this->filesystemMock)
                ->load();
        }

        $this->profile = $this->masterManifest
            ->setFilesystem($this->filesystemMock)
            ->installProfileFromUrl($this->profileUrl);
    }

    protected function installMockBranchProfile()
    {
        if (empty($this->filesystemMock)) {
            $this->filesystemMock = $this->createFilesystemMock();
        }

        GitRepo::$processRunnerClass = '\RainmakerProfileManagerCliBundle\Tests\Unit\Mock\ProcessRunnerMock';
        ProfileInstaller::$gitRepoClass = '\RainmakerProfileManagerCliBundle\Tests\Unit\Mock\GitRepoMock';

        GitRepoMock::$profilesRepo = array();

        $this->profileUrl = 'https://github.com/wackamole0/rainmaker-symfony2.git';

        $this->profileManifest = $this->generateMockProfileManifest(array(
            'type' => 'branch',
            'name' => 'wackamole0/symfony2',
            'profiles' => array(
                array(
                    'version' => '1.0'
                )
            )
        ));
        GitRepoMock::$profilesRepo[$this->profileUrl] = json_encode($this->profileManifest, JSON_PRETTY_PRINT);

        if (empty($this->masterManifest)) {
            $this->masterManifest = $this->loadMasterManifest();
        }

        $this->profile = $this->masterManifest
            ->setFilesystem($this->filesystemMock)
            ->installProfileFromUrl($this->profileUrl);
    }

    protected function generateMockProfileManifest($manifestData)
    {
        $profileManifest = new \stdClass();
        $profileManifest->type = isset($manifestData['type']) ? $manifestData['type'] : '';
        $profileManifest->name = isset($manifestData['name']) ? $manifestData['name'] : '';
        $profileManifest->profiles = array();
        if (!empty($manifestData['profiles'])) {
            foreach ($manifestData['profiles'] as $profileVersionData) {
                $profileVersion = new \stdClass();
                $profileVersion->version = isset($profileVersionData['version']) ? isset($profileVersionData['version']) : '';
                $profileManifest->profiles[] = $profileVersion;
            }
        }

        return $profileManifest;
    }

    protected function fillCachesWithMockData()
    {
        if (empty($this->filesystemMock)) {
            $this->filesystemMock = $this->createFilesystemMock();
        }

        if (empty($this->masterManifest)) {
            $this->masterManifest = $this->loadMasterManifest();
        }

        $projectProfileCachePath = implode(DIRECTORY_SEPARATOR, array(
            MasterManifest::$lxcRootfsCacheFullPath,
            'project',
            'rainmaker/default-project',
            '1'
        ));
        $this->filesystemMock->mkdir($projectProfileCachePath);
        for ($i = 0; $i <= 10; $i++) {
            $this->filesystemMock->touch($projectProfileCachePath . DIRECTORY_SEPARATOR . $i . '.tgz');
        }

        $branchProfileCachePath = implode(DIRECTORY_SEPARATOR, array(
            MasterManifest::$lxcRootfsCacheFullPath,
            'branch',
            'rainmaker/default-branch',
            '1'
        ));
        $this->filesystemMock->mkdir($branchProfileCachePath);
        for ($i = 0; $i <= 10; $i++) {
            $this->filesystemMock->touch($branchProfileCachePath . DIRECTORY_SEPARATOR . $i . '.tgz');
        }
    }

}
