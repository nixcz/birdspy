<?php

namespace App\Data;

use App\Data\Traits\TimestampAbleTrait;
use App\Data\Traits\UniversallyUniqueIdentifierTrait;


class RouteData
{

    const KEY_PRIMARY   = 'primary';
    const KEY_SECONDARY = 'secondary';

    const KEY_VALID   = 'valid';
    const KEY_INVALID = 'invalid';

    const KEY_RPKI_INVALID = 'rpki_invalid';
    const KEY_RTBH         = 'rtbh';
    const KEY_DOS_PROTECT  = 'dos_protect';

    use TimestampAbleTrait;
    use UniversallyUniqueIdentifierTrait;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $tableId;

    /**
     * @var string|null
     */
    private $peerName;

    /**
     * @var mixed
     */
    private $peerAsn;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string
     */
    private $network;

    /**
     * @var string|null
     */
    private $neighborName = 'n/a';

    /**
     * @var mixed
     */
    private $neighborAsn = 'n/a';

    /**
     * @var string|null
     */
    private $nextHop;

    /**
     * @var string
     */
    private $fromProtocol;

    /**
     * @var bool
     */
    private $primary = false;

    /**
     * @var array
     */
    private $flags = [];

    /**
     * @var int
     */
    private $metric;

    /**
     * @var array
     */
    private $communities = [];

    /**
     * @var array
     */
    private $largeCommunities = [];

    /**
     * @var array
     */
    private $extendedCommunities = [];

    /**
     * @var array
     */
    private $asPath = [];

    /**
     * @var string|null
     */
    private $blob;


