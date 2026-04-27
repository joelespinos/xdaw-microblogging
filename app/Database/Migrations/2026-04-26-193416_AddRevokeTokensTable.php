<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRevokeTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'tokenid'          => [
                'type'           => 'VARCHAR',
                'constraint'     => '36',
                'null'           => false,
            ],
            'subject'          => [
                'type'           => 'VARCHAR',
                'constraint'     => '128',
                'null'           => false,
            ],
            'expiration'          => [
                'type'           => 'INT',
                'constraint'     => 11,
                'null'           => false,
            ],
        ]);
        $this->forge->addPrimaryKey(['tokenid', 'subject']);
        $this->forge->createTable('tokens');
    }

    public function down()
    {
        $this->forge->dropTable('tokens');
    }
}
