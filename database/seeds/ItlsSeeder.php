<?php

use App\Models\Itl;
use Illuminate\Database\Seeder;

class ItlsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $knownSequences = [
            [
                'model' => 'Cisco 7905',
                'sequence' => [
                    'Init:Applications',
                    'Key:Applications',
                    'Key:KeyPad3',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad2',
                    'Key:Soft2',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Init:Applications',
                ]
            ],
            [
                'model' => 'Cisco 7905',
                'sequence' => [
                    'Init:Applications',
                    'Key:Applications',
                    'Key:KeyPad3',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad2',
                    'Key:Soft2',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Init:Applications',
                ]
            ],
            [
                'model' => 'Cisco 7906',
                'sequence' => [
                    'Init:Applications',
                    'Key:Applications',
                    'Key:KeyPad3',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad2',
                    'Key:Soft2',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Init:Applications',
                ]
            ],
            [
                'model' => 'Cisco 7911',
                'sequence' => [
                    'Init:Applications',
                    'Key:Applications',
                    'Key:KeyPad3',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad2',
                    'Key:Soft2',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Init:Applications',
                ]
            ],
            [
                'model' => 'Cisco 7941',
                'sequence' => [
                    'Init:Settings',
                    'Key:Settings',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad1',
                    'Key:KeyPad2',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Init:Services'
                ]
            ],
            [
                'model' => 'Cisco 7942',
                'sequence' => [
                    'Init:Settings',
                    'Key:Settings',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad1',
                    'Key:KeyPad2',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Init:Services'
                ]
            ],
            [
                'model' => 'Cisco 7961',
                'sequence' => [
                    'Init:Settings',
                    'Key:Settings',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad1',
                    'Key:KeyPad2',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Init:Services'
                ]
            ],
            [
                'model' => 'Cisco 7962',
                'sequence' => [
                    'Init:Settings',
                    'Key:Settings',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad1',
                    'Key:KeyPad2',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Init:Services'
                ]
            ],
            [
                'model' => 'Cisco 7945',
                'sequence' => [
                    'Init:Settings',
                    'Key:Settings',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad2',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Init:Services'
                ]
            ],
            [
                'model' => 'Cisco 7965',
                'sequence' => [
                    'Init:Settings',
                    'Key:Settings',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad2',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Soft2',
                    'Init:Services'
                ]
            ],
            [
                'model' => 'Cisco 7971',
                'sequence' => [
                    'Init:Settings',
                    'Key:Settings',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad2',
                    'Key:Soft5',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft5',
                    'Init:Services'
                ]
            ],
            [
                'model' => 'Cisco 7975',
                'sequence' => [
                    'Init:Settings',
                    'Key:Settings',
                    'Key:KeyPad4',
                    'Key:KeyPad5',
                    'Key:KeyPad2',
                    'Key:Soft5',
                    'Key:Sleep',
                    'Key:KeyPadStar',
                    'Key:KeyPadStar',
                    'Key:KeyPadPound',
                    'Key:Sleep',
                    'Key:Soft5',
                    'Init:Services'
                ]
            ],
            [
                'model' => 'Cisco 8945',
                'sequence' => [
                    'Key:NavBack',
                    'Key:Sleep',
                    'Key:NavBack',
                    'Key:Sleep',
                    'Key:NavBack',
                    'Key:Sleep',
                    'Key:NavBack',
                    'Key:Sleep',
                    'Key:NavBack',
                    'Key:Sleep',
                    'Key:Settings',
                    'Key:Sleep',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:KeyPad3',
                    'Key:Sleep',
                    'Key:Soft3',
                ]
            ],
            [
                'model' => 'Cisco 8961',
                'sequence' => [
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:Applications',
                    'Key:KeyPad4',
                    'Key:KeyPad4',
                    'Key:KeyPad4',
                    'Key:Soft3',
                ]
            ],
            [
                'model' => 'Cisco 9951',
                'sequence' => [
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:Applications',
                    'Key:KeyPad4',
                    'Key:KeyPad4',
                    'Key:KeyPad4',
                    'Key:Soft3',
                ]
            ],
            [
                'model' => 'Cisco 9971',
                'sequence' => [
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:NavBack',
                    'Key:Applications',
                    'Key:KeyPad4',
                    'Key:KeyPad4',
                    'Key:KeyPad4',
                    'Key:Soft3',
                ]
            ],
            [
                'model' => 'Cisco 8831',
                'sequence' => [
                    'Key:Soft3',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Sleep',
                    'Key:Soft2',
                ]
            ],
            [
                'model' => 'Cisco 8841',
                'sequence' => [
                    'Init:Settings',
                    'Key:Sleep',
                    'Key:Settings',
                    'Key:Sleep',
                    'Key:KeyPad5',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:Soft3',
                ]
            ],
            [
                'model' => 'Cisco 8851',
                'sequence' => [
                    'Init:Settings',
                    'Key:Sleep',
                    'Key:Settings',
                    'Key:Sleep',
                    'Key:KeyPad6',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:Soft3',
                ]
            ],
            [
                'model' => 'Cisco 8861',
                'sequence' => [
                    'Init:Settings',
                    'Key:Sleep',
                    'Key:Settings',
                    'Key:Sleep',
                    'Key:KeyPad6',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:Soft3',
                ]
            ],
            [
                'model' => 'Cisco 7821',
                'sequence' => [
                    'Init:Settings',
                    'Key:Sleep',
                    'Key:Settings',
                    'Key:Sleep',
                    'Key:KeyPad5',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Sleep',
                    'Key:Soft2',
                ]
            ],
            [
                'model' => 'Cisco 7841',
                'sequence' => [
                    'Init:Settings',
                    'Key:Sleep',
                    'Key:Settings',
                    'Key:Sleep',
                    'Key:KeyPad5',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Sleep',
                    'Key:Soft2',
                ]
            ],
            [
                'model' => 'Cisco 7861',
                'sequence' => [
                    'Init:Settings',
                    'Key:Sleep',
                    'Key:Settings',
                    'Key:Sleep',
                    'Key:KeyPad5',
                    'Key:Sleep',
                    'Key:KeyPad4',
                    'Key:Sleep',
                    'Key:Soft4',
                    'Key:Sleep',
                    'Key:Soft2',
                ]
            ]
        ];

        foreach($knownSequences as $knownSequence) {
            Itl::create($knownSequence);
        }
    }
}
