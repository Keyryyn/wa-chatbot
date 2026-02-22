<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function (): void {
    $this->comment('Keep shipping!');
})->purpose('Display a motivational quote');
