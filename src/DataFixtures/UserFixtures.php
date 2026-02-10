<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new Utilisateur();
        $admin->setEmail('admin@example.com');
        $admin->setPrenom('Admin');
        $admin->setNom('System');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setIsActive(true);
        $manager->persist($admin);

        $user = new Utilisateur();
        $user->setEmail('user@example.com');
        $user->setPrenom('John');
        $user->setNom('Doe');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $user->setIsActive(true);
        $manager->persist($user);

        $manager->flush();
    }
}