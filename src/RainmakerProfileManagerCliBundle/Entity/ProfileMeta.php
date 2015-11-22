<?php

namespace RainmakerProfileManagerCliBundle\Entity;

/**
 * @package RainmakerProfileManagerCliBundle\Entity
 */
class ProfileMeta
{
    protected $name = '';
    protected $type = '';
    protected $url = '';
    protected $branch = '';

    public function getName()
    {
        return $this->name;
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
        foreach (array('name', 'type', 'url', 'branch') as $attr) {
            $this->$attr = (!empty($metaData->$attr) ? $metaData->$attr : '');
        }
    }

}
