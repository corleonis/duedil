<?php

namespace Duedil\GitMapperBundle\Controller;

use Duedil\GitMapperBundle\Service\Response\GitComposerFileResponse;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class DefaultController
 * @package Duedil\GitMapperBundle\Controller
 * @Cache(expires="+2 hours", public=true)
 */
class DefaultController extends FOSRestController
{
    /**
     * @var int Request cache time in seconds, 900 sec = 15 min
     */
    private static $CACHE_TIME = 900;

    public function indexAction($userOne, $userTwo)
    {
        $logger = $this->get('logger');

        $userOne = trim($userOne);
        $userTwo = trim($userTwo);

        if ($userOne === $userTwo) {
            $logger->error('Cannot find shortest path for same user');
            return new JsonResponse(
                ['error' => 'Cannot find shortest path for same user'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $service = $this->container->get('git_mapper.service');

        $gitRepositoriesUserOne = $service->getContributionsForUser($userOne);
        $gitRepositoriesUserTwo = $service->getContributionsForUser($userTwo);

        $data = $service->mergeRepositories(
            $gitRepositoriesUserOne->getItems(),
            $gitRepositoriesUserTwo->getItems()
        );

        // Filter repositories that have no composer.json file
        // as those won't have packages on Packagist.
        // We also want to append the package name as that is
        // what will be used on next step to find the record on Packagist.
        $data = array_filter($data, function (&$value) use ($service)
        {
            $composerFile = $service->getComposerFile($value['repository']);
            $result = ($composerFile instanceof GitComposerFileResponse);

            if ($result && $composerFile->getName()) {
                $value['package'] = $composerFile->getName();
            } else {
                $result = false;
            }

            return $result;
        });

        // remove all items that don't have a matching package on Packagist
        $data = array_filter($data, function ($value) use ($service)
        {
            return $service->packageNameExistsOnPackagist($value['package']);
        });

        $packageName = null;
        foreach($data as $item) {
            if (empty($item['repository'])) {
                continue;
            }

            $contributors = $service->getRepositoryContributors($item['repository']);
            $hasUserOne = $hasUserTwo = false;

            // walk through all contributors for the repository
            // and check if both users have contributed
            foreach($contributors as $contributor) {

                $hasUserOne = $hasUserOne || $contributor->getLogin() === $userOne;
                $hasUserTwo = $hasUserTwo || $contributor->getLogin() === $userTwo;
                if ($hasUserOne && $hasUserTwo) {
                    $packageName = $item['package'];
                    break 2;
                }
            }
        }

        $response = new JsonResponse();
        $response->setMaxAge(static::$CACHE_TIME);
        $response->setSharedMaxAge(static::$CACHE_TIME);

        if ($packageName !== null && $packageName !== '') {
            $package = $service->getPackageByName($packageName);

            $response->setData([
                'repository' => $package->getRepository(),
                'package' => $package->getName(),
                'userOne' => $userOne,
                'userTwo' => $userTwo,
            ]);
        } else {
            $logger->error('No matching package or repository "'. $packageName .'" was found.');
            $response->setStatusCode($response::HTTP_NOT_FOUND);
            $response->setData([
                'error' => 'No matching package or repository was found.'
            ]);
        }

        return $response;
    }
}
