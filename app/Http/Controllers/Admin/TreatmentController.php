<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentSpa;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TreatmentRequest;
use App\Models\Treatment;
use App\Modules\Catalog\Actions\SaveTreatment;
use App\Modules\Catalog\Enums\TreatmentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TreatmentController extends Controller
{
    use ResolvesCurrentSpa;

    public function index(Request $request): View
    {
        $spa = $this->currentSpa($request);

        $treatments = $spa->treatments()->orderBy('category')->orderBy('sort_order')->get();

        return view('admin.treatments.index', compact('spa', 'treatments'));
    }

    public function create(Request $request): View
    {
        $spa = $this->currentSpa($request);

        return view('admin.treatments.form', [
            'spa' => $spa,
            'treatment' => new Treatment(['category' => Treatment::CATEGORY_MASSAGE, 'is_active' => true]),
            'categories' => TreatmentCategory::values(),
        ]);
    }

    public function store(TreatmentRequest $request, SaveTreatment $save): RedirectResponse
    {
        $treatment = $save->handle($this->currentSpa($request), $request->treatmentAttributes());

        return redirect()->route('admin.treatments.index')->with('status', "Catalog item \"{$treatment->name}\" created.");
    }

    public function edit(Request $request, Treatment $treatment): View
    {
        $spa = $this->currentSpa($request);
        Gate::authorize('update', $treatment);

        return view('admin.treatments.form', [
            'spa' => $spa,
            'treatment' => $treatment,
            'categories' => TreatmentCategory::values(),
        ]);
    }

    public function update(TreatmentRequest $request, Treatment $treatment, SaveTreatment $save): RedirectResponse
    {
        $save->handle($this->currentSpa($request), $request->treatmentAttributes(), $treatment);

        return redirect()->route('admin.treatments.index')->with('status', "Catalog item \"{$treatment->name}\" updated.");
    }

    public function destroy(Request $request, Treatment $treatment): RedirectResponse
    {
        $this->currentSpa($request);
        Gate::authorize('delete', $treatment);

        $treatment->delete();

        return redirect()->route('admin.treatments.index')->with('status', 'Catalog item deleted.');
    }
}
