<?php

namespace Duedil\GitMapperBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Integration test
 *
 * Class DefaultControllerTest
 * @package Duedil\GitMapperBundle\Tests\Controller
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp() {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testRequestForValidData()
    {
        $this->client->request('GET', '/gitPath/shangguokan/tonicospinelli');

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals('domnikl/design-patterns-php', $content->package);
        static::assertEquals('shangguokan', $content->userOne);
        static::assertEquals('tonicospinelli', $content->userTwo);
    }

    public function testRequestForInvalidData()
    {
        $this->client->request('GET', '/gitPath/shangguokan/shangguokan');

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        static::assertEquals(400, $response->getStatusCode());
        static::assertEquals('Cannot find shortest path for same user', $content->error);
    }
}
