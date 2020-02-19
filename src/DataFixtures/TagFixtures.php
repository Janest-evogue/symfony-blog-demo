<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 20; $i++) {
            $tag = new Tag();

            $tag->setName($faker->word);

            $manager->persist($tag);
            $this->addReference("tag$i", $tag);
        }

        $manager->flush();
    }
}
