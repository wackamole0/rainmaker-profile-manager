<?php

namespace RainmakerProfileManagerCliBundle\Entity;

use RainmakerProfileManagerCliBundle\Util\Filesystem;

use Symfony\Component\Yaml\Yaml;

/**
 * @package RainmakerProfileManagerCliBundle\Entity
 */
abstract class SaltstackTopFile
{

    /**
     * @var \RainmakerProfileManagerCliBundle\Util\Filesystem
     */
    protected $filesystem = null;

    protected $topfileFullPath = '';

    protected $data = array();

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

    public function load($topfileFullPath = null)
    {
        if (!empty($topfileFullPath)) {
            $this->topfileFullPath = $topfileFullPath;
        }

        $content = $this->getFilesystem()->getFileContents($this->topfileFullPath);
        $this->data = Yaml::parse($content);
        $this->normalise();

        return $this;
    }

    public function save()
    {
        if (empty($this->topfileFullPath)) {
            throw new \RuntimeException('Top file full path is not set');
        }

        $content = Yaml::dump($this->data);
        $this->getFilesystem()->putFileContents($this->topfileFullPath, $content);

        return $this;
    }

    protected function normalise()
    {
        if (empty($this->data)) {
            $this->data['base'] = array();
        }
    }

    public function addOrUpdate(Node $node)
    {
        if (!isset($this->data[$node->getEnvironment()])) {
            $this->data[$node->getEnvironment()] = array();
        }

        $this->data[$node->getEnvironment()][$node->getId()] = $node->getProfiles();
    }

    public function remove(Node $node)
    {
        unset($this->data[$node->getEnvironment()][$node->getId()]);
    }

}
