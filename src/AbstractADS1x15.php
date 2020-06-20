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


use Ikarus\SPS\Raspberry\Adafruit\Exception\InvalidChannelException;
use TASoft\Bus\I2C;

class AbstractADS1x15
{
	const CONVERSION_DELAY = 9;

	// Single channels
	const CHANNEL_0 = 0;
	const CHANNEL_1 = 1;
	const CHANNEL_2 = 2;
	const CHANNEL_3 = 3;

	// Channel differences
	const CHANNEL_DIFF_0_1 = 0;
	const CHANNEL_DIFF_0_3 = 1;
	const CHANNEL_DIFF_1_3 = 2;
	const CHANNEL_DIFF_2_3 = 3;

	// Voltage +/- 6.114V
	const GAIN_2_3 = 2;

	// Voltage +/- 4.096V
	const GAIN_1 = 1;

	// Voltage +/- 2.048V
	const GAIN_2 = 0; // default

	// Voltage +/- 1.024V
	const GAIN_4 = 4;

	// Voltage +/- 0.512V
	const GAIN_8 = 8;

	// Voltage +/- 0.256V
	const GAIN_16 = 16;

	// Data rate
	const DR_128_SPS = 128;
	const DR_250_SPS = 250;
	// and even more specified by direct chip class (ADS1015.php or ADS1115.php)

	// REGISTERS
	const REGISTER_CONVERSION = 0x00;
	const REGISTER_CONFIG = 0x01;
	const REGISTER_LOW_TRESHOLD = 0x02;
	const REGISTER_HIGH_TRESHOLD = 0x03;

	// CONFIGURATION
	const CONFIG_OS_SINGLE = 0x8000;
	const CONFIG_OS_BUSY = 0x0000;
	const CONFIG_OS_READY = 0x8000;

	const CONFIG_MODE_CONTINOUS = 0x0000;
	const CONFIG_MODE_SINGLE = 0x0100;

	const CONFIG_COMPERATOR_TRADITIONAL = 0x0000;
	const CONFIG_COMPERATOR_WINDOW = 0x0010;

	const CONFIG_ACTIVE_LOW = 0x0000;
	const CONFIG_ACTIVE_HIGH = 0x0008;

	const CONFIG_LATCH_DISABLED = 0x0000;
	const CONFIG_LATCH_ENABLED = 0x0004;

	const CONFIG_QUE_1CONV = 0x0000;
	const CONFIG_QUE_2CONV = 0x0001;
	const CONFIG_QUE_4CONV = 0x0002;
	const CONFIG_QUE_NONE = 0x0003;

	// Flags mapping
	protected $singleChannelMap = [
		self::CHANNEL_0 => 0x4000,
		self::CHANNEL_1 => 0x5000,
		self::CHANNEL_2 => 0x6000,
		self::CHANNEL_3 => 0x7000,
	];

	protected $differencialChannelMap = [
		self::CHANNEL_DIFF_0_1 => 0x0000,
		self::CHANNEL_DIFF_0_3 => 0x1000,
		self::CHANNEL_DIFF_1_3 => 0x2000,
		self::CHANNEL_DIFF_2_3 => 0x3000,
	];

	protected $gainMap = [
		self::GAIN_2_3 => 0x0000,
		self::GAIN_1 => 0x0200,
		self::GAIN_2 => 0x0400,
		self::GAIN_4 => 0x0600,
		self::GAIN_8 => 0x0800,
		self::GAIN_16 => 0x0A00,
	];

	protected $dataRateMap = [
		self::DR_128_SPS => 0x0000,
		self::DR_250_SPS => 0x0000
	];

	/** @var I2C */
	private $i2cBus;
	protected $gain = self::GAIN_2;
	protected $dataRate = self::DR_128_SPS;

	/**
	 * AbstractADS1x15 constructor.
	 * @param I2C $i2cBus
	 */
	public function __construct(I2C $i2cBus)
	{
		$this->i2cBus = $i2cBus;
	}

