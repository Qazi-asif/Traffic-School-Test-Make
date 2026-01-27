<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

class BookletTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'content',
        'css',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function render(array $data): string
    {
        try {
            // Use view()->make() instead of eval for proper Blade rendering
            $tempView = 'booklet-temp-'.uniqid();
            $viewPath = resource_path('views/booklet-templates');

            // Create temp directory if it doesn't exist
            if (! file_exists($viewPath)) {
                mkdir($viewPath, 0755, true);
            }

            // Write template to temp file
            $tempFile = $viewPath.'/'.$tempView.'.blade.php';
            file_put_contents($tempFile, $this->content);

            // Render using Laravel's view system
            $html = view('booklet-templates.'.$tempView, $data)->render();

            // Clean up temp file
            @unlink($tempFile);

            // Add CSS if present
            if ($this->css) {
                $html = "<style>{$this->css}</style>".$html;
            }

            return $html;
        } catch (\Exception $e) {
            \Log::error('Booklet template render error: '.$e->getMessage());

            return '<p>Error rendering template</p>';
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getAvailableVariables(): array
    {
        return $this->variables ?? [];
    }
}
