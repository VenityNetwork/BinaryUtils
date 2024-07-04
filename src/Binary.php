<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

/**
 * Methods for working with binary strings
 */
namespace pocketmine\utils;

use InvalidArgumentException;
use function chr;
use function ord;
use function pack;
use function preg_replace;
use function round;
use function sprintf;
use function strlen;
use function substr;
use function unpack;
use const PHP_INT_MAX;

class Binary{
	private const SIZEOF_SHORT = 2;
	private const SIZEOF_INT = 4;
	private const SIZEOF_LONG = 8;

	private const SIZEOF_FLOAT = 4;
	private const SIZEOF_DOUBLE = 8;

	public static function signByte(int $value) : int{
		return $value << 56 >> 56;
	}

	public static function unsignByte(int $value) : int{
		return $value & 0xff;
	}

	public static function signShort(int $value) : int{
		return $value << 48 >> 48;
	}

	public static function unsignShort(int $value) : int{
		return $value & 0xffff;
	}

	public static function signInt(int $value) : int{
		return $value << 32 >> 32;
	}

	public static function unsignInt(int $value) : int{
		return $value & 0xffffffff;
	}

	public static function flipShortEndianness(int $value) : int{
		return self::readLShort(self::writeShort($value));
	}

	public static function flipIntEndianness(int $value) : int{
		return self::readLInt(self::writeInt($value));
	}

	public static function flipLongEndianness(int $value) : int{
		return self::readLLong(self::writeLong($value));
	}

	/**
	 * @return mixed[]
	 * @throws BinaryDataException
	 */
	private static function safeUnpack(string $formatCode, string $bytes, int $needLength) : array{
		$haveLength = strlen($bytes);
		if($haveLength < $needLength){
			throw new BinaryDataException("Not enough bytes: need $needLength, have $haveLength");
		}
		//unpack SUCKS SO BADLY. We really need an extension to replace this garbage :(
		$result = unpack($formatCode, $bytes);
		if($result === false){
			//this should never happen; we checked the length above
			throw new \AssertionError("unpack() failed for unknown reason");
		}
		return $result;
	}

	/**
	 * Reads a byte boolean
	 */
	public static function readBool(string $b) : bool{
		return $b[0] !== "\x00";
	}

	/**
	 * Writes a byte boolean
	 */
	public static function writeBool(bool $b) : string{
		return $b ? "\x01" : "\x00";
	}

	/**
	 * Reads an unsigned byte (0 - 255)
	 *
	 * @throws BinaryDataException
	 */
	public static function readByte(string $c) : int{
		if($c === ""){
			throw new BinaryDataException("Expected a string of length 1");
		}
		return ord($c[0]);
	}

	/**
	 * Reads a signed byte (-128 - 127)
	 *
	 * @throws BinaryDataException
	 */
	public static function readSignedByte(string $c) : int{
		if($c === ""){
			throw new BinaryDataException("Expected a string of length 1");
		}
		return self::signByte(ord($c[0]));
	}

	/**
	 * Writes an unsigned/signed byte
	 */
	public static function writeByte(int $c) : string{
		return chr($c);
	}

	/**
	 * Reads a 16-bit unsigned big-endian number
	 *
	 * @throws BinaryDataException
	 */
	public static function readShort(string $str) : int{
		return bedrockbuf_readUnsignedShort($str) ?? throw new BinaryDataException("Failed to read Short");
	}

	/**
	 * Reads a 16-bit signed big-endian number
	 *
	 * @throws BinaryDataException
	 */
	public static function readSignedShort(string $str) : int{
		return bedrockbuf_readShort($str) ?? throw new BinaryDataException("Failed to read signed short");
	}

	/**
	 * Writes a 16-bit signed/unsigned big-endian number
	 */
	public static function writeShort(int $value) : string{
		return bedrockbuf_writeShort($value);
	}

	/**
	 * Reads a 16-bit unsigned little-endian number
	 *
	 * @throws BinaryDataException
	 */
	public static function readLShort(string $str) : int{
		return bedrockbuf_readLUnsignedShort($str) ?? throw new BinaryDataException("Failed to read LShort");
	}

