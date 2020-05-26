<?php

namespace App\Data;

use Exception;


class BgpLargeCommunityData implements BgpCommunityDataInterface
{

    const WILD_CARD     = '*';
    const GLUE          = ', ';
    const FORMATTED     = '(%s' . self::GLUE . '%s' . self::GLUE . '%s)';
    const FILTER_FORMAT = '%s:%s:%s';

    /**
     * 32 bits integer (ME)
     * @var int
     */
    private $asNumber;

    /**
     * 32 bits integer|* (ACTION)
     * @var string|int
     */
    private $value1;

    /**
     * 32 bits integer|* (YOU)
     * @var string|int
     */
    private $value2;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $label;


    /**
     * BgpLargeCommunityData constructor.
     *
     * @param array $array
     */
    public function __construct(array $array)
    {
        [$this->asNumber, $this->value1, $this->value2] = self::sanitizeArray($array);
    }


    /**
     * @return int
     */
    public function getAsNumber(): int
    {
        return $this->asNumber;
    }


    /**
     * @return string|int
     */
    public function getValue1()
    {
        return $this->value1;
    }


    /**
     * @return string|int
     */
    public function getValue2()
    {
        return $this->value2;
    }


    public function getRawValue(): string
    {
        return sprintf(self::FORMATTED, $this->asNumber, $this->value1, $this->value2);
    }


    public function getFilterValue(): string
    {
        return sprintf(self::FILTER_FORMAT, $this->asNumber, $this->value1, $this->value2);
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
        return [$this->asNumber, $this->value1, $this->value2];
    }


    public function getArrayX()
    {
        return [$this->asNumber, $this->value1, self::WILD_CARD];
    }


    public function getArrayXX()
    {
        return [$this->asNumber, self::WILD_CARD, self::WILD_CARD];
    }


    public function hasWildCardValue1()
    {
        return $this->value1 === self::WILD_CARD;
    }


    public function hasWildCardValue2()
    {
        return $this->value2 === self::WILD_CARD;
    }


    public static function sanitizeArray(array $values)
    {
        $asNumber = intval($values[0]);
        $value1   = $values[1] === self::WILD_CARD ? self::WILD_CARD : intval($values[1]);
        $value2   = $values[2] === self::WILD_CARD ? self::WILD_CARD : intval($values[2]);

        if ($value1 === self::WILD_CARD && $value2 !== self::WILD_CARD) {
            return new Exception('Wrong used wild cards in BGP Large Community!');
        }

        return [$asNumber, $value1, $value2];
    }

}
