<?php

namespace App\Data\Traits;

use DateTime;


trait TimestampAbleTrait
{

    /**
     * @var DateTime
     */
    protected $createdAt;


    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }


    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

}