	/**
	 * Reads a 16-bit signed little-endian number
	 *
	 * @throws BinaryDataException
	 */
	public static function readSignedLShort(string $str) : int{
		return bedrockbuf_readLShort($str);
	}

	/**
	 * Writes a 16-bit signed/unsigned little-endian number
	 */
	public static function writeLShort(int $value) : string{
		return bedrockbuf_writeShort($value);
	}

	/**
	 * Reads a 3-byte big-endian number
	 *
	 * @throws BinaryDataException
	 */
	public static function readTriad(string $str) : int{
		return bedrockbuf_readTriad($str) ?? throw new BinaryDataException("Failed to read Triad");
	}

	/**
	 * Writes a 3-byte big-endian number
	 */
	public static function writeTriad(int $value) : string{
		return bedrockbuf_writeTriad($value);
	}

	/**
	 * Reads a 3-byte little-endian number
	 *
	 * @throws BinaryDataException
	 */
	public static function readLTriad(string $str) : int{
		return bedrockbuf_readLTriad($str) ?? throw new BinaryDataException("Failed to read LTriad");
	}

	/**
	 * Writes a 3-byte little-endian number
	 */
	public static function writeLTriad(int $value) : string{
		return bedrockbuf_writeLTriad($value);
	}

	/**
	 * Reads a 4-byte signed integer
	 *
	 * @throws BinaryDataException
	 */
	public static function readInt(string $str) : int{
		return bedrockbuf_readInt($str) ?? throw new BinaryDataException("Failed to read Int");
	}

	/**
	 * Writes a 4-byte integer
	 */
	public static function writeInt(int $value) : string{
		return bedrockbuf_writeInt($value);
	}

	/**
	 * Reads a 4-byte signed little-endian integer
	 *
	 * @throws BinaryDataException
	 */
	public static function readLInt(string $str) : int{
		return bedrockbuf_readLInt($str) ?? throw new BinaryDataException("Failed to read LInt");
	}

	/**
	 * Writes a 4-byte signed little-endian integer
	 */
	public static function writeLInt(int $value) : string{
		return bedrockbuf_writeLInt($value);
	}

	/**
	 * Reads a 4-byte floating-point number
	 *
	 * @throws BinaryDataException
	 */
	public static function readFloat(string $str) : float{
		return bedrockbuf_readFloat($str) ?? throw new BinaryDataException("Failed to read Float");
	}

