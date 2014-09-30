<?php
use HireVoice\Neo4j\Extension;
use HireVoice\Neo4j\Extension\ArrayCollection;

class neo4jProxyApplication_Model_Entity_UserNode extends Application\Model\Entity\UserNode implements HireVoice\Neo4j\Proxy\Entity
{
    private $neo4j_hydrated = array();
    private $neo4j_meta;
    private $neo4j_node;
    private $neo4j_loadCallback;
    private $neo4j_relationships = false;
    private $neo4j_initialized = false;

    function getEntity()
    {
        $entity = new Application\Model\Entity\UserNode;

        foreach ($this->neo4j_meta->getProperties() as $prop) {
            $prop->setValue($entity, $prop->getValue($this));
        }

        $prop = $this->neo4j_meta->getPrimaryKey();
        $prop->setValue($entity, $prop->getValue($this));

        return $entity;
    }

    
    function getContacts()
    {
        self::__loadProperty('getContacts', 'contact');
        return parent::getContacts();
    }

    function addContacts($contacts)
    {
        self::__loadProperty('addContacts', 'contact');
        return parent::addContacts($contacts);
    }

    function setContacts(Doctrine\Common\Collections\ArrayCollection $contacts)
    {
        self::__loadProperty('setContacts', 'contact');
        return parent::setContacts($contacts);
    }


    function __addHydrated($name)
    {
        $this->neo4j_hydrated[] = $name;
    }

    function __setMeta($meta)
    {
        $this->neo4j_meta = $meta;
    }

    function __setNode($node)
    {
        $this->neo4j_node = $node;
    }

    function __getNode()
    {
        return $this->neo4j_node;
    }

    function __setLoadCallback(\Closure $loadCallback)
    {
        $this->neo4j_loadCallback = $loadCallback;
    }

    public function __load()
    {
        $this->neo4j_initialized = true;
    }

    public function __isInitialized()
    {
        return $this->neo4j_initialized;
    }

    private function __loadProperty($name, $propertyName)
    {
        if (in_array($propertyName, $this->neo4j_hydrated)) {
            return;
        }

        if (! $this->neo4j_meta) {
            throw new \HireVoice\Neo4j\Exception('Proxy not fully initialized. Relations are not available when loading the object from a session or other forms of serialization.');
        }

        $property = $this->neo4j_meta->findProperty($name);

        if (strpos($name, 'set') === 0) {
            $this->__addHydrated($propertyName);
            return;
        }

        if (false === $this->neo4j_relationships) {
            $command = new Extension\GetNodeRelationshipsLight($this->neo4j_node->getClient(), $this->neo4j_node);
            $this->neo4j_relationships = $command->execute();
        }

        $this->__addHydrated($propertyName);
        $collection = new ArrayCollection;
        foreach ($this->neo4j_relationships as $relation) {
            if ($relation['type'] == $propertyName) {
                // Read-only relations read the start node instead
                if ($property->isTraversed()) {
                    $nodeUrl = $relation['end'];
                    $root = $relation['start'];
                } else {
                     $nodeUrl = $relation['start'];
                     $root = $relation['end'];
                }

                if (basename($root) == $this->getId()) {
                    $node = $this->neo4j_node->getClient()->getNode(basename($nodeUrl));
                    $loader = $this->neo4j_loadCallback;
                    $collection->add($loader($node));
                }
            }
        }

        if ($property->isRelationList()) {
            $property->setValue($this, $collection);
        } else {
            if (count($collection)) {
                $property->setValue($this, $collection->first());
            }
        }
    }

    function __sleep()
    {
        return array (
  0 => 'name',
  1 => 'surname',
  2 => 'accountType',
  3 => 'numberOfVotes',
  4 => 'numberOfInVotes',
  5 => 'numberOfOutVotes',
  6 => 'numberOfSurveys',
  7 => 'numberOfContainers',
  8 => 'numberOfFriends',
  9 => 'mail',
  10 => 'uid',
  11 => 'circles',
  12 => 'id',
);
    }
}

