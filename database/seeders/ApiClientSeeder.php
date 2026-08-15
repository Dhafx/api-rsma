<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ApiClient;
use Illuminate\Support\Str; 

class ApiClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // RSUP
        $plainTokenRsup = Str::random(60);
        
        $this->command->info('=============================================');
        $this->command->info('TOKEN RSUP : ' . $plainTokenRsup);
        $this->command->warn('SIMPAN TOKEN DI ATAS! (Berikan ke RSMA)');
        $this->command->info('=============================================');
        
        ApiClient::create([
            'name' => 'RSMA',
            'api_key' => hash('sha256', $plainTokenRsup),
            'ip_whitelist' => null, 
            'is_active' => true,
        ]);

        // KOMINFO
        $plainTokenKominfo = Str::random(60);
        
        $this->command->info('TOKEN KOMINFO: ' . $plainTokenKominfo);
        $this->command->warn('SIMPAN TOKEN DI ATAS! (Berikan ke pihak Kominfo)');
        $this->command->info('=============================================');
        
        ApiClient::create([
            'name' => 'KOMINFO',
            'api_key' => hash('sha256', $plainTokenKominfo),
            'ip_whitelist' => null, 
            'is_active' => true,
        ]);
    }
}
