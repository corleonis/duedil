<?php
namespace Duedil\GitMapperBundle\Service;

interface MapperService
{
    /**
     * This method is using GitHub search API to provide all contributions for a
     * GitHub user base on their name. We search through the issues results for
     * all types "Pull Request", state "closed" and author "GitHub username".
     * @param string $username a valid GitHub username
     */
    public function getContributionsForUser($username);

    /**
     * Check if a package exists on Packagist based on its name.
     * The name should be fully qualified like: "symfony/symfony"
     * @param string $packageName Package name in format "<username>/<repository>"
     */
    public function getPackageByName($packageName);

    /**
     * Retrieves all available contributors for a given GitHub
     * repository name or null if there is no matching repository.
     * @param string $repositoryName fill GitHub repository name, e.g.: "corleonis/symfony"
     */
    public function getRepositoryContributors($repositoryName);
}
