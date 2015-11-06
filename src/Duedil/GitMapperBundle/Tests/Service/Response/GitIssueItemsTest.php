<?php
namespace Duedil\GitMapperBundle\Service\Response;

use PHPUnit_Framework_TestCase;

class GitIssueItemsTest extends PHPUnit_Framework_TestCase {

    public function testHtmlUrlGeneratesCorrectRepositoryName()
    {
        $gitIssueItem = new GitIssueItems();
        $gitIssueItem->setHtmlUrl('http://github.com/corLeonis/testRepository.with-dot12and-slash');
        static::assertEquals('corLeonis/testRepository.with-dot12and-slash', $gitIssueItem->getRepository());
    }

    public function testPullRequestIsRemovedFromUrl()
    {
        $gitIssueItem = new GitIssueItems();
        $gitIssueItem->setHtmlUrl('http://github.com/corLeonis/testRepository/pull/123');
        static::assertEquals('http://github.com/corLeonis/testRepository', $gitIssueItem->getHtmlUrl());
    }
}
