<?php
namespace Duedil\GitMapperBundle\Service\Response;

use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\AccessType;
use JMS\Serializer\Annotation\Type;

class GitIssueItems
{
    /**
     * @var string
     * @Type("string")
     * @Accessor(getter="getHtmlUrl",setter="setHtmlUrl")
     */
    private $htmlUrl;

    private $repository;

    /**
     * @return string
     */
    public function getHtmlUrl()
    {
        return $this->htmlUrl;
    }

    /**
     * GitHub repository URL. We need to remove the pull request
     * part of the URL and also as part of setting the URL we also
     * set the repository.
     * @param string $htmlUrl
     */
    public function setHtmlUrl($htmlUrl)
    {
        $this->htmlUrl = preg_replace('#/pull/[0-9]+#i', '', $htmlUrl, 1);
        $this->setRepository($this->htmlUrl);
    }

    /**
     * Private member function to set the repository path of format:
     * "symfony/symfony". Once set this cannot be overridden directly
     * but if we set new HTML URL this will be regenerated.
     * @param $htmlUrl
     */
    private function setRepository($htmlUrl)
    {
        preg_match('#com/([a-z0-9_\-\.]+/[a-z0-9_\-\.]+)#i', $htmlUrl, $matches);
        $this->repository = $matches[1];
    }

    /**
     * GitHub repository name of format: "symfony/symfony"
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
