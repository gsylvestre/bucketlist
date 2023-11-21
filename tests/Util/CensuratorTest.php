<?php

namespace App\Tests\Util;

use App\Util\Censurator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CensuratorTest extends KernelTestCase
{
    public function testPrettyStringIsReturnedTheSame(): void
    {
        $kernel = self::bootKernel();
        $censurator = static::getContainer()->get(Censurator::class);
        $this->assertSame("gentille", $censurator->purify("gentille"));
        $this->assertSame("gentille brocoli chou", $censurator->purify("gentille brocoli chou"));
    }

    public function testBadStringIsReturnedCensurated(): void
    {
        $kernel = self::bootKernel();
        $censurator = static::getContainer()->get(Censurator::class);
        $this->assertSame("***", $censurator->purify("bad"));
        $this->assertSame("mon fils est une ******", $censurator->purify("mon fils est une patate"));
    }

    public function testMultipleBadStringIsReturnedCensurated(): void
    {
        $kernel = self::bootKernel();
        $censurator = static::getContainer()->get(Censurator::class);
        $this->assertSame("le ****** n'est pas *******", $censurator->purify("le viagra n'est pas méchant"));
    }
}
