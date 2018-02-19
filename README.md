Yii2 Base Database Migrations
=========================
  
  
### Install

Via Composer

```json
    "require": {
        "fbn-db-migrations": "dev-master"
    },
```

### Running Migrations
Run `DB_USERNAME=<user> DB_PASSWORD=<password> DB_NAME=<testdb_name> DB_HOST=localhost ant migrations`

### Updating Migrations
Run `DB_USERNAME=<user> DB_PASSWORD=<password> DB_NAME=<testdb_name> DB_HOST=localhost ant update-migrations`


### Writing new migrations
Run `composer install`

Run `DB_USERNAME=<user> DB_PASSWORD=<password> DB_NAME=<testdb_name> DB_HOST=localhost ant migrations`

Run `./yii migrate/create <migration_name>`
