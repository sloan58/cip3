<?php

namespace App\Console\Commands;

use App\Models\Phone;
use Illuminate\Console\Command;
use App\Jobs\PushPhoneBackgroundImageJob;

class TestBackground extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:bg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $file = Storage::url('backgrounds/MD_District_Seal.png');
        $phone = Phone::where('name', 'SEP001D452CDDB1')->first();
        PushPhoneBackgroundImageJob::dispatch($phone, \App\User::first()->email, 'MD_District_Seal.png');

    }
}
