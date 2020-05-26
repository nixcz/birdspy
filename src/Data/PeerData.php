<?php

namespace App\Data;


class PeerData
{

    /**
     * @var string
     */
    private $name = 'n/a';

    /**
     * @var mixed
     */
    private $asn = 'n/a';

    /**
     * @var string|null
     */
    private $table = 'n/a';

    /**
     * @var string|null
     */
    private $protocol = 'n/a';

    /**
     * @var string|null
     */
    private $ip = 'n/a';

    /**
     * @var string|null
     */
    private $description = 'n/a';


    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        if ($this->table === 'master4' || $this->table === 'master6') {
            return '';
        }

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
     * @return mixed
     */
    public function getAsn()
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
     * @return string|null
     */
    public function getTable(): ?string
    {
        return $this->table;
    }


    /**
     * @param string|null $table
     */
    public function setTable(?string $table): void
    {
        $this->table = $table;
    }


    /**
     * @return string|null
     */
    public function getProtocol(): ?string
    {
        return 'R' . substr($this->table, 1);
    }


    /**
     * @param string|null $protocol
     */
    public function setProtocol(?string $protocol): void
    {
        $this->table = 'T' . substr($protocol, 1);
    }


    /**
     * @return string|null
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }


    /**
     * @param string|null $ip
     */
    public function setIp(?string $ip): void
    {
        $this->ip = $ip;
    }


    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        if ($this->table === 'master4' || $this->table === 'master6') {
            return $this->table;
        }

        return $this->description;
    }


    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

}
