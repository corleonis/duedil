<?php
namespace Duedil\GitMapperBundle\Service\Response;

use JMS\Serializer\Annotation\Type;

class GitRepositoryContributorsResponse
{
    /**
     * Username for the contributor
     * @var string $login
     * @Type("string")
     */
    private $login;

    /**
     * GitHub link for the contributor
     * @var string $htmlUrl
     * @Type("string")
     */
    private $htmlUrl;

    /**
     * @return string Username for the contributor
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $login Username for the contributor
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return string GitHub link for the contributor
     */
    public function getHtmlUrl()
    {
        return $this->htmlUrl;
    }

    /**
     * @param string $htmlUrl GitHub link for the contributor
     */
    public function setHtmlUrl($htmlUrl)
    {
        $this->htmlUrl = $htmlUrl;
    }
}
