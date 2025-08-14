<?php declare(strict_types=1);

namespace tests;

use App\Model\GameOfLife;
use Exception;
use PHPUnit\Framework\TestCase;

class GameOfLifeTest extends TestCase
{
    public function testCheckMoreDistinctSpeciesThanDefined(): void
    {
        $game = new GameOfLife();
        $this->expectException(Exception::class);
        $game->loadXml(__DIR__ . '/assets/moreSpecies.xml');
    }

    public function testOrganismsPresent(): void
    {
        $game = new GameOfLife();
        $this->expectException(Exception::class);
        $game->loadXml(__DIR__ . '/assets/noOrganism.xml');
    }

    public function testBirthOfOrganism(): void
    {
        $game = new GameOfLife();
        $game->loadXml(__DIR__ . '/assets/birthOfOrganism.xml');
        $game->simulate();
        $this->assertEquals('spec1', $game->world[1][1]);
    }
}