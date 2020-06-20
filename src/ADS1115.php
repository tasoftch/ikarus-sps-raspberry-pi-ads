<?php
/**
 * Copyright (c) 2020 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Ikarus\SPS\Raspberry\Adafruit;


class ADS1115 extends AbstractADS1x15
{
	const DR_8_SPS = 8;
	const DR_16_SPS = 16;
	const DR_32_SPS = 32;
	const DR_64_SPS = 64;
	const DR_128_SPS = 128;
	const DR_250_SPS = 250;
	const DR_475_SPS = 475;
	const DR_860_SPS = 860;

	protected $dataRateMap = [
		self::DR_8_SPS => 0x0000,
		self::DR_16_SPS => 0x0020,
		self::DR_32_SPS => 0x0040,
		self::DR_64_SPS => 0x0060,
		self::DR_128_SPS => 0x0080,
		self::DR_250_SPS => 0x00A0,
		self::DR_475_SPS => 0x00C0,
		self::DR_860_SPS => 0x00E0,
	];

	protected $dataRate = self::DR_16_SPS;

	/**
	 * @inheritDoc
	 */
	public static function convertInteger(int $integer): int
	{
		return $integer > 0x7FFF ? -0x8000+($integer & 0x7FFF) : $integer;
	}
}