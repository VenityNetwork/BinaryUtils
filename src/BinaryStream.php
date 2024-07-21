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

namespace pocketmine\utils;

use function chr;
use function ord;
use function round;
use function strlen;
use function substr;

class BinaryStream{
	//TODO: use typed properties when https://bugs.php.net/bug.php?id=81090 is fixed

	/** @var int */
	protected $offset;
	/** @var string */
	protected $buffer;

	public function __construct(string $buffer = "", int $offset = 0){
		$this->buffer = $buffer;
		$this->offset = $offset;
	}

	/**
	 * Rewinds the stream pointer to the start.
	 */
	public function rewind() : void{
		$this->offset = 0;
	}

	public function setOffset(int $offset) : void{
		$this->offset = $offset;
	}

	public function getOffset() : int{
		return $this->offset;
	}

	public function getBuffer() : string{
		return $this->buffer;
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException if there are not enough bytes left in the buffer
	 */
	public function get(int $len) : string{
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
	public function getRemaining() : string{
		$buflen = strlen($this->buffer);
		if($this->offset >= $buflen){
			throw new BinaryDataException("No bytes left to read");
		}
		$str = substr($this->buffer, $this->offset);
		$this->offset = $buflen;
		return $str;
	}

	public function put(string $str) : void{
		$this->buffer .= $str;
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getBool() : bool{
		return $this->get(1) !== "\x00";
	}

	public function putBool(bool $v) : void{
		$this->buffer .= ($v ? "\x01" : "\x00");
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getByte() : int{
		return ord($this->get(1));
	}

	public function putByte(int $v) : void{
		$this->buffer .= chr($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getShort() : int{
		return bedrockbuf_readUnsignedShort($this->get(2)) ?? throw new BinaryDataException("Failed to read short");
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getSignedShort() : int{
		return bedrockbuf_readShort($this->get(2)) ?? throw new BinaryDataException("Failed to read signed short");
	}

	public function putShort(int $v) : void{
		$this->buffer .= bedrockbuf_writeShort($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getLShort() : int{
		return bedrockbuf_readLUnsignedShort($this->get(2)) ?? throw new BinaryDataException("Failed to read LShort");
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getSignedLShort() : int{
		return bedrockbuf_readLShort($this->get(2)) ?? throw new BinaryDataException("Failed to read signed LShort");
	}

	public function putLShort(int $v) : void{
		$this->buffer .= bedrockbuf_writeLShort($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getTriad() : int{
		return bedrockbuf_readTriad($this->get(3)) ?? throw new BinaryDataException("Failed to read triad");
	}

	public function putTriad(int $v) : void{
		$this->buffer .= bedrockbuf_writeTriad($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getLTriad() : int{
		return bedrockbuf_readLTriad($this->get(3)) ?? throw new BinaryDataException("Failed to read LTriad");
	}

	public function putLTriad(int $v) : void{
		$this->buffer .= bedrockbuf_writeLTriad($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getInt() : int{
		return bedrockbuf_readInt($this->get(4)) ?? throw new BinaryDataException("Failed to read int");
	}

	public function putInt(int $v) : void{
		$this->buffer .= bedrockbuf_writeInt($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getLInt() : int{
		return bedrockbuf_readLInt($this->get(4)) ?? throw new BinaryDataException("Failed to read LInt");
	}

	public function putLInt(int $v) : void{
		$this->buffer .= bedrockbuf_writeLInt($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getFloat() : float{
		return Binary::readFloat($this->get(4));
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getRoundedFloat(int $accuracy) : float{
		return round(bedrockbuf_readFloat($this->get(4)) ?? throw new BinaryDataException("Failed to read float"), $accuracy);
	}

	public function putFloat(float $v) : void{
		$this->buffer .= bedrockbuf_writeFloat($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getLFloat() : float{
		return bedrockbuf_readLFloat($this->get(4)) ?? throw new BinaryDataException("Failed to read LFloat");
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getRoundedLFloat(int $accuracy) : float{
		return round(bedrockbuf_readLFloat($this->get(4)) ?? throw new BinaryDataException("Failed to read LFloat"), $accuracy);
	}

	public function putLFloat(float $v) : void{
		$this->buffer .= bedrockbuf_writeLFloat($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getDouble() : float{
		return bedrockbuf_readDouble($this->get(8)) ?? throw new BinaryDataException("Failed to read double");
	}

	public function putDouble(float $v) : void{
		$this->buffer .= bedrockbuf_writeDouble($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getLDouble() : float{
		return bedrockbuf_readLDouble($this->get(8)) ?? throw new BinaryDataException("Failed to read LDouble");
	}

	public function putLDouble(float $v) : void{
		$this->buffer .= bedrockbuf_writeLDouble($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getLong() : int{
		return bedrockbuf_readLong($this->get(8)) ?? throw new BinaryDataException("Failed to read long");
	}

	public function putLong(int $v) : void{
		$this->buffer .= bedrockbuf_writeLong($v);
	}

	/**
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getLLong() : int{
		return bedrockbuf_readLLong($this->get(8)) ?? throw new BinaryDataException("Failed to read LLong");
	}

	public function putLLong(int $v) : void{
		$this->buffer .= bedrockbuf_writeLLong($v);
	}

	/**
	 * Reads a 32-bit variable-length unsigned integer from the buffer and returns it.
	 *
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getUnsignedVarInt() : int{
		return bedrockbuf_readVarInt($this->buffer, $this->offset, false) ?? throw new BinaryDataException("Failed to read UnsignedVarInt");
	}

	/**
	 * Writes a 32-bit variable-length unsigned integer to the end of the buffer.
	 */
	public function putUnsignedVarInt(int $v) : void{
		$this->buffer .= bedrockbuf_writeVarInt($v, false);
	}

	/**
	 * Reads a 32-bit zigzag-encoded variable-length integer from the buffer and returns it.
	 *
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getVarInt() : int{
		return bedrockbuf_readVarInt($this->buffer, $this->offset, true) ?? throw new BinaryDataException("Failed to read VarInt");
	}

	/**
	 * Writes a 32-bit zigzag-encoded variable-length integer to the end of the buffer.
	 */
	public function putVarInt(int $v) : void{
		$this->buffer .= bedrockbuf_writeVarInt($v, true);
	}

	/**
	 * Reads a 64-bit variable-length integer from the buffer and returns it.
	 *
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getUnsignedVarLong() : int{
		return bedrockbuf_readVarLong($this->buffer, $this->offset, false) ?? throw new BinaryDataException("Failed to read UnsignedVarLong");
	}

	/**
	 * Writes a 64-bit variable-length integer to the end of the buffer.
	 */
	public function putUnsignedVarLong(int $v) : void{
		$this->buffer .= bedrockbuf_writeVarLong($v, false);
	}

	/**
	 * Reads a 64-bit zigzag-encoded variable-length integer from the buffer and returns it.
	 *
	 * @phpstan-impure
	 * @throws BinaryDataException
	 */
	public function getVarLong() : int{
		return bedrockbuf_readVarLong($this->buffer, $this->offset, true) ?? throw new BinaryDataException("Failed to read VarLong");
	}

	/**
	 * Writes a 64-bit zigzag-encoded variable-length integer to the end of the buffer.
	 */
	public function putVarLong(int $v) : void{
		$this->buffer .= bedrockbuf_writeVarLong($v, true);
	}

	/**
	 * Returns whether the offset has reached the end of the buffer.
	 */
	public function feof() : bool{
		return !isset($this->buffer[$this->offset]);
	}
}
