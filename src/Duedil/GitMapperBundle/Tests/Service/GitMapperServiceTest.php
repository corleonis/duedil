<?php
namespace Duedil\GitMapperBundle\Service;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ApcCache;
use Duedil\GitMapperBundle\Service\Response\GitIssueSearchResponse;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\Annotation\Type;
use GuzzleHttp\Client as GuzzleClient;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Packagist\Api\Client as PackagistClient;

/**
 * @Type("integer")
 * @var GitIssueSearchResponse
 */
class GitMapperServiceTest extends TestCase
{
    /**
     * @var GitMapperService
     */
    private $service;

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var PackagistClient|PHPUnit_Framework_MockObject_MockObject
     */
    private $packagistApi;

    /**
     * @var ClientInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $guzzleClient;

    /**
     * @var SerializerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var ApcCache|PHPUnit_Framework_MockObject_MockObject
     */
    private $apcCache;

    const VALID_USERNAME = 'valid_username';
    const INVALID_USERNAME = 'invalid_username';

    /**
     * @return null
     */
    public function setUp()
    {
        AnnotationRegistry::registerLoader('class_exists');

        $this->packagistApi = $this->getMock(PackagistClient::class);
        $this->guzzleClient = $this->getMock(GuzzleClient::class);
        $this->serializer = SerializerBuilder::create()->build();
        $this->apcCache = $this->getMock(ApcCache::class);

        $this->service = new GitMapperService(
            $this->packagistApi, $this->guzzleClient, $this->serializer, $this->apcCache,
            'gitHubUsername', 'gitHubToken', 200
        );

        parent::setUp();
    }

    public function testContributionsForValidUser() {
        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->method('getBody')
            ->will(static::returnValue('{
                "totalCount": 2,
                "items": [
                    {"htmlUrl": "http:\/\/github.com\/userNameOne\/repository-one"},
                    {"htmlUrl": "http:\/\/github.com\/userNameTwo\/another-repository"}
                ]
            }'));

        $this->guzzleClient->expects(static::any())
            ->method('request')
            ->will(static::returnValue($response));

        $contributions = $this->service->getContributionsForUser(static::VALID_USERNAME);
        static::assertEquals(2, $contributions->getTotalCount());
        static::assertContains('repository-one', $contributions->getItems()[0]);
    }

    public function testContributionsForInvalidUser() {
        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->method('getBody')
            ->will(static::returnValue(null));

        $this->guzzleClient->expects(static::any())
            ->method('request')
            ->will(static::returnValue($response));

        $contributions = $this->service->getContributionsForUser(static::INVALID_USERNAME);
        static::assertNull($contributions);
    }
}
