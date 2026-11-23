<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Property;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class PropertyApiTest extends ApiTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        // Test DB must be freshly migrated and dedicated to this suite.
        $this->em->createQuery('DELETE FROM App\Entity\Property p')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    private function createUser(string $email): User
    {
        $user = (new User())->setEmail($email);
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, 'TestPassword123!'));
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    private function createProperty(User $owner, string $city, int $price): Property
    {
        $property = (new Property())->setTitle('Apartment')->setCity($city)->setPrice($price)->setPostalCode('69003')->setPropertyType('apartment')->setOwner($owner);
        $this->em->persist($property);
        $this->em->flush();
        return $property;
    }

    public function testPublicSearchFilters(): void
    {
        $owner = $this->createUser('owner@example.test');
        $this->createProperty($owner, 'Lyon', 250000);
        $this->createProperty($owner, 'Paris', 700000);
        $client = static::createClient();
        $response = $client->request('GET', '/api/properties?city=Lyon&price[gte]=200000&price[lte]=300000', ['headers' => ['Accept' => 'application/ld+json']]);
        self::assertResponseIsSuccessful();
        self::assertCount(1, $response->toArray()['member'] ?? $response->toArray()['hydra:member'] ?? []);
    }

    public function testAnonymousCannotCreateProperty(): void
    {
        static::createClient()->request('POST', '/api/properties', ['json' => [
            'title' => 'Test', 'price' => 100000, 'city' => 'Lyon', 'postalCode' => '69003', 'propertyType' => 'apartment'
        ]]);
        self::assertResponseStatusCodeSame(401);
    }

    public function testDifferentUserCannotDeleteProperty(): void
    {
        $owner = $this->createUser('owner@example.test');
        $other = $this->createUser('other@example.test');
        $property = $this->createProperty($owner, 'Lyon', 250000);
        $client = static::createClient();
        $client->loginUser($other, 'api');
        $client->request('DELETE', '/api/properties/'.$property->getId());
        self::assertResponseStatusCodeSame(403);
    }
}