    /**
     * RouteData constructor.
     */
    public function __construct()
    {
        $this->id = UniversallyUniqueIdentifierTrait::createUuid();
    }


    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }


    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }


    /**
     * @return string
     */
    public function getTableId(): string
    {
        return $this->tableId;
    }


    /**
     * @param string $tableId
     */
    public function setTableId(string $tableId): void
    {
        $this->tableId = $tableId;
    }


    /**
     * @return string|null
     */
    public function getPeerName(): ?string
    {
        return $this->peerName;
    }


    /**
     * @param string|null $peerName
     */
    public function setPeerName(?string $peerName): void
    {
        $this->peerName = $peerName;
    }


    /**
     * @return mixed
     */
    public function getPeerAsn()
    {
        return $this->peerAsn;
    }


    /**
     * @param mixed $peerAsn
     */
    public function setPeerAsn($peerAsn): void
    {
        $this->peerAsn = $peerAsn;
    }


    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }


    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }


    /**
     * @return string
     */
    public function getNetwork(): string
    {
        return $this->network;
    }


    /**
     * @param string $network
     */
    public function setNetwork(string $network): void
    {
        $this->network = $network;
    }


    /**
     * @return string|null
     */
    public function getNeighborName(): ?string
    {
        return $this->neighborName;
    }


    /**
     * @param string|null $neighborName
     */
    public function setNeighborName(?string $neighborName): void
    {
        $this->neighborName = $neighborName;
    }


    /**
     * @return mixed
     */
    public function getNeighborAsn()
    {
        return $this->neighborAsn;
    }


    /**
     * @param mixed $neighborAsn
     */
    public function setNeighborAsn($neighborAsn): void
    {
        $this->neighborAsn = $neighborAsn;
    }


    /**
     * @return string|null
     */
    public function getNextHop(): ?string
    {
        return $this->nextHop;
    }


    /**
     * @param string $nextHop
     */
    public function setNextHop(string $nextHop): void
    {
        $this->nextHop = $nextHop;
    }


    /**
     * @return string
     */
    public function getFromProtocol(): string
    {
        return $this->fromProtocol;
    }


    /**
     * @param string $fromProtocol
     */
    public function setFromProtocol(string $fromProtocol): void
    {
        $this->fromProtocol = $fromProtocol;
    }


    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->primary;
    }


    /**
     * @param bool $primary
     */
    public function setPrimary(bool $primary): void
    {
        $this->primary = $primary;
    }


    /**
     * @return array
     */
    public function getFlags(): array
    {
        return $this->flags;
    }


    public function getSortedFlags()
    {
        $flags = $this->flags;

        usort(
            $flags,
            function ($a, $b) {
                return $a['weight'] <=> $b['weight'];
            }
        );

        return $flags;
    }


    public function addFlag($flag)
    {
        $id = $flag['id'];

        if ($id === self::KEY_INVALID) {
            $this->removeFlag(self::KEY_VALID);
        }

        $this->flags[$id] = $flag;
    }


    public function removeFlag($id)
    {
        unset($this->flags[$id]);
    }


    /**
     * @return int
     */
    public function getMetric(): int
    {
        return $this->metric;
    }


    /**
     * @param int $metric
     */
    public function setMetric(int $metric): void
    {
        $this->metric = $metric;
    }


    /**
     * @return array
     */
    public function getCommunities(): array
    {
        return $this->communities;
    }


    /**
     * @return array
     */
    public function getSortedCommunities(): array
    {
        $communities = $this->communities;

        $results = [];

        foreach ($communities as $community) {
            $values = explode(':', $community['id']);

            $results[] = array_merge(
                $community,
                [
                    'asn'   => $values[0],
                    'value' => $values[1],
                ]
            );
        }

        usort(
            $results,
            function (array $a, array $b) {
                return $a['asn'] <=> $b['asn'] ?: $a['value'] <=> $b['value'];
            }
        );

        foreach ($results as $result) {
            unset($result['asn'], $result['value']);
        }

        return $results;
    }


    /**
     * @return array
     */
    public function getFilterCommunities(): array
    {
        $results = [];

        foreach ($this->communities as $community) {
            $values = explode(':', $community['id']);

            $key  = sprintf(BgpCommunityData::FILTER_FORMAT, $values[0], $values[1]);
            $keyX = sprintf(BgpCommunityData::FILTER_FORMAT, $values[0], '*');

            $results[$key]  = $key;
            $results[$keyX] = $keyX;
        }

        return $results;
    }


    /**
     * @param array $community
     */
    public function addCommunity(array $community)
    {
        $this->communities[] = $community;
    }


    /**
     * @return array
     */
    public function getLargeCommunities(): array
    {
        return $this->largeCommunities;
    }


    /**
     * @return array
     */
    public function getSortedLargeCommunities(): array
    {
        $communities = $this->largeCommunities;

        $results = [];

        foreach ($communities as $community) {
            $values = explode(':', $community['id']);

            $results[] = array_merge(
                $community,
                [
                    'asn'     => $values[0],
                    'value_1' => $values[1],
                    'value_2' => $values[2],
                ]
            );
        }

        usort(
            $results,
            function (array $a, array $b) {
                if ($a['asn'] === $b['asn']) {
                    if ($a['value_1'] === $b['value_1']) {

                        return $a['value_2'] <=> $b['value_2'];
                    }

                    return $a['value_1'] <=> $b['value_1'];
                }

                return $a['asn'] <=> $b['asn'];
            }
        );

        foreach ($results as $result) {
            unset($result['asn'], $result['value_1'], $result['value_2']);
        }

        return $results;
    }


    /**
     * @return array
     */
    public function getFilterLargeCommunities(): array
    {
        $results = [];

        foreach ($this->largeCommunities as $community) {
            $values = explode(':', $community['id']);

            $key   = sprintf(BgpLargeCommunityData::FILTER_FORMAT, $values[0], $values[1], $values[2]);
            $keyX  = sprintf(BgpLargeCommunityData::FILTER_FORMAT, $values[0], $values[1], '*');
            $keyXX = sprintf(BgpLargeCommunityData::FILTER_FORMAT, $values[0], '*', '*');

            $results[$key]   = $key;
            $results[$keyX]  = $keyX;
            $results[$keyXX] = $keyXX;
        }

        return $results;
    }


    /**
     * @param array $community
     */
    public function addLargeCommunity(array $community)
    {
        $this->largeCommunities[] = $community;
    }


    /**
     * @return array
     */
    public function getExtendedCommunities(): array
    {
        return $this->extendedCommunities;
    }


    /**
     * @return array
     */
    public function getSortedExtendedCommunities(): array
    {
        $communities = $this->extendedCommunities;

        $results = [];

        foreach ($communities as $community) {
            $values = explode(':', $community['id']);

            $results[] = array_merge(
                $community,
                [
                    'a' => $values[0],
                    'b' => $values[1],
                    'c' => $values[2],
                ]
            );
        }

        usort(
            $results,
            function (array $a, array $b) {
                if ($a['a'] === $b['a']) {
                    if ($a['b'] === $b['b']) {

                        return $a['c'] <=> $b['c'];
                    }

                    return $a['b'] <=> $b['b'];
                }

                return $a['a'] <=> $b['a'];
            }
        );

        foreach ($results as $result) {
            unset($result['a'], $result['b'], $result['c']);
        }

        return $results;
    }


    /**
     * @return array
     */
    public function getFilterExtendedCommunities(): array
    {
        $communities = [];

        foreach ($this->extendedCommunities as $community) {
            $values = explode(':', $community['id']);

            $key   = sprintf(BgpExtendedCommunityData::FILTER_FORMAT, $values[0], $values[1], $values[2]);
            $keyX  = sprintf(BgpExtendedCommunityData::FILTER_FORMAT, $values[0], $values[1], '*');
            $keyXX = sprintf(BgpExtendedCommunityData::FILTER_FORMAT, $values[0], '*', '*');

            $communities[$key]   = $key;
            $communities[$keyX]  = $keyX;
            $communities[$keyXX] = $keyXX;
        }

        return $communities;
    }


    /**
     * @param array $community
     */
    public function addExtendedCommunity(array $community)
    {
        $this->extendedCommunities[] = $community;
    }


    /**
     * @return array
     */
    public function getAsPath(): array
    {
        return $this->asPath;
    }


    /**
     * @param array $asPath
     */
    public function setAsPath(array $asPath): void
    {
        $this->asPath = $asPath;
    }


    /**
     * @return string|null
     */
    public function getBlob(): ?string
    {
        return $this->blob;
    }


    /**
     * @param string|null $blob
     */
    public function setBlob(?string $blob = null): void
    {
        $this->blob = $blob;
    }


    public function getBackground()
    {
        $background = 'success';

        /*if (key_exists(self::KEY_SECONDARY, $this->flags)) {
            $background = 'secondary';
        }

        if (key_exists(self::KEY_DOS_PROTECT, $this->flags)) {
            $background = 'yellow';
        }

        if (key_exists(self::KEY_RTBH, $this->flags)) {
            $background = 'orange';
        }*/

        if (key_exists(self::KEY_RPKI_INVALID, $this->flags)) {
            return 'red';
        }

        if (key_exists(self::KEY_INVALID, $this->flags)) {
            return 'red';
        }

        return $background;
    }


    /**
     * @return bool
     */
    public function isHighlighted(): bool
    {
        if (key_exists(self::KEY_INVALID, $this->flags)) {
            return true;
        }

        return false;
    }


    public function toFormattedArray()
    {
        return [
            'id'                   => $this->getUuid(),
            'table_id'             => $this->getTableId(),
            'table_name'           => $this->getTableName(),
            'peer_name'            => $this->getPeerName(),
            'peer_asn'             => $this->getPeerAsn(),
            'description'          => $this->getDescription(),
            'network'              => $this->getNetwork(),
            'next_hop'             => $this->getNextHop(),
            'neighbor_name'        => $this->getNeighborName(),
            'neighbor_asn'         => $this->getNeighborAsn(),
            'primary'              => $this->isPrimary(),
            'flags'                => array_values($this->getSortedFlags()),
            'metric'               => $this->getMetric(),
            'communities'          => [
                'count'         => count($this->getCommunities()),
                'values'        => array_values($this->getSortedCommunities()),
                'filter_values' => array_values($this->getFilterCommunities()),
            ],
            'large_communities'    => [
                'count'         => count($this->getLargeCommunities()),
                'values'        => array_values($this->getSortedLargeCommunities()),
                'filter_values' => array_values($this->getFilterLargeCommunities()),
            ],
            'extended_communities' => [
                'count'         => count($this->getExtendedCommunities()),
                'values'        => array_values($this->getSortedExtendedCommunities()),
                'filter_values' => array_values($this->getFilterExtendedCommunities()),
            ],
            'as_path'              => $this->getAsPath(),
            'highlighted'          => $this->isHighlighted(),
            'background'           => $this->getBackground(),
        ];
    }

}
