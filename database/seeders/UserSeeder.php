<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $pegawai = Pegawai::all();

        $users = [
            ['name' => 'Super Admin', 'username' => 'admin', 'role' => 'admin', 'pegawai_id' => null],
            ['name' => 'Supervisor', 'username' => 'spv', 'role' => 'spv', 'pegawai_id' => null],
            ['name' => 'Operator', 'username' => 'operator', 'role' => 'pegawai', 'pegawai_id' => $pegawai->first()?->id],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'pegawai_id' => $user['pegawai_id'],
                ]
            );
        }

        $spvNames = ['Sugiantoro', 'Darmawan', 'Hendrawan', 'Prasetyo', 'Kurniawan', 'Wibisono'];
        foreach ($spvNames as $i => $name) {
            $peg = $pegawai->skip($i + 1)->first();
            User::updateOrCreate(
                ['username' => 'spv' . ($i + 1)],
                [
                    'name' => 'SPV ' . $name,
                    'password' => Hash::make('password'),
                    'role' => 'spv',
                    'pegawai_id' => $peg?->id,
                ]
            );
        }

        $opNames = ['Satriawan', 'Purnomo', 'Handoko', 'Suryanto', 'Widodo', 'Setiawan', 'Hartono', 'Gunawan', 'Saputra', 'Firmansyah'];
        foreach ($opNames as $i => $name) {
            $peg = $pegawai->skip($i + 7)->first();
            User::updateOrCreate(
                ['username' => 'operator' . ($i + 1)],
                [
                    'name' => 'Operator ' . $name,
                    'password' => Hash::make('password'),
                    'role' => 'pegawai',
                    'pegawai_id' => $peg?->id,
                ]
            );
        }
    }
}
