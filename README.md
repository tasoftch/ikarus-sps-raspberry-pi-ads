# Ikarus SPS Raspberry Pi ADS1x15
This package extends your sps application and/or any other application by the Adafruit ADS1015/ADS1115 analog to digital converter for a raspberry pi device.

It enables the i2c bus for php and ships with several class wrappers to easy communicate with the ads chip.

### Installation
```bin
$ composer require ikarus/sps-raspberry-pi-ads1x15
```
#### Usage
```php
<?php
use Ikarus\SPS\Raspberry\Adafruit\ADS1115;
use TASoft\Bus\I2C;

$i2c = new I2C(0x48, 1);
$ADS = new ADS1115($i2c);

$ADS->setDataRate( ADS1115::DR_32_SPS );
$ADS->setGain( ADS1115::GAIN_16 );

for($e=0;$e<50;$e++) {
	$value = $ADS->readAnalogValue( ADS1115::CHANNEL_1 );
	printf("Hex: 0x%04x - Int: %d - Float, converted: %f V\n", $value, $value, $ADS->convertVoltage($value));

	usleep(500000);
}
```