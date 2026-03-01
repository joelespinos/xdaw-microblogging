<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserProfileModel;
use App\Models\PiwladaPostModel;
use App\Models\PiwladaMediaModel;
use App\Entities\UserProfileEntity;
use App\Entities\PiwladaPostEntity;
use App\Entities\PiwladaMediaEntity;
use Ramsey\Uuid\Uuid;
use Faker\Factory;

class SeedInitDatabaseSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('es_ES');

        $userModel    = new UserProfileModel();
        $piwladaModel = new PiwladaPostModel();
        $mediaModel   = new PiwladaMediaModel();

        $roles = ['standard', 'standard', 'standard', 'standard', 'admin'];

        foreach ($roles as $role) {

            $user = new UserProfileEntity([
                'user_uuid' => Uuid::uuid7(),
                'username'  => $faker->userName(),
                'email'     => $faker->unique()->safeEmail(),
                'role'      => $role
            ]);

            $user->setPassword('1234');
            $userModel->save($user);

            for ($i = 0; $i < 5; $i++) {

                $piwlada = new PiwladaPostEntity([
                    'piwlada_uuid' => Uuid::uuid7(),
                    'user_uuid'    => $user->user_uuid,
                    'parent_uuid'  => null,
                    'content'      => $faker->realText(120),
                    'visibility'   => 'public'
                ]);

                $piwladaModel->save($piwlada);

                // Media
                for ($j = 0; $j < 2; $j++) {

                    $randomNumber = random_int(1, 4);
                    $fileName = "default-{$randomNumber}.jpg";
                    
                    $media = new PiwladaMediaEntity([
                        'media_uuid'            => Uuid::uuid7(),
                        'piwlada_uuid'          => $piwlada->piwlada_uuid,
                        'file_path'             => "seeder-images/{$fileName}",
                        'file_original_name'    => $fileName,
                        'mime_type'             => 'image/jpeg'
                    ]);
                        
                    $mediaModel->save($media);
                }

                // Comments
                for ($k = 0; $k < 15; $k++) {

                    $comment = new PiwladaPostEntity([
                        'piwlada_uuid' => Uuid::uuid7(),
                        'user_uuid'    => $user->user_uuid,
                        'parent_uuid'  => $piwlada->piwlada_uuid,
                        'content'      => $faker->realText(80),
                        'visibility'   => 'public'
                    ]);

                    $piwladaModel->save($comment);
                }
            }
        }
    }
}