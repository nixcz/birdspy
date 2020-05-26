<?php

namespace App\Data;

use App\Data\Traits\UniversallyUniqueIdentifierTrait;
use App\Data\Traits\TimestampAbleTrait;
use DateTime;


class BgpProtocolData
{

    const STATE_ESTABLISHED = 'Established';

    const STATUS_UP = 'up';

    use TimestampAbleTrait;
    use UniversallyUniqueIdentifierTrait;

    /**
     * R6_35236x1, R4_35236x3
     *
     * @var string
     */
    private $name;

    /**
     * start, up
     *
     * @var string
     */
    private $state;

    /**
     * @var DateTime
     */
    private $stateChanged;

    /**
     * DDP.NIX.CZ - 2001:7f8:14:5ec::253 - (1), DDP.NIX.CZ - 194.50.100.253 - (1)
     *
     * @var string|null
     */
    private $description;

    /**
     * @var string
     */
    private $bgpState;

    /**
     * @var string|null
     */
    private $neighborAddress;

    /**
     * @var int
     */
    private $asn;

    /**
     * @var string
     */
    private $table;

    /**
     * @var int|null
     */
    private $importLimit;

    /**
     * @var int|null
     */
    private $routeLimit;

    /**
     * @var int|null
     */
    private $importedRoutes;

    /**
     * @var int|null
     */
    private $exportedRoutes;

    /**
     * @var int|null
     */
    private $selectedRoutes = 0;

    /**
     * @var int|null
     */
    private $invalidRoutes = 0;

    /**
     * @var string|null
     */
    private $blob;


    /**
     * BgpProtocolsData constructor.
     */
    public function __construct()
    {
        $this->id = UniversallyUniqueIdentifierTrait::createUuid();
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }


    /**
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }


    /**
     * @return DateTime
     */
    public function getStateChanged(): DateTime
    {
        return $this->stateChanged;
    }


