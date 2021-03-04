# Queueing Jobs

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\ExampleJob;

Queue::push(new ExampleJob());
```

For additional information about the Queue read the [Laravel Queue documentation](https://laravel.com/docs/8.x/queues).