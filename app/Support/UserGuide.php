<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UserGuide
{
    public const AUDIENCES = ['all' => 'Everyone', 'garage' => 'Garage owners', 'supplier' => 'Suppliers', 'staff' => 'Staff', 'admin' => 'Platform admins'];

    public function chapters(): array
    {
        $parts = preg_split('/^## /m', File::get(base_path('docs/USER_GUIDE.md')));
        array_shift($parts);

        return array_map(function ($part) {
            [$title, $body] = explode("\n", $part, 2);
            preg_match('/<!-- audience: ([a-z,]+) -->/', $body, $match);
            $body = preg_replace('/<!-- audience: [a-z,]+ -->\s*/', '', $body);

            return ['id' => Str::slug(trim($title)), 'title' => trim($title),
                'audiences' => explode(',', $match[1] ?? 'all'), 'body' => trim($body),
                'html' => Str::markdown($body, ['html_input' => 'strip', 'allow_unsafe_links' => false])];
        }, $parts);
    }
}
