<?php

namespace App\DataFixtures;

use App\Entity\Search;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product1 = new Search();
        $product1->setLatitude('47,3167');
        $product1->setLongitude('5,0167');
        $product1->setSearchDate(new DateTime());
        $product1->setCity('Dijon');
        
        $manager->persist($product1);

        $product2 = new Search();
        $product2->setLatitude('40,7143');
        $product2->setLongitude('-74,006');
        $product2->setSearchDate((new DateTime())->modify('-1 day'));
        $product2->setCity('New york');

        $manager->persist($product2);

        $manager->flush();
    }
}
