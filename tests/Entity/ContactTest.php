<?php

namespace App\Tests\Entity;

use App\Entity\Contact;
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $contact = new Contact();

        $contact->setNom('John Doe');
        $this->assertEquals('John Doe', $contact->getNom());

        $contact->setSujet('Inquiry');
        $this->assertEquals('Inquiry', $contact->getSujet());

        $contact->setEmail('john@example.com');
        $this->assertEquals('john@example.com', $contact->getEmail());

        $contact->setMessage('Hello, I have a question.');
        $this->assertEquals('Hello, I have a question.', $contact->getMessage());
    }
}
