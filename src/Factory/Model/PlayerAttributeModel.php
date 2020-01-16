<?php declare(strict_types=1);

namespace App\Factory\Model;

use JMS\Serializer\Serializer AS Serializer;

class PlayerAttributeModel
{
    /**
     * @var int
     *
     * @Serializer\Type(name="integer")
     */
    public $attributeId;

    /**
     * @var string
     *
     * @Serializer\Type(name="string")
     */
    public $attributeName;

    /**
     * @var string
     *
     * @Serializer\Type(name="string")
     */
    public $attributeLevel;

    /**
     * @var float
     *
     * @Serializer\Type(name="float")
     */
    public $attributeLevelInNumber;

    /**
     * @var float|null
     *
     * @Serializer\Type(name="float")
     * @Serializer\Type(name="NULL")
     */
    public $attributeImprovePrice;

    /**
     * PlayerAttributeModel constructor.
     *
     * @param int $attributeId
     * @param string $attributeName
     * @param string $attributeLevel
     * @param float $attributeLevelInNumber
     * @param float|null $attributeImprovePrice
     */
    public function __construct(
        int $attributeId,
        string $attributeName,
        string $attributeLevel,
        float $attributeLevelInNumber,
        ?float $attributeImprovePrice
    ) {
        $this->attributeId = $attributeId;
        $this->attributeName = $attributeName;
        $this->attributeLevel = $attributeLevel;
        $this->attributeLevelInNumber = $attributeLevelInNumber;
        $this->attributeImprovePrice = $attributeImprovePrice;
    }
}