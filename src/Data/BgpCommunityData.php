<?php

namespace App\Data;


class BgpCommunityData implements BgpCommunityDataInterface
{

    const WILD_CARD     = '*';
    const GLUE          = ',';
    const FORMATTED     = '(%s' . self::GLUE . '%s)';
    const FILTER_FORMAT = '%s:%s';

    /**
     * 16 bits integer
     * @var int
     */
    private $asNumber;

    /**
     * 16 bits integer|*
     * @var string|int
     */
    private $value;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $label;


    /**
     * BgpCommunityData constructor.
     *
     * @param array $array
     */
    public function __construct(array $array)
    {
        [$this->asNumber, $this->value] = self::sanitizeArray($array);
    }


    /**
     * @return int
     */
    public function getAsNumber(): int
    {
        return $this->asNumber;
    }


    /**
     * @return int|string
     */
    public function getValue()
    {
        return $this->value;
    }


    public function getRawValue(): string
    {
        return sprintf(self::FORMATTED, $this->asNumber, $this->value);
    }


    public function getFilterValue(): string
    {
        return sprintf(self::FILTER_FORMAT, $this->asNumber, $this->value);
    }


    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }


    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }


    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }


    /**
     * @param string|null $label
     */
    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }


    public function getArray()
    {
        return [$this->asNumber, $this->value];
    }


    public function getArrayX()
    {
        return [$this->asNumber, self::WILD_CARD];
    }


    public function hasWildCardValue()
    {
        return $this->value === self::WILD_CARD;
    }


    public static function sanitizeArray(array $array)
    {
        $asNumber = intval($array[0]);
        $value    = $array[1] === self::WILD_CARD ? self::WILD_CARD : intval($array[1]);

        return [$asNumber, $value];
    }

}
