<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    const CATEGORIES = [
        'symfony',
        'doctrine',
        'twig',
        'mysql',
        'jquery'

    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::CATEGORIES as $categoryName) {
            $category = new Category();

            $category->setName(ucfirst($categoryName));

            $manager->persist($category);
            $this->addReference($categoryName, $category);
        }

        $manager->flush();
    }

    public static function getRandom()
    {
        return self::CATEGORIES[array_rand(self::CATEGORIES)];
    }
}
