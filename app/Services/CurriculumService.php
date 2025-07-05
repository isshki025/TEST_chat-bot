<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CurriculumService
{
    protected $curriculumPath;
    protected $curriculumData;

    public function __construct()
    {
        $this->curriculumPath = storage_path('curriculum');
        $this->loadCurriculumData();
    }

    public function loadCurriculumData(): void
    {
        try {
            $this->curriculumData = [];
            
            if (!File::exists($this->curriculumPath)) {
                Log::warning('Curriculum directory does not exist: ' . $this->curriculumPath);
                return;
            }

            $files = File::files($this->curriculumPath);
            
            foreach ($files as $file) {
                if ($file->getExtension() === 'txt') {
                    $unitName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                    $content = File::get($file->getPathname());
                    
                    $this->curriculumData[$unitName] = [
                        'name' => $unitName,
                        'content' => $content,
                        'path' => $file->getPathname(),
                        'size' => $file->getSize(),
                        'modified' => $file->getMTime()
                    ];
                }
            }
            
            Log::info('Loaded ' . count($this->curriculumData) . ' curriculum units');
        } catch (\Exception $e) {
            Log::error('Failed to load curriculum data: ' . $e->getMessage());
            $this->curriculumData = [];
        }
    }

    public function searchRelevantContent(string $question): array
    {
        $results = [];
        $questionLower = mb_strtolower($question);
        $keywords = $this->extractKeywords($questionLower);
        
        foreach ($this->curriculumData as $unit) {
            $score = $this->calculateRelevanceScore($unit['content'], $keywords);
            
            if ($score > 0) {
                $results[] = [
                    'unit' => $unit['name'],
                    'content' => $unit['content'],
                    'score' => $score,
                    'excerpt' => $this->createExcerpt($unit['content'], $keywords)
                ];
            }
        }
        
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($results, 0, 3);
    }

    public function getAllUnits(): array
    {
        return array_keys($this->curriculumData);
    }

    public function getUnitContent(string $unitName): ?string
    {
        return $this->curriculumData[$unitName]['content'] ?? null;
    }

    private function extractKeywords(string $text): array
    {
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $stopWords = [
            'は', 'が', 'を', 'に', 'で', 'と', 'の', 'や', 'から', 'まで', 
            'について', 'として', 'という', 'である', 'です', 'ます', 'した',
            'how', 'what', 'when', 'where', 'why', 'who', 'the', 'is', 'are', 'was', 'were'
        ];
        
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return !in_array(mb_strtolower($word), $stopWords) && mb_strlen($word) > 1;
        });
        
        return array_values($keywords);
    }

    private function calculateRelevanceScore(string $content, array $keywords): float
    {
        $contentLower = mb_strtolower($content);
        $score = 0;
        
        foreach ($keywords as $keyword) {
            $keywordLower = mb_strtolower($keyword);
            $count = substr_count($contentLower, $keywordLower);
            
            if ($count > 0) {
                $score += $count * (mb_strlen($keyword) / 10);
            }
        }
        
        return $score;
    }

    private function createExcerpt(string $content, array $keywords): string
    {
        $lines = explode("\n", $content);
        $relevantLines = [];
        
        foreach ($lines as $line) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($line, $keyword) !== false) {
                    $relevantLines[] = trim($line);
                    break;
                }
            }
        }
        
        $excerpt = implode("\n", array_slice($relevantLines, 0, 5));
        
        return mb_strlen($excerpt) > 300 ? mb_substr($excerpt, 0, 300) . '...' : $excerpt;
    }

    public function refreshCurriculumData(): void
    {
        $this->loadCurriculumData();
    }

    public function getCurriculumStats(): array
    {
        return [
            'total_units' => count($this->curriculumData),
            'total_size' => array_sum(array_column($this->curriculumData, 'size')),
            'units' => array_map(function($unit) {
                return [
                    'name' => $unit['name'],
                    'size' => $unit['size'],
                    'modified' => date('Y-m-d H:i:s', $unit['modified'])
                ];
            }, $this->curriculumData)
        ];
    }
}