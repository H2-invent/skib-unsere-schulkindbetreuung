<?php

namespace App\Tests;

use App\Controller\workflowController;
use App\Entity\Stadt;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

class WorkflowTest extends WebTestCase
{
    private $client;
    function setUp()
    {
        $this->client = static::createClient();
    }
    public function testHomePage()
    {

    }
}
