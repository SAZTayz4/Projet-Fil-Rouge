<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class AuthService
{
    private $userRepository;

    public function __construct(EntityRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user || !$user->verifyPassword($password)) {
            return null;
        }

        return $user;
    }

    public function register(string $nom, string $email, string $password): User
    {
        $user = new User();
        $user->setNom($nom);
        $user->setEmail($email);
        $user->setMotDePasse($password);
        $user->setRole('ROLE_USER');
        
        $this->userRepository->save($user, true);
        
        return $user;
    }

    public function getCurrentUser(): ?User
    {
        return isset($_SESSION['user_id']) 
            ? $this->userRepository->find($_SESSION['user_id']) 
            : null;
    }
} 