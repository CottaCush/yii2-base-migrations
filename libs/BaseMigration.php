<?php

namespace dbmigrations\libs;

use CottaCush\Yii2\Date\DateUtils;
use dbmigrations\constants\MigrationConstants;
use Yii;
use yii\caching\TagDependency;
use yii\db\Migration;
use yii\db\Query;

class BaseMigration extends Migration
{
    const PERMISSIONS = 'permissions';

    const AUDIT_CREATOR_FIELD = 'created_by';
    const AUDIT_UPDATER_FIELD = 'updated_by';

    public function getTableEncoding(): ?string
    {
        return ($this->db->driverName === 'mysql') ? MigrationConstants::MYSQL_TABLE_OPTIONS : null;
    }

    /**
     * @param string $table
     * @param array $columns
     * @param null $options
     */
    public function createTable($table, $columns, $options = null)
    {
        $options = !is_null($options) ? $options : $this->getTableEncoding();
        parent::createTable($table, $columns, $options);
    }

    /**
     * @author Adeyemi Olaoye <yemi@cottacush.com>
     * @param $sourceTable
     * @param $sourceColumn
     * @param $refTable
     * @param $refColumn
     * @return string
     */
    public function getForeignKeyConstraintName($sourceTable, $sourceColumn, $refTable, $refColumn): string
    {
        return 'fk_' . $sourceTable . '_' . $refTable . '_' . $sourceColumn . '_' . $refColumn;
    }

    /**
     * @inheritdoc
     */
    public function addForeignKey(
        $name,
        $table,
        $columns,
        $refTable,
        $refColumns = 'id',
        $delete = null,
        $update = null
    ) {
        $refColumnsName = is_array($refColumns) ? implode('_', $refColumns) : $refColumns;
        $columnsName = is_array($columns) ? implode('_', $columns) : $columns;

        $name = $name ?: $this->getForeignKeyConstraintName($table, $columnsName, $refTable, $refColumnsName);
        return parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * @inheritdoc
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        $columnsName = is_array($columns) ? implode('_', $columns) : $name;

        $name = is_null($name) ? 'k_' . $table . $columnsName : $name;

        parent::createIndex($name, $table, $columns, $unique);
    }

    /**
     * @author Adeyemi Olaoye <yemi@cottacush.com>
     * @param $tableName
     * @param bool $addAuditFields
     * @param $auditTableName
     */
    public function createLOVTable($tableName, $addAuditFields = false, $auditTableName = null)
    {
        $this->createTable($tableName, [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(50)->unique()->notNull(),
            'key' => $this->string(50)->unique()->notNull(),
            'is_active' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        if ($addAuditFields) {
            $this->addAuditFields($tableName, $auditTableName);
        }
    }

    /**
     * @author Adeyemi Olaoye <yemi@cottacush.com>
     * @param $table
     * @param string $refTable
     * @param bool $addForeignKeys
     * @param null $column
     */
    public function addAuditFields($table, string $refTable, $addForeignKeys = true, $column = null)
    {
        $column = is_null($column) ? $this->integer()->unsigned() : $column;
        $tempColumn = clone $column;
        $this->addColumn($table, self::AUDIT_CREATOR_FIELD, $tempColumn->notNull());
        $this->addColumn($table, self::AUDIT_UPDATER_FIELD, $column);

        if ($addForeignKeys) {
            $this->addAuditColumnConstraints($table, $refTable);
        }
    }

    /**
     * @author Adeyemi Olaoye <yemi@cottacush.com>
     * @param $table
     * @param string $refTable
     */
    public function addAuditColumnConstraints($table, string $refTable)
    {
        $this->addForeignKey(null, $table, self::AUDIT_CREATOR_FIELD, $refTable);
        $this->addForeignKey(null, $table, self::AUDIT_UPDATER_FIELD, $refTable);
    }

    /**
     * @author Adeyemi Olaoye <yemi@cottacush.com>
     * @param $table
     * @param string $refTable
     */
    public function dropAuditFields($table, string $refTable)
    {
        $this->dropForeignKey(
            $this->getForeignKeyConstraintName(
                $table,
                self::AUDIT_CREATOR_FIELD,
                $refTable,
                'id'
            ),
            $table
        );
        $this->dropColumn($table, self::AUDIT_CREATOR_FIELD);

        $this->dropForeignKey(
            $this->getForeignKeyConstraintName(
                $table,
                self::AUDIT_UPDATER_FIELD,
                $refTable,
                'id'
            ),
            $table
        );
        $this->dropColumn($table, self::AUDIT_UPDATER_FIELD);
    }

    /**
     * @param $role
     * @param array|string $permissions
     * @author Olawale Lawal <wale@cottacush.com>
     */
    public function grantPermission($role, array|string $permissions)
    {
        $query = new Query();

        $roleId = $query->select('id')
            ->from(MigrationConstants::TABLE_ROLES)
            ->where(['key' => $role])->scalar();

        $permissionId = $query->select('id')
            ->from(MigrationConstants::TABLE_PERMISSIONS)
            ->where(['key' => $permissions])->column();

        $existingPermissions = $query->select('permission_id')
            ->from(MigrationConstants::TABLE_ROLES_PERMISSIONS)
            ->where(['role_id' => $roleId, 'permission_id' => $permissionId])->column();

        $changes = array_diff($permissionId, $existingPermissions);

        if (!$changes) {
            return;
        }

        $data = [];
        foreach ($changes as $permissionId) {
            $data[] = [
                'permission_id' => $permissionId,
                'role_id' => $roleId,
                'created_at' => DateUtils::getMysqlNow()
            ];
        }
        $this->batchInsert(
            MigrationConstants::TABLE_ROLES_PERMISSIONS,
            ['permission_id', 'role_id', 'created_at'],
            $data
        );

        TagDependency::invalidate(Yii::$app->cache, [self::PERMISSIONS]);
    }

    /**
     * Helps with revoking permissions
     * @param array|string $permissions
     * @author Olawale Lawal <wale@cottacush.com>
     */
    public function revokePermission(array|string $permissions)
    {
        $query = new Query();

        $permissionId = $query->select('id')
            ->from(MigrationConstants::TABLE_PERMISSIONS)
            ->where(['key' => $permissions])->column();

        $this->delete(MigrationConstants::TABLE_ROLES_PERMISSIONS, ['permission_id' => $permissionId]);
        $this->delete(MigrationConstants::TABLE_PERMISSIONS, ['key' => $permissions]);

        TagDependency::invalidate(Yii::$app->cache, [self::PERMISSIONS]);
    }
}
