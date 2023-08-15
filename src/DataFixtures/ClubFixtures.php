<?php

namespace App\DataFixtures;

use App\Entity\Club;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClubFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = [
            "Microsoft",
            "Enactus",
            "Chess Club",
            "Google",
            "ASCI",
            "Polycivil",
            "Polyrobot",
            "Poly Gamers",
            "Fun Club",
        ];
        for($i=0;$i<count($data);$i++){
            $club = new Club;
            $club->setName($data[$i]);
            $manager->persist($club);
            
        }
        $manager->flush();
    }
}
