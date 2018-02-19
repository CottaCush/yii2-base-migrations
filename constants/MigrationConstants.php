<?php

namespace dbmigrations\constants;

/**
 * Interface MigrationInterface
 * Holds table names for migrations
 * @package app\interfaces
 * @author Adeyemi Olaoye <yemi@cottacush.com>
 */
interface MigrationConstants
{
    const MYSQL_TABLE_OPTIONS = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

    const TABLE_STATUSES = 'statuses';

    const TABLE_ROLES = 'roles';
    const TABLE_PERMISSIONS = 'permissions';
    const TABLE_ROLES_PERMISSIONS = 'role_permissions';

    const TABLE_ADMINS = 'admins';
    const TABLE_USER_CREDENTIALS = 'user_credentials';
    const TABLE_USER_LOGIN_HISTORY = 'user_login_history';
    const TABLE_USER_TYPES = 'user_types';

    const VALUE_ACTIVE = 'active';
    const VALUE_INACTIVE = 'inactive';
}
