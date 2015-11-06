<?php
namespace Duedil\GitMapperBundle\Service;

use Doctrine\Common\Cache\ApcCache;
use Duedil\GitMapperBundle\Service\Response\GitComposerFileResponse;
use Duedil\GitMapperBundle\Service\Response\GitIssueItems;
use Duedil\GitMapperBundle\Service\Response\GitIssueSearchResponse;
use Duedil\GitMapperBundle\Service\Response\GitRepositoryContributorsResponse;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JMS\Serializer\SerializerInterface;
use Packagist\Api\Client as PackagistApi;
use Packagist\Api\Result\Package;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * Class GitMapperService
 * @package Duedil\GitMapperBundle\Service
 * @Cache(expires="+2 hours", public=true)
 */
class GitMapperService implements MapperService
{
    private static $RESULTS_PER_PAGE = 100;

    /**
     * @var int $TTL Cache time to live in seconds, 300 sec = 5 min
     */
    private static $TTL = 300;

    private static $GIT_CONTRIBUTIONS_URL = 'https://api.github.com/search/issues?q=type:pr state:closed author:%s&per_page=%s';

    private static $GIT_RAW_COMPOSER_URL = 'https://raw.githubusercontent.com/%s/master/composer.json';

    private static $GIT_REPOSITORY_CONTRIBUTORS_URL = 'https://api.github.com/repos/%s/contributors?per_page=%s';

    /**
     * @var PackagistApi
     */
    private $packagistApi;

    /**
     * @var ClientInterface Guzzle HTTP client
     */
    private $httpClient;

    /**
     * @var SerializerInterface JMS serializer
     */
    private $serializer;

    /**
     * @var ApcCache application caching
     */
    private $apcCache;

    /**
     * @var string GitHub authentication username
     */
    private $gitHubUsername;

    /**
     * @var string GitHub authentication token
     */
    private $gitHubToken;

    /**
     * @param PackagistApi $packagistApi
     * @param ClientInterface $httpClient
     * @param SerializerInterface $serializer
     * @param ApcCache $appCache
     * @param $gitHubUsername
     * @param $gitHubToken
     * @param $maxItemsPerPage
     */
    public function __construct(
        PackagistApi $packagistApi,
        ClientInterface $httpClient,
        SerializerInterface $serializer,
        ApcCache $appCache,
        $gitHubUsername,
        $gitHubToken,
        $maxItemsPerPage
    ) {
        $this->packagistApi = $packagistApi;
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->apcCache = $appCache;
        $this->gitHubUsername = $gitHubUsername;
        $this->gitHubToken = $gitHubToken;
        $this->maxItemsPerPage = $maxItemsPerPage <= 0? static::$RESULTS_PER_PAGE : $maxItemsPerPage;
    }

    /**
     * This method is using GitHub search API to provide all contributions for a
     * GitHub user base on their name. We search through the issues results for
     * all types "Pull Request", state "closed" and author "GitHub username".
     * @param string $username a valid GitHub username
     * @return GitIssueSearchResponse|null Response or null if no matching user has been found
     */
    public function getContributionsForUser($username) {
        $gitContributionsUrl = sprintf(static::$GIT_CONTRIBUTIONS_URL, urlencode($username), $this->maxItemsPerPage);
        $cacheKey = md5($gitContributionsUrl);

        if ($cachedResponse = $this->apcCache->fetch($cacheKey)) {
            return unserialize($cachedResponse);
        }

        try {
            $response = $this->httpClient->request('GET', $gitContributionsUrl);
        } catch(GuzzleException $e) {
            return null;
        }

        $data = (string)$response->getBody();
        if ($data) {
            $response = $this->serializer->deserialize($data, GitIssueSearchResponse::class, 'json');
            $this->apcCache->save($cacheKey, serialize($response), static::$TTL);
            return $response;
        }

        return null;
    }

