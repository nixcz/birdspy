<?php

namespace App\Data\Traits;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;


trait UniversallyUniqueIdentifierTrait
{

    /**
     * @var UuidInterface
     */
    protected $id;


    /**
     * @return UuidInterface
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->id->toString();
    }


    /**
     * @param string $uuid
     */
    public function setUuid(string $uuid)
    {
        $factory = Uuid::getFactory();

        $this->id = $factory->fromString($uuid);
    }


    public static function createUuid()
    {
        // TODO Exception

        return Uuid::uuid4();
    }

}
