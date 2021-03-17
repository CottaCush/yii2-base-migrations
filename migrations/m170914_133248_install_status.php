<?php

use dbmigrations\constants\MigrationConstants;
use dbmigrations\libs\BaseMigration;

/**
 * Class m170914_133248_install_status
 */
class m170914_133248_install_status extends BaseMigration
{
    public function up(): void
    {
        $this->createTable(MigrationConstants::TABLE_STATUSES, [
            'id' => $this->smallInteger()->unsigned()->notNull() . ' AUTO_INCREMENT PRIMARY KEY',
            'key' => $this->string(100)->notNull()->unique(),
            'label' => $this->string(100)->notNull(),
        ]);

        $this->batchInsert(
            MigrationConstants::TABLE_STATUSES,
            ['key', 'label'],
            [
                ['active', 'Active'],
                ['inactive', 'Inactive']
            ]
        );
    }

    public function down(): void
    {
        $this->dropTable(MigrationConstants::TABLE_STATUSES);
    }
}
