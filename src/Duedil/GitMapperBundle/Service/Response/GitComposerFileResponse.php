<?php
namespace Duedil\GitMapperBundle\Service\Response;

use JMS\Serializer\Annotation\Type;

class GitComposerFileResponse
{
    /**
     * @var string $name Composer package name
     * @Type("string")
     */
    private $name;

    /**
     * @var string $type Composer package type, e.g. library, module
     * @Type("string")
     */
    private $type;

    /**
     * @var string $homepage URL to home page could be GitHub
     * @Type("string")
     */
    private $homepage;

    /**
     * @return string Composer package name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name Composer package name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string Composer package type, e.g. library, module
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type Composer package type, e.g. library, module
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string URL to home page could be GitHub
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * @param string $homepage URL to home page could be GitHub
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
    }
}
