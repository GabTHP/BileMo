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
        $brands = ["Gamsung", "Xiaobriel", "Gabble"];

        for ($i = 0; $i < 25; $i++) {
            $product = new Product();
            $product->setName($faker->domainWord);
            $product->setDescription($faker->text($maxNbChars = 200));
            $product->setBrand($brands[array_rand($brands, 1)]);
            $product->setModel($faker->swiftBicNumber);
            $product->setPrice($faker->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = 10000));
            $manager->persist($product);
        }
        $manager->flush();
    }
}
