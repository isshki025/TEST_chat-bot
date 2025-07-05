<?php

namespace App\Http\Controllers;

use App\Services\CurriculumService;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected $curriculumService;
    protected $openAiService;

    public function __construct(CurriculumService $curriculumService, OpenAiService $openAiService)
    {
        $this->curriculumService = $curriculumService;
        $this->openAiService = $openAiService;
    }

    public function index(): View
    {
        $stats = $this->curriculumService->getCurriculumStats();
        return view('chat.index', compact('stats'));
    }

    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        try {
            $question = $request->input('question');
            
            $relevantContent = $this->curriculumService->searchRelevantContent($question);
            
            if (empty($relevantContent)) {
                return response()->json([
                    'success' => false,
                    'message' => 'お聞きの内容に関連するカリキュラムが見つかりませんでした。別の質問をお試しください。'
                ]);
            }

            $combinedContent = $this->combineRelevantContent($relevantContent);
            
            $response = $this->openAiService->generateHint($question, $combinedContent);
            
            if ($response['success']) {
                return response()->json([
                    'success' => true,
                    'answer' => $response['content'],
                    'related_units' => array_column($relevantContent, 'unit'),
                    'usage' => $response['usage'] ?? null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'AIからの回答取得に失敗しました。しばらく時間をおいて再度お試しください。'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Chat Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました。しばらく時間をおいて再度お試しください。'
            ]);
        }
    }

    public function refresh(): JsonResponse
    {
        try {
            $this->curriculumService->refreshCurriculumData();
            $stats = $this->curriculumService->getCurriculumStats();
            
            return response()->json([
                'success' => true,
                'message' => 'カリキュラムデータを更新しました。',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Refresh Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'カリキュラムデータの更新に失敗しました。'
            ]);
        }
    }

    public function units(): JsonResponse
    {
        try {
            $units = $this->curriculumService->getAllUnits();
            return response()->json([
                'success' => true,
                'units' => $units
            ]);
        } catch (\Exception $e) {
            Log::error('Units Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'カリキュラム単元の取得に失敗しました。'
            ]);
        }
    }

    public function unit(string $unitName): JsonResponse
    {
        try {
            $content = $this->curriculumService->getUnitContent($unitName);
            
            if ($content === null) {
                return response()->json([
                    'success' => false,
                    'message' => '指定された単元が見つかりませんでした。'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'unit' => $unitName,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            Log::error('Unit Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '単元内容の取得に失敗しました。'
            ]);
        }
    }

    private function combineRelevantContent(array $relevantContent): string
    {
        $combined = '';
        
        foreach ($relevantContent as $content) {
            $combined .= "【{$content['unit']}】\n";
            $combined .= $content['excerpt'] . "\n\n";
        }
        
        return $combined;
    }
}