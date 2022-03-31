<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;


use Faker;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
    
        for ($i = 0; $i < 25; $i++) {
            $product = new Product();
            $product->setName($faker->username);
            $product->setDescription($faker->text($maxNbChars = 200));
            $product->setBrand($faker->company);
            $product->setModel($faker->personalIdentityNumber);
            $manager->persist($auteurs[$i]);
        }      
        $manager->flush();
    }
}
