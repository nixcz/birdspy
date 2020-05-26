<?php

namespace App\Data;

use Exception;


class BgpExtendedCommunityData implements BgpCommunityDataInterface
{

    const WILD_CARD     = '*';
    const GLUE          = ', ';
    const FORMATTED     = '(%s' . self::GLUE . '%s' . self::GLUE . '%s)';
    const FILTER_FORMAT = '%s:%s:%s';

    /**
     * @var string|int
     */
    private $a;

    /**
     * @var string|int
     */
    private $b;

    /**
     * @var string|int
     */
    private $c;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $label;


    /**
     * BgpExtCommunityData constructor.
     *
     * @param array $array
     */
    public function __construct(array $array)
    {
        [$this->a, $this->b, $this->c] = self::sanitizeArray($array);
    }


    /**
     * @return int|string
     */
    public function getA()
    {
        return $this->a;
    }


    /**
     * @return int|string
     */
    public function getB()
    {
        return $this->b;
    }


    /**
     * @return int|string
     */
    public function getC()
    {
        return $this->c;
    }


    public function getRawValue(): string
    {
        return sprintf(self::FORMATTED, $this->a, $this->b, $this->c);
    }


    public function getFilterValue(): string
    {
        return sprintf(self::FILTER_FORMAT, $this->a, $this->b, $this->c);
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
        return [$this->a, $this->b, $this->c];
    }


    public function getArrayX()
    {
        return [$this->a, $this->b, self::WILD_CARD];
    }


    public function getArrayXX()
    {
        return [$this->a, self::WILD_CARD, self::WILD_CARD];
    }


    public function hasWildCardB()
    {
        return $this->b === self::WILD_CARD;
    }


    public function hasWildCardC()
    {
        return $this->c === self::WILD_CARD;
    }


    public static function sanitizeArray(array $values)
    {
        // TODO data types

        $a = $values[0];
        $b = $values[1];
        $c = $values[2];

        if ($b === self::WILD_CARD && $c !== self::WILD_CARD) {
            return new Exception('Wrong used wild cards in BGP Extended Community!');
        }

        return [$a, $b, $c];
    }

}