	/**
	 * @return I2C
	 */
	public function getI2cBus(): I2C
	{
		return $this->i2cBus;
	}

	/**
	 * @return int
	 */
	public function getGain(): int
	{
		return $this->gain;
	}

	/**
	 * @param int $gain
	 * @return static
	 */
	public function setGain(int $gain)
	{
		$this->gain = $gain;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getDataRate(): int
	{
		return $this->dataRate;
	}

	/**
	 * @param int $dataRate
	 * @return static
	 */
	public function setDataRate(int $dataRate)
	{
		$this->dataRate = $dataRate;
		return $this;
	}

	/**
	 * Converts a 16 bit integer result from i2c into the corresponding device integer
	 *
	 * @param int $integer
	 * @return int
	 */
	public static function convertInteger(int $integer): int {
		return $integer;
	}

	/**
	 * Helper method to setup the default configuration
	 *
	 * @param int $channel
	 * @param array $map
	 * @param bool $dr
	 * @param bool $gain
	 * @return int
	 */
	protected function getMainConfig(int $channel, array $map, bool $dr = true, bool $gain = true): int {
		$config =
			static::CONFIG_QUE_NONE |
			static::CONFIG_LATCH_DISABLED |
			static::CONFIG_ACTIVE_LOW |
			static::CONFIG_COMPERATOR_TRADITIONAL |
			static::CONFIG_MODE_SINGLE
		;

		$addr = $map[$channel] ?? NULL;
		if(NULL === $addr)
			throw (new InvalidChannelException("Channel map not found for channel $channel"))->setChannel($channel);

		$config |= $addr;

		if($dr)		$config |= $this->dataRateMap[ $this->getDataRate() ] ?? 0x0;
		if($gain)	$config |= $this->gainMap[ $this->getGain() ] ?? 0x0;
		return $config;
	}

	/**
	 * Reads a single analog value from the specified channel 0 until 3.
	 *
	 * @param int $channel
	 * @return int
	 */
	public function readAnalogValue(int $channel): int {
		$config = $this->getMainConfig($channel, $this->singleChannelMap);
		$config |= static::CONFIG_OS_SINGLE;

		$this->i2cBus->write16( static::REGISTER_CONFIG, $config);
		usleep( static::CONVERSION_DELAY * 1000 );
		$this->i2cBus->writeRegister(static::REGISTER_CONVERSION);
		$result = $this->i2cBus->read2Bytes();
		return static::convertInteger( $result );
	}

	/**
	 * Reads the differential between two channels.
	 *
	 * @param int $channelDiff
	 * @return int
	 * @see AbstractADS1x15::CHANNEL_DIFF_* constants
	 */
	public function readAnalogDifferential(int $channelDiff): int {
		$config = $this->getMainConfig($channelDiff, $this->differencialChannelMap);
		$config |= static::CONFIG_OS_SINGLE;

		$this->i2cBus->write16( static::REGISTER_CONFIG, $config);
		usleep( static::CONVERSION_DELAY * 1000 );
		$this->i2cBus->writeRegister(static::REGISTER_CONVERSION);
		$result = $this->i2cBus->read2Bytes();
		return static::convertInteger( $result );
	}

	// Display voltage mapping
	protected $gainVoltageMap = [
		self::GAIN_2_3 => 6.114,
		self::GAIN_1 => 4.096,
		self::GAIN_2 => 2.048,
		self::GAIN_4 => 1.024,
		self::GAIN_8 => 0.512,
		self::GAIN_16 => 0.256,
	];

	/**
	 * Converts a returned value into its corresponding voltage.
	 *
	 * @param int $value
	 * @return float
	 */
	public function convertVoltage(int $value) {
		$q = $this->gainVoltageMap[ $this->getGain() ] ?? 0;
		return (float)$value*$q/32768.0;
	}
}