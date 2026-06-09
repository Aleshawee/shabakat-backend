<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizContest;
use App\Models\QuizQuestion;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class QuizContestController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();
        $query = QuizContest::withCount('questions');

        if ($request->search) $query->where('title', 'like', "%{$request->search}%");
        if ($request->status) $query->where('status', $request->status);

        $perPage = min((int) $request->per_page, 100) ?: 50;
        return response()->json($query->latest()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $admin = $request->user();
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'entry_fee' => 'required|integer|min:0',
            'prize' => 'nullable|string|max:255',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'status' => 'required|in:draft,open,closed',
        ]);

        $contest = QuizContest::create($data);

        ActivityLog::create([
            'admin_id' => $admin->id,
            'action' => 'create',
            'target_type' => 'quiz_contest',
            'target_id' => $contest->id,
            'details' => "إضافة مسابقة: {$contest->title}",
        ]);

        return response()->json($contest, 201);
    }

    public function show($id)
    {
        $contest = QuizContest::with('questions')->findOrFail($id);
        return response()->json($contest);
    }

    public function update(Request $request, $id)
    {
        $contest = QuizContest::findOrFail($id);

        $data = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'entry_fee' => 'integer|min:0',
            'prize' => 'nullable|string|max:255',
            'starts_at' => 'date',
            'ends_at' => 'date|after:starts_at',
            'status' => 'in:draft,open,closed',
        ]);

        $contest->update($data);
        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'update',
            'target_type' => 'quiz_contest',
            'target_id' => $contest->id,
            'details' => "تعديل مسابقة: {$contest->title}",
        ]);

        return response()->json($contest);
    }

    public function destroy(Request $request, $id)
    {
        $contest = QuizContest::findOrFail($id);
        $contest->questions()->delete();
        $contest->delete();

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'delete',
            'target_type' => 'quiz_contest',
            'target_id' => $contest->id,
            'details' => "حذف مسابقة: {$contest->title}",
        ]);

        return response()->json(['message' => 'تم الحذف']);
    }

    // --- Questions management ---

    public function questions($contestId)
    {
        $contest = QuizContest::findOrFail($contestId);
        return response()->json($contest->questions()->orderBy('sort_order')->get());
    }

    public function storeQuestion(Request $request, $contestId)
    {
        $contest = QuizContest::findOrFail($contestId);

        $data = $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'correct_answer' => 'required|string',
            'points' => 'required|integer|min:1',
            'sort_order' => 'integer|min:0',
        ]);

        $data['contest_id'] = $contest->id;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $question = QuizQuestion::create($data);
        return response()->json($question, 201);
    }

    public function updateQuestion(Request $request, $contestId, $questionId)
    {
        $contest = QuizContest::findOrFail($contestId);

        $question = QuizQuestion::where('contest_id', $contest->id)->findOrFail($questionId);
        $data = $request->validate([
            'question' => 'string',
            'options' => 'array|min:2',
            'correct_answer' => 'string',
            'points' => 'integer|min:1',
            'sort_order' => 'integer|min:0',
        ]);

        $question->update($data);
        return response()->json($question);
    }

    public function destroyQuestion($contestId, $questionId)
    {
        $contest = QuizContest::findOrFail($contestId);

        $question = QuizQuestion::where('contest_id', $contest->id)->findOrFail($questionId);
        $question->delete();

        return response()->json(['message' => 'تم حذف السؤال']);
    }

}
