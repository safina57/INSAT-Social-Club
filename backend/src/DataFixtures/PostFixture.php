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
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $password = $faker->password();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $user->setFullname($faker->name)
                ->setEmail($faker->email)
                ->setUsername($faker->userName)
                ->setPassword($hashedPassword)
                ->setBirthDate($faker->dateTimeBetween('-90 years', '-18 years'))
                ->setStatus('Offline');
            $manager->persist($user);
            for ($j = 0; $j < 10; $j++) {
                $post = new Post();
                $post->setCaption($faker->realText($maxNbChars = 50, $indexSize = 2))
                    ->setReactCount($faker->numberBetween($min = 0, $max = 10))
                    ->setUser($user);
                $manager->persist($post);
            }
        }
        $manager->flush();
    }
}
