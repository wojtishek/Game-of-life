<?php declare(strict_types = 1);

namespace App\Model;

use Exception;
use Latte\Engine;
use SimpleXMLElement;
use function array_column;
use function array_fill;
use function array_map;
use function array_rand;
use function array_unique;
use function count;
use function simplexml_load_file;

class GameOfLife
{

	private int $dimension;

	private int $speciesCount;

	private int $iterationsCount;

	/** @var array<int, array<int, int|string>> */
	private array $world = [];

	/** @var array<string> */
	private array $uniqueSpecies = [];

	/**
	 * @return array<int, array<int, int|string>>
	 */
	public function getWorld(): array
	{
		return $this->world;
	}

	public function loadXml(string $filePath): void
	{
		$xml = simplexml_load_file($filePath);
		if ($xml === false) {
			throw new Exception("Failed to load XML file: $filePath");
		}

		$this->dimension = (int) $xml->world->dimension;
		$this->speciesCount = (int) $xml->world->speciesCount;
		$this->iterationsCount = (int) $xml->world->iterationsCount;

		$this->world = array_fill(
			0,
			$this->dimension,
			array_fill(0, $this->dimension, 0),
		);

		$this->uniqueSpecies = array_unique(array_column($xml->xpath('organisms/organism'), 'species'));
		$this->uniqueSpecies = array_map('strval', $this->uniqueSpecies);
		if (count($this->uniqueSpecies) > $this->speciesCount) {
			throw new Exception('More species defined in XML than allowed: ' . count($this->uniqueSpecies));
		}

		if ($xml->organisms) {
			$this->fillOrganisms($xml);
		} else {
			throw new Exception("No organisms found in XML file: $filePath");
		}
	}

	public function simulate(bool $visualizeEveryStep = false): void
	{
		for ($iter = 0; $iter < $this->iterationsCount; $iter++) {
			$newWorld = array_fill(
				0,
				$this->dimension,
				array_fill(0, $this->dimension, 0),
			);

			for ($x = 0; $x < $this->dimension; $x++) {
				for ($y = 0; $y < $this->dimension; $y++) {
					$newWorld[$x][$y] = $this->calculateNewState($x, $y);
				}
			}

			$this->world = $newWorld;
			if ($visualizeEveryStep) {
				$this->visualizeWorld();
			}
		}
	}

	public function visualizeWorld(): void
	{
		$latte = new Engine();
		$latte->setTempDirectory(__DIR__ . '/../../temp');
		$params = [
			'world' => $this->world,
		];
		$latte->render(__DIR__ . '/../templates/gameOfLife.latte', $params);
	}

	public function saveToXML(string $filename): void
	{
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><life></life>');

		$world = $xml->addChild('world');
		$world->addChild('dimension', (string) $this->dimension);
		$world->addChild('speciesCount', (string) $this->speciesCount);
		$world->addChild('iterationsCount', (string) $this->iterationsCount);

		$organisms = $xml->addChild('organisms');

		for ($x = 0; $x < $this->dimension; $x++) {
			for ($y = 0; $y < $this->dimension; $y++) {
				if ($this->world[$x][$y] > 0) {
					$organism = $organisms->addChild('organism');
					$organism->addChild('x_pos', (string) $x);
					$organism->addChild('y_pos', (string) $y);
					$organism->addChild('species', $this->world[$x][$y]);
				}
			}
		}

		$xml->asXML($filename);
	}

	private function fillOrganisms(SimpleXMLElement $xml): void
	{
		foreach ($xml->organisms->organism as $organism) {
			$x = (int) $organism->x_pos;
			$y = (int) $organism->y_pos;
			$species = (string) $organism->species;
			$this->world[$x][$y] = $species;
		}
	}

	private function countNeighbors(int $x, int $y, int|string $species): int
	{
		$count = 0;
		for ($dx = -1; $dx <= 1; $dx++) {
			for ($dy = -1; $dy <= 1; $dy++) {
				if ($dx == 0 && $dy == 0) {
					continue;
				}

				$nx = $x + $dx;
				$ny = $y + $dy;

				if ($nx >= 0 && $nx < $this->dimension
					&& $ny >= 0 && $ny < $this->dimension
				) {
					if ($this->world[$nx][$ny] == $species) {
						$count++;
					}
				}
			}
		}

		return $count;
	}

	private function calculateNewState(int $x, int $y): int|string
	{
		$currentSpecies = $this->world[$x][$y];

		if ($currentSpecies !== 0) {
			$neighbors = $this->countNeighbors($x, $y, $currentSpecies);

			return $neighbors === 2 || $neighbors === 3 ? $currentSpecies : 0;
		} else {
			$birthCandidates = [];

			foreach ($this->uniqueSpecies as $species) {
				$neighbors = $this->countNeighbors($x, $y, $species);
				if ($neighbors == 3) {
					$birthCandidates[] = $species;
				}
			}

			if (count($birthCandidates) == 1) {
				return $birthCandidates[0];
			} elseif (count($birthCandidates) > 1) {
				return $birthCandidates[array_rand($birthCandidates)];
			}

			return 0;
		}
	}

}
