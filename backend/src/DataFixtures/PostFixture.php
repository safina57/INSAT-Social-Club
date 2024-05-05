<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $user= new User();
        $user->setFullname($faker->name)
            ->setEmail($faker->email)
            ->setUsername($faker->name)
            ->setPassword($faker->password())
            ->setBirthDate($faker->dateTimeBetween('-90 years', '-18 years'))
            ->setStatus('online')
        ;
        $manager->persist($user);
        $manager->flush();
        for ($i=0; $i < 10; $i++) {
            $post = new Post();
            $post->setCaption($faker->realText($maxNbChars = 50, $indexSize = 2))
            ->setReactCount($faker->numberBetween($min = 0, $max = 10))
            ->setUser($user);
            $manager->persist($post);
        }
        $manager->flush();
    }
}