    /**
     * @param DateTime $stateChanged
     */
    public function setStateChanged(DateTime $stateChanged): void
    {
        $this->stateChanged = $stateChanged;
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
     * @return string|null
     */
    public function getPeerName()
    {
        return preg_replace("/^(.*)\s+-\s+(.*)\s+-\s+(\(\d+\))/", "$1", $this->description);
    }


    /**
     * @return string
     */
    public function getFormattedDescription()
    {
        // WNET UKRAINE IPv4 ( 1820x2 )

        $name    = $this->getPeerName();
        $version = preg_match("/^T6_/", $this->getTable()) ? 'IPv6' : 'IPv4';
        $number  = preg_replace("/(T4_|T6_)(\d+x\d+)/", "$2", $this->getTable());

        return sprintf('%s %s (%s)', $name, $version, $number);
    }


    /**
     * @return string
     */
    public function getBgpState(): string
    {
        return $this->bgpState;
    }


    /**
     * @param string $bgpState
     */
    public function setBgpState(string $bgpState): void
    {
        $this->bgpState = $bgpState;
    }


    /**
     * @return string
     */
    public function getBgpStateShortcut(): string
    {
        return substr($this->bgpState, 0, 3);
    }


    /**
     * @return string|null
     */
    public function getNeighborAddress(): ?string
    {
        return $this->neighborAddress;
    }


    /**
     * @param string $neighborAddress
     */
    public function setNeighborAddress(string $neighborAddress): void
    {
        $this->neighborAddress = $neighborAddress;
    }


    /**
     * @return int
     */
    public function getAsn(): int
    {
        return $this->asn;
    }


    /**
     * @param int $asn
     */
    public function setAsn(int $asn): void
    {
        $this->asn = $asn;
    }


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
     * @return int|null
     */
    public function getImportLimit(): ?int
    {
        return $this->importLimit;
    }


    /**
     * @param int|null $importLimit
     */
    public function setImportLimit(?int $importLimit): void
    {
        $this->importLimit = $importLimit;
    }


    /**
     * @return int
     */
    public function getRouteLimit(): ?int
    {
        if (! is_null($this->routeLimit)) {
            return $this->routeLimit;
        }

        return $this->importedRoutes ?: 0;
    }


    /**
     * @param int|null $routeLimit
     */
    public function setRouteLimit(?int $routeLimit): void
    {
        $this->routeLimit = $routeLimit;
    }


    /**
     * @return float
     */
    public function getPfxLimitRatio()
    {
        if (! empty($this->getImportLimit())) {
            return $this->getRouteLimit() / $this->getImportLimit();
        }

        return 0;
    }


    /**
     * @return float
     */
    public function getInvalidRatio()
    {
        if (! empty($this->getImportedRoutes())) {
            return $this->getInvalidRoutes() / $this->getImportedRoutes();
        }

        return 0;
    }


    /**
     * @return int|null
     */
    public function getImportedRoutes(): ?int
    {
        return $this->getBgpState() === self::STATE_ESTABLISHED ? $this->importedRoutes : null;
    }


    /**
     * @param int|null $importedRoutes
     */
    public function setImportedRoutes(?int $importedRoutes): void
    {
        $this->importedRoutes = $importedRoutes;
    }


    /**
     * @return int|null
     */
    public function getExportedRoutes(): ?int
    {
        return $this->getBgpState() === self::STATE_ESTABLISHED ? $this->exportedRoutes : null;
    }


    /**
     * @param int|null $exportedRoutes
     */
    public function setExportedRoutes(?int $exportedRoutes): void
    {
        $this->exportedRoutes = $exportedRoutes;
    }


    /**
     * @return int|null
     */
    public function getSelectedRoutes(): ?int
    {
        return $this->selectedRoutes;
    }


    /**
     * @param int|null $selectedRoutes
     */
    public function setSelectedRoutes(?int $selectedRoutes): void
    {
        $this->selectedRoutes = $selectedRoutes;
    }


    /**
     * @return int|null
     */
    public function getInvalidRoutes(): ?int
    {
        return $this->invalidRoutes;
    }


    /**
     * @param int|null $invalidRoutes
     */
    public function setInvalidRoutes(?int $invalidRoutes): void
    {
        $this->invalidRoutes = $invalidRoutes;
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
    public function setBlob(?string $blob): void
    {
        $this->blob = $blob;
    }


    /**
     * @return bool
     */
    public function isHighlighted(): bool
    {
        if ($this->getState() !== self::STATUS_UP) {
            return true;
        }

        return false;
    }


    /**
     * @return array
     */
    public function toFormattedArray()
    {
        return [
            'id'              => $this->getUuid(),
            'peer_name'       => $this->getPeerName(),
            'table'           => $this->getTable(),
            'protocol'        => $this->getName(),
            'ip_address'      => $this->getNeighborAddress(),
            'description'     => $this->getDescription(),
            'asn'             => $this->getAsn(),
            'bgp_state'       => [
                'value'    => $this->getBgpState(),
                'shortcut' => $this->getBgpStateShortcut(),
            ],
            'import_limit'    => $this->getImportLimit(),
            'import_ratio'    => ! $this->isHighlighted() ? $this->getPfxLimitRatio() : null,
            'imported_routes' => ! $this->isHighlighted() ? $this->getImportedRoutes() : null,
            'exported_routes' => ! $this->isHighlighted() ? $this->getExportedRoutes() : null,
            'selected_routes' => ! $this->isHighlighted() ? $this->getSelectedRoutes() : null,
            'invalid_routes'  => ! $this->isHighlighted() ? $this->getInvalidRoutes() : null,
            'invalid_ratio'   => ! $this->isHighlighted() ? $this->getInvalidRatio() : null,
            'state'           => $this->getState(),
            'state_changed'   => [
                'value'     => $this->getStateChanged() ? $this->getStateChanged()->format('Y-m-d H:i:s') : null,
                'timestamp' => $this->getStateChanged() ? (int) $this->getStateChanged()->format('U') : null,
            ],
            'highlighted'     => $this->isHighlighted(),
        ];
    }

}
