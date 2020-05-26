<?php

namespace App\Bird;

use App\Data\BgpCommunityDataInterface;
use App\Utils\CommonFilters;


class CommandParameters
{

    const TABLE_ALL     = 'all';
    const CONDITION_AND = '&&';
    const CONDITION_OR  = '||';

    /**
     * @var string
     */
    private $table = self::TABLE_ALL;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $export;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var BgpCommunityDataInterface[]|array
     */
    private $bgpCommunities = [];

    /**
     * @var string
     */
    private $bgpCommunitiesCondition = self::CONDITION_OR;

    /**
     * @var bool
     */
    private $count = false;


    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }


    /**
     * @param string $table
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }


    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }


    /**
     * @param string $protocol
     */
    public function setProtocol(string $protocol): void
    {
        $this->protocol = $protocol;
    }


    /**
     * @return string
     */
    public function getExport(): string
    {
        return $this->export;
    }


    /**
     * @param string $export
     */
    public function setExport(string $export): void
    {
        $this->export = $export;
    }


    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }


    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }


    /**
     * @return string
     */
    public function getPrefixKey()
    {
        return CommonFilters::sanitizeCacheKey($this->prefix);
    }


    /**
     * @return BgpCommunityDataInterface[]|array
     */
    public function getBgpCommunities()
    {
        return $this->bgpCommunities;
    }


    /**
     * @param BgpCommunityDataInterface[]|array $bgpCommunities
     */
    public function setBgpCommunities($bgpCommunities): void
    {
        $this->bgpCommunities = $bgpCommunities;
    }


    /**
     * @param BgpCommunityDataInterface $communityData
     */
    public function addBgpCommunity(BgpCommunityDataInterface $communityData)
    {
        $this->bgpCommunities[] = $communityData;
    }


    /**
     * @return string
     */
    public function getBgpCommunitiesKey()
    {
        $values = [];

        foreach ($this->bgpCommunities as $community) {
            $values[] = $community->getRawValue();
        }

        return md5(implode('-', $values));
    }


    /**
     * @return string
     */
    public function getBgpCommunitiesCondition(): string
    {
        return $this->bgpCommunitiesCondition;
    }


    /**
     * @param string $bgpCommunitiesCondition
     */
    public function setBgpCommunitiesCondition(string $bgpCommunitiesCondition): void
    {
        $this->bgpCommunitiesCondition = $bgpCommunitiesCondition;
    }


    /**
     * @return bool
     */
    public function isCount(): bool
    {
        return $this->count;
    }


    /**
     * @param bool $count
     */
    public function setCount(bool $count): void
    {
        $this->count = $count;
    }


    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->count ? 'count' : 'all';
    }


    /**
     * @return bool
     */
    public function isForAllTables()
    {
        return $this->getTable() === self::TABLE_ALL;
    }

}
