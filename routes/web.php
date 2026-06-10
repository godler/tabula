<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/login', 'pages::login')->name('login');
Route::livewire('/databases', 'pages::databases')->name('databases');
Route::livewire('/databases/{database}', 'pages::database')->name('database');
Route::livewire('/databases/{database}/{table}', 'pages::table')->name('table');

Route::get('/', fn () => redirect()->route('login'));
