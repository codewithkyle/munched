# Ingesting Data

Ingest endpoints are the main way we feed data into the clients application. Data can be served as `application/json` and `application/x-ndjson` content types. In this example we will be adding a new endpoint to the `Server/app/Http/Controllers/IngestController.php` file.

```php
public function getExamples(Request $request)
{
    // ...
}
```

The first step is to determine what type of data we need to respond with.

```php
public function getExamples(Request $request)
{
    try {
        $accepts = $this->validateAcceptHeader($request, ["application/x-ndjson", "application/json"]);
    } catch (Exception $e) {
        return response($e->getMessage(), $e->statusCode);
    }
}
```

Once we have the requested content type we can respond with the data.

```php
public function getExamples(Request $request)
{
    // ...snip...

    switch ($accepts) {
        case "application/x-ndjson":
            $path = storage_path("ndjson/users.ndjson");
            $etag = $this->generateEtag($path);
            return response(file_get_contents($path))->header("ETag", $etag);
        case "application/json":
            $ingestService = new IngestService();
            $data = $ingestService->getAllExamples();
            return $this->buildSuccessResponse($data);
    }
}
```

Now let's add our `getAllExamples()` function to the `Server/app/Services/IngestService.php` file.

```php
use App\Models\Example;

public function getAllExamples(): array
{
    $output = [];
    $examples = Example::get();
    foreach ($examples as $example) {
        $output[] = [
            "Title" => $example->title,
        ];
    }
    return $output;
}
```

For additional information about streaming NDJSON files read the [Streams—The definitive guide](https://web.dev/streams/) article by Thomas Steiner.