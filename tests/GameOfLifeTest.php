<?php declare(strict_types=1);

use App\Model\GameOfLife;
use Nette\Utils\FileSystem;
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
        $this->assertEquals('spec1', $game->getWorld()[1][1]);
    }

	public function testDeathOfOrganism(): void
	{
		$game = new GameOfLife();
		$game->loadXml(__DIR__ . '/assets/deathOfOrganism.xml');
		$game->simulate();
		$this->assertEquals(0, $game->getWorld()[1][1]);
	}

	public function testSaveAndLoadXml(): void
	{
		$game = new GameOfLife();
		$game->loadXml(__DIR__ . '/assets/initial.xml');
		$game->simulate();
		$game->saveToXml(__DIR__ . '/out.xml');

		$newGame = new GameOfLife();
		$newGame->loadXml(__DIR__ . '/out.xml');
		$this->assertEquals($game->getWorld(), $newGame->getWorld());
		FileSystem::delete(__DIR__ . '/out.xml');
	}
}