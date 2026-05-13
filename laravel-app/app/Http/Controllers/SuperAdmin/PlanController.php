<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PpPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    /**
     * Display a listing of plans
     */
    public function index()
    {
        $plans = PpPlan::orderBy('price', 'asc')->get();
        return view('superadmin.pages.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan
     */
    public function create()
    {
        return view('superadmin.pages.plans.create');
    }

    /**
     * Store a newly created plan
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pp_plans,slug',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:month,year,lifetime',
            'currency' => 'required|string|max:3',
            'is_active' => 'boolean',
        ]);

        $features = $request->input('features', []);
        
        // Convert checkbox strings to booleans where appropriate
        foreach ($features as $key => $value) {
            if ($value === 'on') {
                $features[$key] = true;
            }
        }

        PpPlan::create([
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'description' => $request->description,
            'price' => $request->price,
            'currency' => $request->currency,
            'interval' => $request->interval,
            'features' => $features,
            'is_active' => $request->has('is_active'),
            'is_default' => $request->has('is_default'),
        ]);

        return redirect()->route('superadmin.plans.index')->with('success', 'Plan created successfully');
    }

    /**
     * Show the form for editing the specified plan
     */
    public function edit($id)
    {
        $plan = PpPlan::findOrFail($id);
        return view('superadmin.pages.plans.edit', compact('plan'));
    }

    /**
     * Update the specified plan
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pp_plans,slug,' . $id,
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:month,year,lifetime',
            'currency' => 'required|string|max:3',
        ]);

        $plan = PpPlan::findOrFail($id);
        
        $features = $request->input('features', []);
        foreach ($features as $key => $value) {
            if ($value === 'on') {
                $features[$key] = true;
            }
        }

        $plan->update([
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'description' => $request->description,
            'price' => $request->price,
            'currency' => $request->currency,
            'interval' => $request->interval,
            'features' => $features,
            'is_active' => $request->has('is_active'),
            'is_default' => $request->has('is_default'),
        ]);

        return redirect()->route('superadmin.plans.index')->with('success', 'Plan updated successfully');
    }

    /**
     * Remove the specified plan
     */
    public function destroy($id)
    {
        $plan = PpPlan::findOrFail($id);
        
        if ($plan->is_default) {
            return redirect()->back()->with('error', 'Cannot delete the default plan');
        }

        $plan->delete();

        return redirect()->route('superadmin.plans.index')->with('success', 'Plan deleted successfully');
    }
}
