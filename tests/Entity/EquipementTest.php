<?php

namespace App\Tests\Entity;

use App\Entity\Equipement;
use PHPUnit\Framework\TestCase;

class EquipementTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $equipement = new Equipement();

        $equipement->setNom('Glove');
        $this->assertEquals('Glove', $equipement->getNom());

        $equipement->setCategorie('Defensive');
        $this->assertEquals('Defensive', $equipement->getCategorie());

        $equipement->setDescription('Leather baseball glove');
        $this->assertEquals('Leather baseball glove', $equipement->getDescription());

        $equipement->setImage('glove.jpg');
        $this->assertEquals('glove.jpg', $equipement->getImage());

        $equipement->setSport(1);
        $this->assertEquals(1, $equipement->getSport());
    }
}
