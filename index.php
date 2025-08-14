<?php declare(strict_types=1);

use App\Model\GameOfLife;
use Latte\Engine;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tracy\Debugger;

require_once __DIR__ . '/vendor/autoload.php';
Debugger::enable(mode: Debugger::Development, logDirectory: __DIR__ . '/log');

$loader = new ContainerLoader(__DIR__ . '/temp', autoRebuild: true);
$class = $loader->load(function ($compiler) {
    $compiler->loadConfig(__DIR__ . '/config/config.neon');
});

$container = new $class;
assert($container instanceof Container);

$latte = new Engine();
$latte->setTempDirectory(__DIR__ . '/temp/latte');
$latte->render(__DIR__ . '/app/templates/@layout-head.latte');

$fullIteration = filter_input(INPUT_GET,'full', FILTER_VALIDATE_BOOLEAN) ?? false;

try {

    $gameOfLifeFile = __DIR__ . '/initial.xml';
    if (!file_exists($gameOfLifeFile)) {
        throw new Exception("Game of Life configuration file not found: $gameOfLifeFile");
    }
    $gameOfLife = $container->getByType(GameOfLife::class);
    $gameOfLife->loadXml($gameOfLifeFile);
	$gameOfLife->visualizeWorld('Starting World');
	$gameOfLife->simulate($fullIteration);
	$gameOfLife->visualizeWorld('After Simulation');
	$gameOfLife->saveToXml(__DIR__ . '/out.xml');
} catch (Throwable $e) {
    dump($e);
}
$latte->render(__DIR__ . '/app/templates/@layout-foot.latte');
