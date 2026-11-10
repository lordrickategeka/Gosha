<?php

namespace App\Console\Commands;

use App\Support\AssistantKnowledge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportAssistantKnowledge extends Command
{
    protected $signature = 'assistant:export-knowledge';

    protected $description = 'Export approved GarageHQ guide chapters for the shared assistant tenant';

    public function handle(AssistantKnowledge $knowledge): int
    {
        $path = storage_path('app/private/assistant-knowledge.json');
        File::ensureDirectoryExists(dirname($path));
        $guides = $knowledge->guides();
        File::put($path, json_encode(['guides' => $guides], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        chmod($path, 0600);
        $this->info(count($guides).' guide chapters exported to storage/app/private/assistant-knowledge.json.');

        return self::SUCCESS;
    }
}
