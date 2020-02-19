<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Xvladqt\Faker\LoremFlickrProvider;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new LoremFlickrProvider($faker));

        $imageDir = $this->parameterBag->get('upload_dir');

        for ($i = 0; $i < 100; $i++) {
            $article = new Article();
            $publicationDate = $faker->dateTimeThisDecade();

            $article
                ->setTitle($faker->sentence(10, true))
                ->setContent(implode('<br>', $faker->paragraphs(rand(2, 10))))
                ->setPublicationDate($publicationDate)
                ->setAuthor($this->getReference('admin'))
                ->setCategory($this->getReference(CategoryFixtures::getRandom()))
                // 80% d'articles avec image
                ->setImage($faker->optional(0.8)->image($imageDir, 800, 300, ['computer'], false))
            ;

            $usedTags = [];

            // entre 0 et 5 tags
            for ($j = 0; $j <= rand(0, 5); $j++) {
                $article->addTag($this->getRandomTag($usedTags));
            }

            // entre 0 et 20 commentaires
            for ($k = 0; $k <= rand(0, 20); $k++) {
                $comment = new Comment();
                $comment
                    ->setArticle($article)
                    ->setUser($this->getReference('user' . rand(1, 10)))
                    ->setPublicationDate($faker->dateTimeBetween($publicationDate))
                    ->setContent(implode(PHP_EOL, $faker->sentences))
                ;

                $manager->persist($comment);
            }

            $manager->persist($article);
        }

        $manager->flush();
    }


    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
            TagFixtures::class
        ];
    }

    private function getRandomTag(&$usedTags)
    {
        $tag = $this->getReference('tag' . rand(1, 20));

        if (!in_array($tag, $usedTags)) {
            $usedTags[] = $tag;

            return $tag;
        }

        return $this->getRandomTag($usedTags);
    }
}
