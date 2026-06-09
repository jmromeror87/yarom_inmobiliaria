<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PatchJsonResponse extends Command
{
    protected $signature   = 'app:patch-json-response';
    protected $description = 'Patch vendor JsonResponse to tolerate malformed UTF-8';

    public function handle(): void
    {
        $file = base_path('vendor/laravel/framework/src/Illuminate/Http/JsonResponse.php');

        if (! file_exists($file)) {
            return;
        }

        $content = file_get_contents($file);

        if (str_contains($content, 'JSON_INVALID_UTF8_SUBSTITUTE')) {
            $this->info('JsonResponse patch already applied.');
            return;
        }

        $search  = 'default => json_encode($data, $this->encodingOptions),';
        $replace = '$opts = $this->encodingOptions | JSON_INVALID_UTF8_SUBSTITUTE;
        $this->data = match (true) {
            $data instanceof Jsonable => $data->toJson($opts),
            $data instanceof JsonSerializable => json_encode($data->jsonSerialize(), $opts),
            $data instanceof Arrayable => json_encode($data->toArray(), $opts),
            default => json_encode($data, $opts),
        };
        // patch placeholder';

        // Use sed-style approach
        $patched = str_replace(
            'default => json_encode($data, $this->encodingOptions),',
            'default => json_encode($data, $this->encodingOptions | JSON_INVALID_UTF8_SUBSTITUTE),',
            $content
        );

        file_put_contents($file, $patched);
        $this->info('JsonResponse patch applied.');
    }
}
