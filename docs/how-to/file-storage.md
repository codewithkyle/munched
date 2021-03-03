# File Storage

## Namespace

```php
use App\Facades\File;
```

## Create or Update

```php
$key = "my-unique-file-key";
$path = "/uploads/uploaded-file.pdf";
File::Put($key, $path);
```

## Get

```php
$key = "my-unique-file-key";
$result = File::Get($key);
```

## Delete

```php
$key = "my-unique-file-key";
$result = File::Delete($key);
```