<?php

namespace RainmakerProfileManagerCliBundle\Entity;

/**
 * @package RainmakerProfileManagerCliBundle\Entity
 */
class ProfileMeta
{
    protected $name = '';
    protected $profileName = '';
    protected $type = '';
    protected $url = '';
    protected $branch = 'master';

    public function getName()
    {
        return $this->name;
    }

    public function getProfileName()
    {
        return $this->profileName;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function populate($metaData)
    {
        foreach (array('name', 'profileName', 'type', 'url', 'branch') as $attr) {
            $this->$attr = (!empty($metaData->$attr) ? $metaData->$attr : '');
        }
    }

}
