<?php

use dbmigrations\libs\BaseMigration;

class m170914_133248_install_status extends BaseMigration
{
    public function up()
    {
        $this->createTable(self::TABLE_STATUSES, [
            'id' => $this->smallInteger()->unsigned()->notNull() . ' AUTO_INCREMENT PRIMARY KEY',
            'key' => $this->string(100)->notNull()->unique(),
            'label' => $this->string(100)->notNull(),
        ]);

        $this->batchInsert(
            self::TABLE_STATUSES,
            ['key', 'label'],
            [
                ['active', 'Active'],
                ['inactive', 'Inactive']
            ]
        );
    }

    public function down()
    {
        $this->dropTable(self::TABLE_STATUSES);
    }
}
