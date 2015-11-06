<?php
namespace Duedil\GitMapperBundle\Service\Response;

use JMS\Serializer\Annotation\Type;

class GitIssueSearchResponse
{
    /**
     * @var int
     * @Type("integer")
     */
    private $totalCount;

    /**
     * @var GitIssueItems[]
     * @Type("array<Duedil\GitMapperBundle\Service\Response\GitIssueItems>")
     */
    private $items;

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
    }

    /**
     * @return GitIssueItems[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param GitIssueItems[] $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }
}
