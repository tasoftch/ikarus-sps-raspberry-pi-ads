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


class ADS1015 extends AbstractADS1x15
{
	const CONVERSION_DELAY = 1;

	const DR_128_SPS = 128;
	const DR_250_SPS = 250;
	const DR_490_SPS = 490;
	const DR_920_SPS = 920;
	const DR_1600_SPS = 1600;
	const DR_2400_SPS = 2400;
	const DR_3300_SPS = 3300;

	protected $dataRateMap = [
		self::DR_128_SPS => 0x0000,
		self::DR_250_SPS => 0x0020,
		self::DR_490_SPS => 0x0040,
		self::DR_920_SPS => 0x0060,
		self::DR_1600_SPS => 0x0080,
		self::DR_2400_SPS => 0x00A0,
		self::DR_3300_SPS => 0x00C0,
	];

	protected $dataRate = self::DR_1600_SPS;

	/**
	 * @inheritDoc
	 */
	public static function convertInteger(int $integer): int
	{
		$integer >>= 4;
		return $integer > 0x07FF ? -0x0800+($integer & 0x07FF) : $integer;
	}

	/**
	 * @inheritDoc
	 */
	public function convertVoltage(int $value) {
		$q = $this->gainVoltageMap[ $this->getGain() ] ?? 0;
		return (float)$value*$q/2048.0;
	}
}