<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentSpa;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KnowledgeItemRequest;
use App\Models\SpaKnowledgeItem;
use App\Modules\Knowledge\Enums\KnowledgeCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class KnowledgeItemController extends Controller
{
    use ResolvesCurrentSpa;

    public function index(Request $request): View
    {
        $spa = $this->currentSpa($request);

        $items = $spa->knowledgeItems()->orderBy('category')->orderBy('sort_order')->get();

        return view('admin.knowledge-items.index', compact('spa', 'items'));
    }

    public function create(Request $request): View
    {
        $spa = $this->currentSpa($request);

        return view('admin.knowledge-items.form', [
            'spa' => $spa,
            'item' => new SpaKnowledgeItem(['category' => SpaKnowledgeItem::CATEGORY_GENERAL, 'is_active' => true]),
            'categories' => KnowledgeCategory::values(),
        ]);
    }

    public function store(KnowledgeItemRequest $request): RedirectResponse
    {
        $item = $this->currentSpa($request)->knowledgeItems()->create($request->knowledgeItemAttributes());

        return redirect()->route('admin.knowledge-items.index')->with('status', "Knowledge item \"{$item->title}\" created.");
    }

    public function edit(Request $request, SpaKnowledgeItem $knowledgeItem): View
    {
        $spa = $this->currentSpa($request);
        Gate::authorize('update', $knowledgeItem);

        return view('admin.knowledge-items.form', [
            'spa' => $spa,
            'item' => $knowledgeItem,
            'categories' => KnowledgeCategory::values(),
        ]);
    }

    public function update(KnowledgeItemRequest $request, SpaKnowledgeItem $knowledgeItem): RedirectResponse
    {
        $this->currentSpa($request);

        $knowledgeItem->update($request->knowledgeItemAttributes());

        return redirect()->route('admin.knowledge-items.index')->with('status', "Knowledge item \"{$knowledgeItem->title}\" updated.");
    }

    public function destroy(Request $request, SpaKnowledgeItem $knowledgeItem): RedirectResponse
    {
        $this->currentSpa($request);
        Gate::authorize('delete', $knowledgeItem);

        $knowledgeItem->delete();

        return redirect()->route('admin.knowledge-items.index')->with('status', 'Knowledge item deleted.');
    }
}
