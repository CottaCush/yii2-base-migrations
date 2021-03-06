<?php
/**
 * This view is used by console/controllers/MigrateController.php.
 *
 * The following variables are available in this view:
 */
/* @var string $className the new migration class name without namespace */
/* @var string $namespace the new migration class namespace */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use dbmigrations\constants\MigrationConstants;
use dbmigrations\libs\BaseMigration;

/**
 * Class <?= $className . "\n" ?>
 */
class <?= $className ?> extends BaseMigration
{
    /**
    * @inheritdoc
    */
    public function up()
    {

    }

    /**
    * @inheritdoc
    */
    public function down()
    {

    }
}
