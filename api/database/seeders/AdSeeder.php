<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ad;
use App\Models\User;

class AdSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            $user = User::create([
                'firstname' => 'Demo',
                'lastname'  => 'User',
                'email'     => 'demo@example.com',
                'password'  => bcrypt('password'),
            ]);
        }

        $ads = [
            [
                'title'       => 'Vélo de ville à vendre',
                'description' => 'Vélo en bon état, peu utilisé.',
                'city'        => 'Nice',
                'lat'         => 43.7102 + (rand(-50, 50) / 1000),
                'lng'         => 7.2620  + (rand(-50, 50) / 1000),
            ],
            [
                'title'       => 'Canapé 3 places',
                'description' => 'Canapé gris, très bon état.',
                'city'        => 'Antibes',
                'lat'         => 43.5804 + (rand(-50, 50) / 1000),
                'lng'         => 7.1282  + (rand(-50, 50) / 1000),
            ],
            [
                'title'       => 'iPhone 13 - 128Go',
                'description' => 'Vendu avec accessoires.',
                'city'        => 'Cannes',
                'lat'         => 43.5528 + (rand(-50, 50) / 1000),
                'lng'         => 7.0174  + (rand(-50, 50) / 1000),
            ],
            [
                'title'       => 'Table basse en bois',
                'description' => 'Table en chêne massif.',
                'city'        => 'Grasse',
                'lat'         => 43.6585 + (rand(-50, 50) / 1000),
                'lng'         => 6.9235  + (rand(-50, 50) / 1000),
            ],
            [
                'title'       => 'Cours de guitare',
                'description' => 'Professeur expérimenté, tous niveaux.',
                'city'        => 'Sophia Antipolis',
                'lat'         => 43.6167 + (rand(-50, 50) / 1000),
                'lng'         => 7.0500  + (rand(-50, 50) / 1000),
            ],
            [
                'title'       => 'Appartement à louer',
                'description' => 'T2 meublé, proche mer.',
                'city'        => 'Juan-les-Pins',
                'lat'         => 43.5683 + (rand(-50, 50) / 1000),
                'lng'         => 7.1085  + (rand(-50, 50) / 1000),
            ],
            [
                'title'       => 'Voiture Peugeot 208',
                'description' => '2018, essence, 60 000 km.',
                'city'        => 'Cagnes-sur-Mer',
                'lat'         => 43.6641 + (rand(-50, 50) / 1000),
                'lng'         => 7.1489  + (rand(-50, 50) / 1000),
            ],
            [
                'title'       => 'Playstation 5',
                'description' => 'Avec 2 manettes et 3 jeux.',
                'city'        => 'Vence',
                'lat'         => 43.7231 + (rand(-50, 50) / 1000),
                'lng'         => 7.1123  + (rand(-50, 50) / 1000),
            ],
        ];

        foreach ($ads as $adData) {
            Ad::create([
                ...$adData,
                'user_id' => $user->id,
            ]);
        }
    }
}