	/**
	 * Reads a 4-byte floating-point number, rounded to the specified number of decimal places.
	 *
	 * @throws BinaryDataException
	 */
	public static function readRoundedFloat(string $str, int $accuracy) : float{
		return round(bedrockbuf_readFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte floating-point number.
	 */
	public static function writeFloat(float $value) : string{
		return pack("G", $value);
	}

	/**
	 * Reads a 4-byte little-endian floating-point number.
	 *
	 * @throws BinaryDataException
	 */
	public static function readLFloat(string $str) : float{
		return self::safeUnpack("g", $str, self::SIZEOF_FLOAT)[1];
	}

	/**
	 * Reads a 4-byte little-endian floating-point number rounded to the specified number of decimal places.
	 *
	 * @throws BinaryDataException
	 */
	public static function readRoundedLFloat(string $str, int $accuracy) : float{
		return round(bedrockbuf_readLFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte little-endian floating-point number.
	 */
	public static function writeLFloat(float $value) : string{
		return bedrockbuf_writeLFloat($value);
	}

	/**
	 * Returns a printable floating-point number.
	 */
	public static function printFloat(float $value) : string{
		return preg_replace("/(\\.\\d+?)0+$/", "$1", sprintf("%F", $value));
	}

	/**
	 * Reads an 8-byte floating-point number.
	 *
	 * @throws BinaryDataException
	 */
	public static function readDouble(string $str) : float{
		return bedrockbuf_readDouble($str) ?? throw new BinaryDataException("Failed to read Double");
	}

	/**
	 * Writes an 8-byte floating-point number.
	 */
	public static function writeDouble(float $value) : string{
		return bedrockbuf_writeDouble($value);
	}

	/**
	 * Reads an 8-byte little-endian floating-point number.
	 *
	 * @throws BinaryDataException
	 */
	public static function readLDouble(string $str) : float{
		return bedrockbuf_readLDouble($str) ?? throw new BinaryDataException("Failed to read LDouble");
	}

	/**
	 * Writes an 8-byte floating-point little-endian number.
	 */
	public static function writeLDouble(float $value) : string{
		return bedrockbuf_writeLDouble($value);
	}

	/**
	 * Reads an 8-byte integer.
	 *
	 * @throws BinaryDataException
	 */
	public static function readLong(string $str) : int{
		return bedrockbuf_readLong($str) ?? throw new BinaryDataException("Failed to read Long");
	}

	/**
	 * Writes an 8-byte integer.
	 */
	public static function writeLong(int $value) : string{
		return bedrockbuf_writeLong($value);
	}

	/**
	 * Reads an 8-byte little-endian integer.
	 *
	 * @throws BinaryDataException
	 */
	public static function readLLong(string $str) : int{
		return bedrockbuf_readLLong($str) ?? throw new BinaryDataException("Failed to read LLong");
	}

	/**
	 * Writes an 8-byte little-endian integer.
	 */
	public static function writeLLong(int $value) : string{
		return bedrockbuf_writeLLong($value);
	}

	/**
	 * Reads a 32-bit zigzag-encoded variable-length integer.
	 *
	 * @param int    $offset reference parameter
	 *
	 * @throws BinaryDataException
	 */
	public static function readVarInt(string $buffer, int &$offset) : int{
		// TODO: this is a temporary workaround until bedrockbuf_readVarInt($buffer, $offset, true) is fixed
		$raw = self::readUnsignedVarInt($buffer, $offset);
		$temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
		return $temp ^ ($raw & (1 << 63));
	}

	/**
	 * Reads a 32-bit variable-length unsigned integer.
	 *
	 * @param int    $offset reference parameter
	 *
	 * @throws BinaryDataException if the var-int did not end after 5 bytes or there were not enough bytes
	 */
	public static function readUnsignedVarInt(string $buffer, int &$offset) : int{
		return bedrockbuf_readVarInt($buffer, $offset, false) ?? throw new BinaryDataException("Failed to read UnsignedVarInt");
	}

	/**
	 * Writes a 32-bit integer as a zigzag-encoded variable-length integer.
	 */
	public static function writeVarInt(int $v) : string{
		// TODO: this is a temporary workaround until bedrockbuf_writeVarInt($v, true) is fixed
		$v = ($v << 32 >> 32);
		return self::writeUnsignedVarInt(($v << 1) ^ ($v >> 31));
	}

	/**
	 * Writes a 32-bit unsigned integer as a variable-length integer.
	 *
	 * @return string up to 5 bytes
	 */
	public static function writeUnsignedVarInt(int $value) : string{
		return bedrockbuf_writeVarInt($value, false);
	}

	/**
	 * Reads a 64-bit zigzag-encoded variable-length integer.
	 *
	 * @param int    $offset reference parameter
	 *
	 * @throws BinaryDataException
	 */
	public static function readVarLong(string $buffer, int &$offset) : int{
		// TODO: this is a temporary workaround until bedrockbuf_readVarLong($buffer, $offset, true) is fixed
		$raw = self::readUnsignedVarLong($buffer, $offset);
		$temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
		return $temp ^ ($raw & (1 << 63));
	}

	/**
	 * Reads a 64-bit unsigned variable-length integer.
	 *
	 * @param int    $offset reference parameter
	 *
	 * @throws BinaryDataException if the var-int did not end after 10 bytes or there were not enough bytes
	 */
	public static function readUnsignedVarLong(string $buffer, int &$offset) : int{
		return bedrockbuf_readVarLong($buffer, $offset, false) ?? throw new BinaryDataException("Failed to read UnsignedVarLong");
	}

	/**
	 * Writes a 64-bit integer as a zigzag-encoded variable-length long.
	 */
	public static function writeVarLong(int $v) : string{
		// TODO: this is a temporary workaround until bedrockbuf_writeVarLong($v, true) is fixed
		return self::writeUnsignedVarLong(($v << 1) ^ ($v >> 63));
	}

	/**
	 * Writes a 64-bit unsigned integer as a variable-length long.
	 */
	public static function writeUnsignedVarLong(int $value) : string{
		return bedrockbuf_writeVarLong($value, false);
	}
}
