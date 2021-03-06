# Creating a Model

Create the file in `Server/app/Models`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExampleModel extends Model
{
    protected $fillable = ["uid"];

    protected $hidden = ["id", "created_at", "updated_at"];
}
```