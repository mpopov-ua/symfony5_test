<?php


namespace App\Tests\Controller;


class ConferenceControllerTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback');
    }
}