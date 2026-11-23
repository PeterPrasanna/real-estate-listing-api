<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserRegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof User || !$data->getPlainPassword()) {
            throw new \InvalidArgumentException('Valid registration data is required.');
        }
        $data->setPassword($this->hasher->hashPassword($data, $data->getPlainPassword()));
        $data->eraseCredentials();
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
