<?php declare(strict_types=1);

use App\Model\GameOfLife;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tracy\Debugger;

require_once __DIR__ . '/vendor/autoload.php';
Debugger::enable(mode: Debugger::Development, logDirectory: __DIR__ . '/log');

$loader = new ContainerLoader(__DIR__ . '/temp', autoRebuild: true);
$class = $loader->load(function ($compiler) {
    $compiler->loadConfig(__DIR__ . '/config/services.neon');
});

$container = new $class;
assert($container instanceof Container);

try {
    $gameOfLifeFile = __DIR__ . '/initial.xml';
    if (!file_exists($gameOfLifeFile)) {
        throw new Exception("Game of Life configuration file not found: $gameOfLifeFile");
    }
    $gameOfLife = $container->getByType(GameOfLife::class);
    $gameOfLife->loadXml($gameOfLifeFile);
    $gameOfLife->visualizeWorld();
    $gameOfLife->simulate(true);
} catch (Throwable $e) {
    dump($e);
}