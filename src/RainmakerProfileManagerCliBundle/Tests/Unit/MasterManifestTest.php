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
use RainmakerProfileManagerCliBundle\Util\ProfileRootFsDownloader;
use RainmakerProfileManagerCliBundle\Tests\Unit\Mock\GitRepoMock;
use RainmakerProfileManagerCliBundle\Tests\Unit\Mock\ProfileRootFsDownloaderMock;

use Symfony\Component\Yaml\Yaml;

/**
 * Unit tests \RainmakerProfileManagerCliBundle\Entity\MasterManifest
 *
 * @package RainmakerProfileManagerCliBundle\Tests\Unit
 */
class MasterManifestTest extends AbstractUnitTest
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
     * Test installing a new branch profile in repository dev branch.
     */
    public function testInstallNewBranchInRepoDevBranchProfile()
    {
        $this->createMockMasterManifestInstallation();
        $this->installMockBranchProfile('dev');

        // Check the profile is installed on the filesystem correctly.
        $this->assertEquals('/srv/saltstack/profiles/branch/wackamole0/symfony2-dev', $this->profile->getFullPath());
        $this->assertTrue($this->filesystemMock->exists($this->profile->getFullPath() . '/.git'));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getFullPath() . '/manifest.json'));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getSaltStateTreeSymlinkFullPath()));
        $this->assertTrue($this->filesystemMock->exists($this->profile->getPillarDataTreeSymlinkFullPath()));

        // Check here that the profile has been added to the master manifest.
        $this->assertTrue($this->masterManifest->profileWithUrlPresent($this->profileUrl, 'dev'));

        // Reload manifest and test for profile to ensure profile changes were persisted correctly.
        $this->masterManifest = $this->loadMasterManifest();
        $this->assertTrue($this->masterManifest->profileWithUrlPresent($this->profileUrl, 'dev'));
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
     * Test listing nodes.
     */
    public function testListingNodes()
    {
        $this->createMockMasterManifestInstallation();
        $this->masterManifest->addNode('project1.localdev', 'rainmaker/default-project', '1.0');
        $this->masterManifest->addNode('project1-branch1.localdev', 'rainmaker/default-branch', '1.0');
        $this->masterManifest->addNode('project1-branch2.localdev', 'rainmaker/default-branch', '1.0');
        $this->masterManifest->addNode('project2.localdev', 'rainmaker/default-project', '1.0');
        $this->masterManifest->addNode('project2-branch1.localdev', 'rainmaker/default-branch', '1.0');
        $this->masterManifest->addNode('project2-branch2.localdev', 'rainmaker/default-branch', '1.0');

        $output = $this->masterManifest->listNodes();

        $this->assertEquals(
            file_get_contents($this->getPathToTestAcceptanceFilesDirectory() . DIRECTORY_SEPARATOR . 'configuredNodesList'),
            $output
        );
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
     * Test checking for installed profile.
     */
    public function testCheckingForInstalledProfile()
    {
        $this->createMockMasterManifestInstallation();
        $this->assertTrue($this->masterManifest->hasInstalledProfileProfile('rainmaker/default-branch'));
    }

    /**
     * Test checking for installed profile with specific profile version present.
     */
    public function testCheckingForInstalledProfileAtSpecificVersion()
    {
        $this->createMockMasterManifestInstallation();
        $this->assertTrue($this->masterManifest->hasInstalledProfileProfile('rainmaker/default-branch', '1.0'));
    }

    /**
     * Test checking for missing profile yields false.
     */
    public function testCheckingForMissingProfile()
    {
        $this->createMockMasterManifestInstallation();
        $this->assertFalse($this->masterManifest->hasInstalledProfileProfile('wackamole/drupal-classic'));
    }

    /**
     * Test checking for an installed profile with missing specific version yields false.
     */
    public function testCheckingForInstalledProfileWithMissingSpecificVersion()
    {
        $this->createMockMasterManifestInstallation();
        $this->assertFalse($this->masterManifest->hasInstalledProfileProfile('rainmaker/default-branch', '2.0'));
    }

    /**
     * Test retriving a sorted list of profile versions and test getting the most recent version available of a profile.
     */
    public function testRetrievingMostRecentProfileVersion()
    {
        $this->createMockMasterManifestInstallation();
        $profile = $this->masterManifest->getProfile('rainmaker/drupal-classic');
        $this->assertEquals(
            array('1.0', '0.5.1', '0.5'),
            $profile->getAvailableProfileVersions()
        );
        $this->assertEquals('1.0', $this->masterManifest->getLatestProfileVersionForProfileWithName('rainmaker/drupal-classic'));
    }

    /**
     * Test downloading rootfs of profile version.
     */
    public function testDownloadingRootfsOfProfileVersion()
    {
        $this->createMockMasterManifestInstallation();

        $this->assertFalse($this->masterManifest->hasCachedProfileRootFs('rainmaker/default-branch', '1.0'));
        $this->masterManifest->downloadProfileRootFs('rainmaker/default-branch', '1.0');
        $this->assertTrue($this->masterManifest->hasCachedProfileRootFs('rainmaker/default-branch', '1.0'));

        $this->assertEquals(
            '/var/cache/lxc/rainmaker/branch/rainmaker/default-branch/1/1.0.tgz',
            $this->masterManifest->getLxcCacheProfileRootFsFullPath('rainmaker/default-branch', '1.0')
        );
    }

    /**
     * Test retrieving list of mounts and exports for profile.
     */
    public function testRetrievalOfProfileMountsAndExports() {
        $this->createMockMasterManifestInstallation();

        $json = '{"mounts":[{"source":"\/var\/cache\/lxc\/rainmaker","target":"{{container_rootfs}}\/var\/cache\/lxc\/rainmaker","group":"bind"},{"source":"\/srv\/saltstack","target":"{{container_rootfs}}\/srv\/saltstack","group":"bind"}],"exports":[]}';
        $this->assertEquals($json, $this->masterManifest->getProfileVersionMountsAndExports('rainmaker/default-project', '1.0'));

        $json = '{"mounts":[{"source":"\/srv\/saltstack","target":"{{container_rootfs}}\/srv\/saltstack","group":"bind"},{"source":"{{container_rootfs}}\/rootfs\/var\/www\/html","target":"\/export\/rainmaker\/{{container_name}}","group":"nfs"}],"exports":[{"source":"\/export\/rainmaker\/{{container_name}}","group":""}]}';
        $this->assertEquals($json, $this->masterManifest->getProfileVersionMountsAndExports('rainmaker/drupal-classic', '1.0'));

    }

    /**
     * Test retrieving list of post-provisioning scripts to be run in JSON form.
     */
    //public function testRetrievalOfPostProvisioningScripts()
    //{
    //    throw new \RuntimeException('test body required implementation');
    //}


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

        MasterManifest::$profileRootFsDownloaderClass = 'RainmakerProfileManagerCliBundle\Tests\Unit\Mock\ProfileRootFsDownloaderMock';
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
            'downloadBaseUrl' => 'http://image.rainmaker-dev.org',
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
            'downloadBaseUrl' => 'http://image.rainmaker-dev.org',
            'profiles' => array(
                array(
                    'version' => '1.0',
                    'mounts' => array(
                        array(
                            'source' => '/var/cache/lxc/rainmaker',
                            'target' => '{{container_rootfs}}/var/cache/lxc/rainmaker',
                            'group' => 'bind'
                        ),
                        array(
                            'source' => '/srv/saltstack',
                            'target' => '{{container_rootfs}}/srv/saltstack',
                            'group' => 'bind'
                        )
                    ),
                    'exports' => array()
                )
            )
        ));
        GitRepoMock::$profilesRepo['https://github.com/wackamole0/rainmaker-default-project-profile.git'] = json_encode($profileManifest, JSON_PRETTY_PRINT);

        $profileManifest = $this->generateMockProfileManifest(array(
            'type' => 'branch',
            'name' => 'rainmaker/default-branch',
            'downloadBaseUrl' => 'http://image.rainmaker-dev.org',
            'profiles' => array(
                array(
                    'version' => '1.0',
                    'mounts' => array(
                        array(
                            'source' => '/srv/saltstack',
                            'target' => '{{container_rootfs}}/srv/saltstack',
                            'group' => 'bind'
                        ),
                        array(
                            'source' => '{{container_rootfs}}/rootfs/var/www/html',
                            'target' => '/export/rainmaker/{{container_name}}',
                            'group' => 'nfs'
                        )
                    ),
                    'exports' => array()
                )
            )
        ));
        GitRepoMock::$profilesRepo['https://github.com/wackamole0/rainmaker-default-branch-profile.git'] = json_encode($profileManifest, JSON_PRETTY_PRINT);

        $profileManifest = $this->generateMockProfileManifest(array(
            'type' => 'branch',
            'name' => 'rainmaker/drupal-classic',
            'downloadBaseUrl' => 'http://image.rainmaker-dev.org',
            'profiles' => array(
                array(
                    'version' => '1.0',
                    'mounts' => array(
                        array(
                            'source' => '/srv/saltstack',
                            'target' => '{{container_rootfs}}/srv/saltstack',
                            'group' => 'bind'
                        ),
                        array(
                            'source' => '{{container_rootfs}}/rootfs/var/www/html',
                            'target' => '/export/rainmaker/{{container_name}}',
                            'group' => 'nfs'
                        )
                    ),
                    'exports' => array(
                        array(
                            'source' => '/export/rainmaker/{{container_name}}'
                        )
                    )
                ),
                array(
                    'version' => '0.5',
                    'mounts' => array(
                        array(
                            'source' => '/srv/saltstack',
                            'target' => '{{container_rootfs}}/srv/saltstack',
                            'group' => 'bind'
                        ),
                        array(
                            'source' => '{{container_rootfs}}/rootfs/var/www/html',
                            'target' => '/export/rainmaker/{{container_name}}',
                            'group' => 'nfs'
                        )
                    ),
                    'exports' => array(
                        array(
                            'source' => '/export/rainmaker/{{container_name}}'
                        )
                    )
                ),
                array(
                    'version' => '0.5.1',
                    'mounts' => array(
                        array(
                            'source' => '/srv/saltstack',
                            'target' => '{{container_rootfs}}/srv/saltstack',
                            'group' => 'bind'
                        ),
                        array(
                            'source' => '{{container_rootfs}}/rootfs/var/www/html',
                            'target' => '/export/rainmaker/{{container_name}}',
                            'group' => 'nfs'
                        )
                    ),
                    'exports' => array(
                        array(
                            'source' => '/export/rainmaker/{{container_name}}'
                        )
                    )
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
            'downloadBaseUrl' => 'http://image.rainmaker-dev.org',
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

    protected function installMockBranchProfile($branch = 'master')
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
            'downloadBaseUrl' => 'http://image.rainmaker-dev.org',
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
            ->installProfileFromUrl($this->profileUrl, true, $branch);
    }

    protected function generateMockProfileManifest($manifestData)
    {
        $profileManifest = new \stdClass();
        $profileManifest->type = isset($manifestData['type']) ? $manifestData['type'] : '';
        $profileManifest->name = isset($manifestData['name']) ? $manifestData['name'] : '';
        $profileManifest->downloadBaseUrl = isset($manifestData['downloadBaseUrl']) ? $manifestData['downloadBaseUrl'] : '';
        $profileManifest->profiles = array();
        if (!empty($manifestData['profiles'])) {
            foreach ($manifestData['profiles'] as $profileVersionData) {
                $profileVersion = new \stdClass();
                $profileVersion->version = isset($profileVersionData['version']) ? $profileVersionData['version'] : '';
                $profileVersion->mounts = array();
                $profileVersion->exports = array();

                if (isset($profileVersionData['mounts']) && is_array($profileVersionData['mounts'])) {
                    foreach ($profileVersionData['mounts'] as $mountData) {
                        $mount = new \stdClass();
                        $mount->source = $mountData['source'];
                        $mount->target = $mountData['target'];
                        $mount->group = isset($mountData['group']) ? $mountData['group'] : '';
                        $profileVersion->mounts[] = $mount;
                    }
                }

                if (isset($profileVersionData['exports']) && is_array($profileVersionData['exports'])) {
                    foreach ($profileVersionData['exports'] as $exportData) {
                        $export = new \stdClass();
                        $export->source = $exportData['source'];
                        $export->group = isset($exportData['group']) ? $exportData['group'] : '';
                        $profileVersion->exports[] = $export;
                    }
                }

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
