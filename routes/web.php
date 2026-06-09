<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/login', 'pages::login')->name('login');
Route::livewire('/databases', 'pages::databases')->name('databases');

Route::get('/', fn () => redirect()->route('login'));
