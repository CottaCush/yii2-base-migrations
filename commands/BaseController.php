<?php

namespace dbmigrations\commands;

use CottaCush\Yii2\Controller\BaseConsoleController;
use CottaCush\Yii2\Date\DateUtils;
use dbmigrations\constants\MigrationConstants;
use dbmigrations\libs\Utils;
use Faker\Factory;
use Faker\Generator;
use Yii;
use yii\base\InvalidRouteException;
use yii\base\Module;
use yii\console\Exception;
use yii\db\Query;
use yii\helpers\BaseInflector;

/**
 * Class BaseController
 * @author Adeyemi Olaoye <yemi@cottacush.com>
 * @package fbnmigrations\commands
 */
class BaseController extends BaseConsoleController
{
    const DEFAULT_NUMBER_OF_ROWS = 10;

    public Generator $faker;
    public string $now;
    public int $actorId;

    /**
     * BaseController constructor.
     * @param $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->faker = Factory::create();
        $this->now = DateUtils::getMysqlNow();
    }

    /**
     * Helps to link actions and
     * @param $table
     * @param $column
     * @param $action
     * @param array $conditions
     * @param string $returnType
     * @param null $controller
     * @return mixed
     * @throws InvalidRouteException
     * @throws Exception
     * @author Olawale Lawal <wale@cottacush.com>
     */
    public function dependsOn(
        $table,
        $column,
        $action,
        $conditions = [],
        $returnType = 'scalar',
        $controller = null
    ): mixed
    {
        $records = (new Query)->from($table)->select($column)->andFilterWhere($conditions);

        if (!$records->count()) {
            if (!$controller) {
                $this->runAction($action);
            } else {
                Yii::$app->runAction($controller . '/' . $action);
            }
        }
        return $records->$returnType();
    }

    /**
     * @author Olawale Lawal <wale@cottacush.com>
     * @param $identifier
     * @param $password
     * @param $userTypedId
     * @param string $status
     * @return string
     */
    public function addUserCredentials(
        $identifier,
        $password,
        $userTypedId,
        $status = MigrationConstants::VALUE_ACTIVE
    ): string
    {
        $this->insert(MigrationConstants::TABLE_USER_CREDENTIALS, [
            'identifier' => $identifier,
            'password' => $password,
            'status' => $status,
            'created_at' => $this->now,
            'updated_at' => $this->now,
            'user_type_id' => $userTypedId
        ]);
        return $this->db->getLastInsertID();
    }

    /**
     * @author Adeyemi Olaoye <yemi@cottacush.com>
     * @param $tableName
     * @param $rows
     * @param array $commonData
     */
    public function seedLOVs($tableName, $rows, $commonData = [])
    {
        $this->batchInsert(
            $tableName,
            array_merge(
                array_keys($rows[0]),
                ['created_at', 'created_by', 'updated_at'],
                array_keys($commonData)
            ),
            $rows,
            array_merge(
                ['created_at' => $this->now, 'created_by' => $this->actorId, 'updated_at' => $this->now],
                $commonData
            )
        );
    }

    /**
     * @param $tableName
     * @param int $noOfRecords
     * @param array $extraData
     * @author Adeyemi Olaoye <yemi@cottacush.com>
     */
    public function seedUniqueLOVs($tableName, int $noOfRecords, $extraData = [])
    {
        $names = Utils::getUniqueNames($noOfRecords, $this->faker);

        $data = [];
        for ($i = 0; $i < $noOfRecords; $i++) {
            $name = $names[$i];
            $name = $name . '-' . $i . '-' . microtime();
            $data[] = [
                'name' => $name,
                'key' => BaseInflector::slug($name),
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $this->actorId,
                'updated_by' => $this->actorId
            ];
        }
        $this->batchInsert(
            $tableName,
            ['name', 'key', 'created_at', 'updated_at', 'created_by', 'updated_by'],
            $data,
            $extraData
        );
    }

    /**
     * @author Olawale Lawal <wale@cottacush.com>
     * @param $table
     * @param array $columns
     * @param array $rows
     * @param array $commonData
     * @return int
     */
    public function batchInsert($table, array $columns, array $rows, $commonData = []): int
    {
        foreach ($rows as &$row) {
            $row = array_merge($row, $commonData);
        }

        return parent::batchInsert($table, $columns, $rows);
    }

    /**
     * @author Adeyemi Olaoye <yemi@cottacush.com>
     * @param $table
     * @param $value
     * @param string $column
     * @param array $otherConditions
     * @return false|null|string
     */
    public function getIdFromColumn($table, $value, $column = 'key', array $otherConditions = []): bool|string|null
    {
        $conditions = array_merge([$column => $value], $otherConditions);
        return (new Query)->from($table)->select('id')
            ->andFilterWhere($conditions)->scalar();
    }
}
