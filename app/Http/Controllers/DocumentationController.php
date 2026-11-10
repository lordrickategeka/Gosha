<?php

namespace App\Http\Controllers;

use App\Support\UserGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DocumentationController extends Controller
{
    public function guide(Request $request, UserGuide $guide)
    {
        $audience = is_string($request->query('audience')) ? $request->query('audience') : 'all';
        if (! array_key_exists($audience, UserGuide::AUDIENCES)) {
            $audience = 'all';
        }
        $search = is_string($request->query('q')) ? mb_substr(trim($request->query('q')), 0, 200) : '';
        $all = $guide->chapters();
        $chapters = array_values(array_filter($all, fn ($chapter) => ($audience === 'all' || in_array('all', $chapter['audiences']) || in_array($audience, $chapter['audiences']))
            && ($search === '' || mb_stripos($chapter['title']."\n".$chapter['body'], $search) !== false)));

        return view('documentation.guide', compact('chapters', 'audience', 'search') + [
            'audiences' => UserGuide::AUDIENCES, 'chapterCount' => count($all), 'title' => 'User guide | GarageHQ',
        ]);
    }

    public function download()
    {
        return response()->download(base_path('docs/USER_GUIDE.md'), 'GarageHQ-User-Guide.md', ['Content-Type' => 'text/markdown; charset=UTF-8']);
    }

    public function api()
    {
        $specs = collect(['garagehq' => 'GarageHQ application API', 'cream-theme' => 'Cream Theme partner API', 'onepass' => 'OnePass OTP API'])
            ->map(fn ($name, $service) => ['name' => $name, 'url' => route('api-docs.spec', $service, false)])->values()->all();

        return view('documentation.api', ['title' => 'API reference | GarageHQ', 'apiSpecs' => $specs]);
    }

    public function spec(string $service)
    {
        abort_unless(in_array($service, ['garagehq', 'cream-theme', 'onepass'], true), 404);
        $data = json_decode(File::get(base_path('docs/openapi/'.$service.'.json')), true, 512, JSON_THROW_ON_ERROR);

        return response()->json($data)->header('X-Content-Type-Options', 'nosniff');
    }
}
