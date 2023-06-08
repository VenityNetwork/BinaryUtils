<?php

namespace testing;

use pocketmine\utils\Binary;

use function microtime;
use function readUnsignedVarInt;
use function round;
use function writeUnsignedVarInt;

require_once "vendor/autoload.php";

$start = microtime(true);
for($i = 0; $i < 1_000_000; $i++) {
	$offset = 0;
	readUnsignedVarInt(writeUnsignedVarInt(999), $offset);
}
echo "Using ext-encoding: " . round(microtime(true) - $start, 4) . "s\n";

$start = microtime(true);
for($i = 0; $i < 1_000_000; $i++) {
	$offset = 0;
	Binary::readUnsignedVarInt(Binary::writeUnsignedVarInt(999), $offset);
}
echo "Using unpack() and pack(): " . round(microtime(true) - $start, 4) . "s\n";

//Result:
//Using ext-encoding: 0.0584s
//Using unpack() and pack(): 0.2774s