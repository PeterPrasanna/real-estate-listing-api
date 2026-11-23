<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\PropertyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['property:read']]),
        new Get(normalizationContext: ['groups' => ['property:read']]),
        new Post(processor: \App\State\PropertyOwnerProcessor::class, denormalizationContext: ['groups' => ['property:write']], normalizationContext: ['groups' => ['property:read']]),
        new Put(security: "object.getOwner() == user", denormalizationContext: ['groups' => ['property:write']], normalizationContext: ['groups' => ['property:read']]),
        new Patch(security: "object.getOwner() == user", denormalizationContext: ['groups' => ['property:write']], normalizationContext: ['groups' => ['property:read']]),
        new Delete(security: "object.getOwner() == user")
    ],
    normalizationContext: ['groups' => ['property:read']],
    denormalizationContext: ['groups' => ['property:write']]
)]
class Property
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['property:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    #[Groups(['property:read', 'property:write'])]
    private string $title = '';

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['property:read', 'property:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\Positive]
    #[Groups(['property:read', 'property:write'])]
    private int $price = 0;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['property:read', 'property:write'])]
    private string $city = '';

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    #[Groups(['property:read', 'property:write'])]
    private string $postalCode = '';

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['apartment', 'house', 'land', 'commercial'])]
    #[Groups(['property:read', 'property:write'])]
    private string $propertyType = 'apartment';

    #[ORM\Column]
    #[Groups(['property:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['property:read'])]
    private ?User $owner = null;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }
    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function getPrice(): int { return $this->price; }
    public function setPrice(int $price): self { $this->price = $price; return $this; }
    public function getCity(): string { return $this->city; }
    public function setCity(string $city): self { $this->city = $city; return $this; }
    public function getPostalCode(): string { return $this->postalCode; }
    public function setPostalCode(string $postalCode): self { $this->postalCode = $postalCode; return $this; }
    public function getPropertyType(): string { return $this->propertyType; }
    public function setPropertyType(string $propertyType): self { $this->propertyType = $propertyType; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getOwner(): ?User { return $this->owner; }
    public function setOwner(?User $owner): self { $this->owner = $owner; return $this; }
}
