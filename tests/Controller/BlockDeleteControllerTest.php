<?php


namespace App\Tests\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BlockDeleteControllerTest extends WebTestCase
{
    public function testBlockDelete(){
        $client = static::createClient();

        $userRepository = self::getContainer()->get(User::class);
        $testUser = $userRepository->findOneByEmail('entwicklung@h2-invent.com');
        $client->loginUser($testUser);
        $client->request('GET','/org_block/schule/block/deleteBlock?id=18');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}