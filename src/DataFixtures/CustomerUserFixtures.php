<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Customer;
use App\Entity\User;

use Faker;

class CustomerUserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $customer = new Customer();
            $customer->setName($faker->company);
            $customer->setEmail($faker->email);
            $password = $this->encoder->encodePassword($customer, 'azerty');
            $customer->setPassword($password);
            $manager->persist($customer);

            for ($j = 0; $j < 5; $j++) {
                $user = new User();
                $user->setUsername($faker->username);
                $user->setFirstName($faker->firstName);
                $user->setLastName($faker->lastName);
                $user->setEmail($faker->email);
                $password = 'azerty';
                $user->setPassword($password);
                $user->setCustomer($customer);
                $manager->persist($user);
            }   
        }      
        $manager->flush();
    }
}
