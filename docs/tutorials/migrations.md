# Database Migrations

For additional information about database migrations read the [Laravel migration documentation](https://laravel.com/docs/8.x/migrations).
## Creating a Table

Run the following command:

```bash
# Replace 'examples' with your desired table name
php ./Server/artisan make:migration create_examples_table
```

Make sure the generated file includes the following table columns:

```php
$table->id()->autoIncrement();
$table->uuid("uid");
$table->timestamps();
```
