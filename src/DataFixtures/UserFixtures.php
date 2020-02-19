<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /**
     * @var PasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $admin = new User();
        $admin
            ->setFirstname('Julien')
            ->setLastname('Anest')
            ->setEmail('julien.anest@gmail.com')
            ->setPassword($this->encoder->encodePassword($admin, 'Julien1'))
            ->setRole('ROLE_ADMIN')
        ;

        $manager->persist($admin);
        $this->addReference('admin', $admin);

        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user
                ->setFirstname($faker->firstName)
                ->setLastname($faker->lastName)
                ->setEmail($faker->email)
                ->setPassword($this->encoder->encodePassword($user, 'Password0'))
            ;

            $manager->persist($user);
            $this->addReference("user$i", $user);
        }

        $manager->flush();
    }
}
