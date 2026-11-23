<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Property;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PropertyFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create or reuse a demo user
        $repository = $manager->getRepository(User::class);
        $user = $repository->findOneBy([
            'email' => 'demo@example.com',
        ]);

        if ($user === null) {
            $user = new User();
            $user->setEmail('demo@example.com');
            $user->setRoles(['ROLE_USER']);

            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    'Demo1234!'
                )
            );

            $manager->persist($user);
        }

        // 10 sample real estate listings
        $properties = [
            ['Appartement T2 Lyon', 'Appartement lumineux proche du centre.', 180000, 'Lyon', '69003', 'apartment'],
            ['Maison familiale Lyon', 'Maison avec jardin et garage.', 420000, 'Lyon', '69005', 'house'],
            ['Studio Paris', 'Studio idéal pour investissement.', 230000, 'Paris', '75011', 'apartment'],
            ['Appartement T3 Marseille', 'Appartement avec balcon.', 195000, 'Marseille', '13006', 'apartment'],
            ['Villa Nice', 'Villa avec terrasse et piscine.', 850000, 'Nice', '06000', 'house'],
            ['Appartement Bordeaux', 'Appartement rénové en centre-ville.', 310000, 'Bordeaux', '33000', 'apartment'],
            ['Maison Toulouse', 'Maison avec trois chambres.', 345000, 'Toulouse', '31000', 'house'],
            ['Studio Lille', 'Studio proche des transports.', 125000, 'Lille', '59000', 'apartment'],
            ['Appartement T4 Lyon', 'Grand appartement familial.', 350000, 'Lyon', '69007', 'apartment'],
            ['Local commercial Nantes', 'Local commercial proche du centre.', 390000, 'Nantes', '44000', 'commercial'],
        ];

        foreach ($properties as [
            $title,
            $description,
            $price,
            $city,
            $postalCode,
            $propertyType,
        ]) {
            // Avoid duplicates when fixtures are reloaded
            $existing = $manager->getRepository(Property::class)
                ->findOneBy([
                    'title' => $title,
                    'owner' => $user,
                ]);

            if ($existing !== null) {
                continue;
            }

            $property = new Property();

            $property->setTitle($title);
            $property->setDescription($description);
            $property->setPrice($price);
            $property->setCity($city);
            $property->setPostalCode($postalCode);
            $property->setPropertyType($propertyType);
            $property->setOwner($user);

            $manager->persist($property);
        }

        $manager->flush();
    }
}