    /**
     * Retrieve the composer.json file for a GitHub repository which contains package name
     * and other information. If the file is not present in the repository this method
     * will return null.
     * @param string $repositoryName full GitHub repository name, e.g.: "corleonis/symfony"
     * @return GitComposerFileResponse|null Composer file response or null if no file is found
     */
    public function getComposerFile($repositoryName) {
        $repositoryName = $this->cleanRepositoryName($repositoryName);
        $gitComposerUrl = sprintf(static::$GIT_RAW_COMPOSER_URL, $repositoryName);
        $cacheKey = md5($gitComposerUrl);

        if ($cachedResponse = $this->apcCache->fetch($cacheKey)) {
            return $cachedResponse;
        }

        try {
            $response = $this->httpClient->request('GET', $gitComposerUrl);
        } catch(GuzzleException $e) {
            return null;
        }

        $data = (string)$response->getBody();

        if ($data) {
            $response = $this->serializer->deserialize($data, GitComposerFileResponse::class, 'json');
            $this->apcCache->save($cacheKey, $response, static::$TTL);
            return $response;
        }

        return null;
    }

    /**
     * Check if a package exists on Packagist based on its name.
     * The name should be fully qualified like: "symfony/symfony"
     * @param $packageName Package name in format "<username>/<repository>"
     * @return bool true if the package is present on Packagist
     */
    public function packageNameExistsOnPackagist($packageName) {
        try {
            return $this->getPackageByName($packageName) instanceof Package;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if a package exists on Packagist based on its name.
     * The name should be fully qualified like: "symfony/symfony"
     * @param string $packageName Package name in format "<username>/<repository>"
     * @return Package|null the package if it's present on Packagist or NULL
     */
    public function getPackageByName($packageName) {
        try {
            return $this->packagistApi->get($packageName);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Retrieves all available contributors for a given GitHub
     * repository name or null if there is no matching repository.
     * @param string $repositoryName fill GitHub repository name, e.g.: "corleonis/symfony"
     * @return GitRepositoryContributorsResponse[]|null response or null if can't be found
     */
    public function getRepositoryContributors($repositoryName) {
        $repositoryName = $this->cleanRepositoryName($repositoryName);
        $gitRepositoryContributorsUrl = sprintf(static::$GIT_REPOSITORY_CONTRIBUTORS_URL,
            $repositoryName, $this->maxItemsPerPage);
        $cacheKey = md5($gitRepositoryContributorsUrl);

        if ($cachedResponse = $this->apcCache->fetch($cacheKey)) {
            return $cachedResponse;
        }

        try {
            $response = $this->httpClient->request('GET',
                $gitRepositoryContributorsUrl,
                ['auth' => [$this->gitHubUsername, $this->gitHubToken]]
            );
        } catch(GuzzleException $e) {
            return null;
        }

        $data = (string)$response->getBody();
        if ($data) {
            $response = $this->serializer->deserialize(
                $data, 'array<' . GitRepositoryContributorsResponse::class . '>', 'json');

            $this->apcCache->save($cacheKey, $response, static::$TTL);
            return $response;
        }

        return null;
    }

    /**
     * Merges and filters two repository items by creating an array
     * by using the GitHub repository name as a key.
     * @param GitIssueItems[] $repositoryItemsOne
     * @param GitIssueItems[] $repositoryItemsTwo
     * @return array Merged array of all GitHub repositories
     */
    public function mergeRepositories(array $repositoryItemsOne, array $repositoryItemsTwo)
    {
        $data = [];
        foreach ($repositoryItemsOne as $item) {
            $data[$item->getRepository()] = [
                'repository' => $item->getRepository(),
                'url' => $item->getHtmlUrl()
            ];
        }

        foreach ($repositoryItemsTwo as $item) {
            $data[$item->getRepository()] = [
                'repository' => $item->getRepository(),
                'url' => $item->getHtmlUrl()
            ];
        }

        return $data;
    }

    public function cleanRepositoryName($repository) {
        return preg_replace('/[^a-z0-9\-_\.\/]+/i', '', $repository);
    }
}
