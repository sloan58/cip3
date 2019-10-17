<?php

use Illuminate\Database\Seeder;
use App\Models\BgImageDimension;

class BgImageDimensionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dimensions = [
            'Cisco 7911' => [
                'full_size' => '95x34',
                'thumb' => '23x8'
            ],
            'Cisco 7941' => [
                'full_size' => '320x196',
                'thumb' => '80x53'
            ],
            'Cisco 7942' => [
                'full_size' => '320x196',
                'thumb' => '80x53'
            ],
            'Cisco 7961' => [
                'full_size' => '320x196',
                'thumb' => '80x53'
            ],
            'Cisco 7962' => [
                'full_size' => '320x196',
                'thumb' => '80x53'
            ],
            'Cisco 7970' => [
                'full_size' => '320x212',
                'thumb' => '80x53'
            ],
            'Cisco 7971' => [
                'full_size' => '320x212',
                'thumb' => '80x53'
            ],
            'Cisco 7945' => [
                'full_size' => '320x212',
                'thumb' => '80x53'
            ],
            'Cisco 7965' => [
                'full_size' => '320x212',
                'thumb' => '80x53'
            ],
            'Cisco 7975' => [
                'full_size' => '320x216',
                'thumb' => '80x53'
            ],
            'Cisco 9951' => [
                'full_size' => '640x480',
                'thumb' => '123x111'
            ],
            'Cisco 9971' => [
                'full_size' => '640x480',
                'thumb' => '123x111'
            ],
            'Cisco 8845' => [
                'full_size' => '800x400',
                'thumb' => '139x109'
            ],
            'Cisco 8851' => [
                'full_size' => '800x400',
                'thumb' => '139x109'
            ],
            'Cisco 8861' => [
                'full_size' => '800x400',
                'thumb' => '139x109'
            ],
            'Cisco 8865' => [
                'full_size' => '800x400',
                'thumb' => '139x109'
            ],
        ];

        foreach ($dimensions as $key => $val) {
            BgImageDimension::firstOrCreate([
                'model' => $key,
                'full_size' => $val['full_size'],
                'thumb' => $val['thumb']
            ]);
        }
    }
}
