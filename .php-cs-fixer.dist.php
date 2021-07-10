<?php declare(strict_types=1);

use Framework\CodingStandard\Config;
use Framework\CodingStandard\Finder;

return (new Config())->setFinder(
	Finder::create()->in(__DIR__)
);
