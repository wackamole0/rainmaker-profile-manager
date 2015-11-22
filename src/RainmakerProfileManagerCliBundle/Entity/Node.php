<?php

namespace RainmakerProfileManagerCliBundle\Entity;

/**
 * @package RainmakerProfileManagerCliBundle\Entity
 */
class Node
{
    protected $id = '';
    protected $profile = '';
    protected $version = '';
    protected $environment = '';
    protected $type = '';

    public function getId()
    {
        return $this->id;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getType()
    {
        return $this->type;
    }

    public function populate($nodeData)
    {
        foreach (array('id', 'profile', 'version', 'environment', 'type') as $attr) {
            $this->$attr = (!empty($nodeData->$attr) ? $nodeData->$attr : '');
        }
    }

    public function asMasterManifestMeta()
    {
        $meta = new \stdClass();
        foreach (array('id', 'profile', 'version', 'environment', 'type') as $attr) {
            $meta->$attr = $this->$attr;
        }

        return $meta;
    }

    public function getProfiles()
    {
        $profilePath = array('rainmaker', $this->getType());
        if ('core' != $this->getType()) {
            $profilePath[] = $this->getProfile();
        }
        $profilePath[] = 'v' . str_replace('.', '_', $this->getVersion());

        return array(implode(DIRECTORY_SEPARATOR, $profilePath));
    }

}
