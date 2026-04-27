<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUserProfileAndPiwladaPostMigration extends Migration
{
    public function up()
    {
        // Afegir el nou camp 'descriptive_name' a 'user_profile'
        $fieldsUser = [
            'descriptive_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'username'
            ],
        ];
        
        $this->forge->addColumn('user_profile', $fieldsUser);

        // Modificar el camp 'visibility' de 'piwlada_post' per afegir 'draft' al ENUM
        $fieldsPost = [
            'visibility' => [
                'name'       => 'visibility',
                'type'       => 'ENUM',
                'constraint' => ['private', 'public', 'draft'],
                'default'    => 'public',
            ],
        ];
        
        $this->forge->modifyColumn('piwlada_post', $fieldsPost);
    }

    public function down()
    {
        // Eliminar el camp 'descriptive_name' de 'user_profile'
        $this->forge->dropColumn('user_profile', 'descriptive_name');

        // Revertir el camp 'visibility' de 'piwlada_post' a l'estat original sense 'draft'
        $fieldsPost = [
            'visibility' => [
                'name'       => 'visibility',
                'type'       => 'ENUM',
                'constraint' => ['private', 'public'],
                'default'    => 'public',
            ],
        ];
        
        $this->forge->modifyColumn('piwlada_post', $fieldsPost);
    }
}
