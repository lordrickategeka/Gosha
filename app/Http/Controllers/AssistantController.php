<?php

namespace App\Http\Controllers;

use App\Services\AssistantService;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function __invoke(Request $request, AssistantService $assistant)
    {
        abort_unless($request->user()->is_active, 403);
        $input = $request->validate([
            'question' => ['bail', 'required', 'string', 'max:1200', function ($attribute, $value, $fail) {
                if (trim($value) === '') {
                    $fail('Enter a question about GarageHQ.');
                }
            }],
            'consent' => ['required', 'accepted'],
        ]);
        $result = $assistant->ask($request->user(), trim($input['question']));
        $response = response()->json($result['body'], $result['status'])->header('Cache-Control', 'no-store');

        return $result['status'] === 429 ? $response->header('Retry-After', '60') : $response;
    }
}
