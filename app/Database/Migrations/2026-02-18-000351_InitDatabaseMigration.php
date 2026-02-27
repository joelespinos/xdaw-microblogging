<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitDatabaseMigration extends Migration
{
    public function up()
    {
        /*
        *  USER PROFILE
        */
        $this->forge->addField([
            'user_uuid' => [
                'type'       => 'BINARY',
                'constraint' => 16
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['standard', 'admin'],
                'default'    => 'standard',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('user_uuid');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('user_profile');

        /*
        *  PIWLADA POST
        */
        $this->forge->addField([
            'piwlada_uuid' => [
                'type'       => 'BINARY',
                'constraint' => 16
            ],
            'user_uuid' => [
                'type'       => 'BINARY',
                'constraint' => 16
            ],
            'parent_uuid' => [
                'type'       => 'BINARY',
                'constraint' => 16,
                'null' => true,
            ],
            'content' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'visibility' => [
                'type'       => 'ENUM',
                'constraint' => ['private', 'public'],
                'default'    => 'public',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('piwlada_uuid');

        // FK user_profile
        $this->forge->addForeignKey(
            'user_uuid',        // Primer camp represeta el nom del camp en la taula actual
            'user_profile',     // Segon camp representa la taula a la qual esta referenciant la FK
            'user_uuid',        // Tercer camp representa el nom del camp de la taula a la qual s'esta referenciant (es a dir la taula del segon camp)
            'CASCADE',          // haura onDeleteCascade
            'CASCADE'           // haura onUpdateCascade
        );

        // FK piwlada parent
        $this->forge->addForeignKey(
            'parent_uuid',
            'piwlada_post',
            'piwlada_uuid',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('piwlada_post');

        /*
        *  PIWLADA MEDIA (IMAGES)
        */
        $this->forge->addField([
            'media_uuid' => [
                'type'       => 'BINARY',
                'constraint' => 16
            ],
            'piwlada_uuid' => [
                'type'       => 'BINARY',
                'constraint' => 16
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'file_original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('media_uuid');

        $this->forge->addForeignKey(
            'piwlada_uuid',
            'piwlada_post',
            'piwlada_uuid',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('piwlada_media');
    }

    public function down()
    {
        $this->forge->dropTable('piwlada_media');
        $this->forge->dropTable('piwlada_post');
        $this->forge->dropTable('user_profile');
    }
}
