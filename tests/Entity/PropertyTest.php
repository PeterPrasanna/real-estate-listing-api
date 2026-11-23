<?php
namespace App\Tests\Entity;

use App\Entity\Property;
use PHPUnit\Framework\TestCase;

final class PropertyTest extends TestCase
{
    public function testPropertyFields(): void
    {
        $property = (new Property())->setTitle('Apartment')->setPrice(250000)->setCity('Lyon')->setPostalCode('69003')->setPropertyType('apartment');
        self::assertSame('Apartment', $property->getTitle());
        self::assertSame(250000, $property->getPrice());
        self::assertSame('Lyon', $property->getCity());
        self::assertSame('69003', $property->getPostalCode());
        self::assertSame('apartment', $property->getPropertyType());
        self::assertInstanceOf(\DateTimeImmutable::class, $property->getCreatedAt());
    }
}
