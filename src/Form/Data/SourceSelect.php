<?php

namespace App\Form\Data;

use App\Data\SymbolData;


class SourceSelect
{

    const TYPE_EXPORTED_PROTOCOL = 'exported_protocol';
    const TYPE_IMPORTED_PROTOCOL = 'imported_protocol';
    const TYPE_PROTOCOL          = 'protocol';
    const TYPE_TABLE             = 'table';


    /**
     * @var SymbolData
     */
    private $symbol;

    /**
     * @var string
     */
    private $type;


    /**
     * SourceSelect constructor.
     *
     * @param SymbolData $symbol
     * @param string     $type
     */
    public function __construct(SymbolData $symbol, string $type)
    {
        $this->symbol = $symbol;
        $this->type   = $type;
    }


    /**
     * @return SymbolData
     */
    public function getSymbol(): SymbolData
    {
        return $this->symbol;
    }


    /**
     * @param SymbolData $symbol
     */
    public function setSymbol(SymbolData $symbol): void
    {
        $this->symbol = $symbol;
    }


    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

}
