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
 * it under the terms of the GNU Lesser General License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\utils;

#include <rules/BinaryIO.h>

use function chr;
use function ord;
use function strlen;
use function substr;

class BinaryStream{
	//TODO: use typed properties when https://bugs.php.net/bug.php?id=81090 is fixed

	/** @var int */
	var $offset;
	/** @var string */
	var $buffer;

	function __construct($buffer = "", $offset = 0){
		$this->buffer = $buffer;
		$this->offset = $offset;
	}

	/**
	 * Rewinds the stream pointer to the start.
	 */
	function rewind(){
		$this->offset = 0;
	}

	function setOffset($offset){
		$this->offset = $offset;
	}

	function getOffset(){
		return $this->offset;
	}

	function getBuffer(){
		return $this->buffer;
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException if there are not enough bytes left in the buffer
	 */
	function get(int $len){
		if($len === 0){
			return "";
		}
		if($len < 0){
			throw new \InvalidArgumentException("Length must be positive");
		}

		$remaining = strlen($this->buffer) - $this->offset;
		if($remaining < $len){
			throw new BinaryDataException("Not enough bytes left in buffer: need $len, have $remaining");
		}

		return $len === 1 ? $this->buffer[$this->offset++] : substr($this->buffer, ($this->offset += $len) - $len, $len);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getRemaining(){
		$buflen = strlen($this->buffer);
		if($this->offset >= $buflen){
			throw new BinaryDataException("No bytes left to read");
		}
		$str = substr($this->buffer, $this->offset);
		$this->offset = $buflen;
		return $str;
	}

	function put($str){
		$this->buffer .= $str;
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getBool(){
		return $this->get(1) !== "\x00";
	}

	function putBool($v){
		$this->buffer .= ($v ? "\x01" : "\x00");
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getByte(){
		return ord($this->get(1));
	}

	function putByte($v){
		$this->buffer .= chr($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getShort(){
		return \readUnsignedShortBE($this->get(2));
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getSignedShort(){
		return \readSignedShortBE($this->get(2));
	}

	function putShort($v){
		$this->buffer .= \writeShortBE($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getLShort(){
		return \readUnsignedShortLE($this->get(2));
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getSignedLShort(){
		return \readSignedShortLE($this->get(2));
	}

	function putLShort($v){
		$this->buffer .= \writeShortLE($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getTriad(){
		return Binary::readTriad($this->get(3));
	}

	function putTriad($v){
		$this->buffer .= Binary::writeTriad($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getLTriad(){
		return Binary::readLTriad($this->get(3));
	}

	function putLTriad($v){
		$this->buffer .= Binary::writeLTriad($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getInt(){
		return \readSignedIntBE($this->get(4));
	}

	function putInt($v){
		$this->buffer .= \writeIntBE($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getLInt(){
		return \readSignedIntLE($this->get(4));
	}

	function putLInt($v){
		$this->buffer .= \writeIntLE($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getFloat(){
		return \readFloatBE($this->get(4));
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getRoundedFloat($accuracy){
		return round(readFloatBE($this->get(4)), $accuracy);
	}

	function putFloat($v){
		$this->buffer .= \writeFloatBE($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getLFloat(){
		return \readFloatLE($this->get(4));
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getRoundedLFloat($accuracy){
		return round(readFloatLE($this->get(4)), $accuracy);
	}

	function putLFloat($v){
		$this->buffer .= \writeFloatLE($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getDouble(){
		return \readDoubleBE($this->get(8));
	}

	function putDouble($v){
		$this->buffer .= \writeDoubleBE($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getLDouble(){
		return \readDoubleLE($this->get(8));
	}

	function putLDouble($v){
		$this->buffer .= \writeDoubleLE($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getLong(){
		return \readSignedLongBE($this->get(8));
	}

	function putLong($v){
		$this->buffer .= \writeLongBE($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getLLong(){
		return \readSignedLongLE($this->get(8));
	}

	function putLLong($v){
		$this->buffer .= \writeLongLE($v);
	}

	/**
	 * Reads a 32-bit variable-length unsigned integer from the buffer and returns it.
	 *
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getUnsignedVarInt(){
		return \readUnsignedVarInt($this->buffer, $this->offset);
	}

	/**
	 * Writes a 32-bit variable-length unsigned integer to the end of the buffer.
	 */
	function putUnsignedVarInt($v){
		$this->put(\writeUnsignedVarInt($v));
	}

	/**
	 * Reads a 32-bit zigzag-encoded variable-length integer from the buffer and returns it.
	 *
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getVarInt(){
		return \readSignedVarInt($this->buffer, $this->offset);
	}

	/**
	 * Writes a 32-bit zigzag-encoded variable-length integer to the end of the buffer.
	 */
	function putVarInt($v){
		$this->put(\writeSignedVarInt($v));
	}

	/**
	 * Reads a 64-bit variable-length integer from the buffer and returns it.
	 *
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getUnsignedVarLong(){
		return \readUnsignedVarLong($this->buffer, $this->offset);
	}

	/**
	 * Writes a 64-bit variable-length integer to the end of the buffer.
	 */
	function putUnsignedVarLong($v){
		$this->buffer .= \writeUnsignedVarLong($v);
	}

	/**
	 * Reads a 64-bit zigzag-encoded variable-length integer from the buffer and returns it.
	 *
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	function getVarLong(){
		return \readSignedVarLong($this->buffer, $this->offset);
	}

	/**
	 * Writes a 64-bit zigzag-encoded variable-length integer to the end of the buffer.
	 */
	function putVarLong($v){
		$this->buffer .= \writeSignedVarLong($v);
	}

	/**
	 * Returns whether the offset has reached the end of the buffer.
	 */
	function feof(){
		return !isset($this->buffer[$this->offset]);
	}
}
