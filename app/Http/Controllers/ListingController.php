<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    // Show all listings
    public function index()
    {
        return view(
            'listings.index',
            [
                'listings' => Listing::latest()
                    ->filter(request(['tags', 'search']))
                    ->paginate(6)
            ]
        );
    }

    // Show single listing
    public function show(Listing $listing)
    {
        return view('listings.show', ['listing' => $listing]);
    }

    // Show create form
    public function create()
    {
        return view('listings.create');
    }

    // store new listing
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required',
        ]);

        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $formFields['user_id'] = auth()->id();

        Listing::create($formFields);

        return redirect('/')->with('message', 'Listing created successfully ');
    }

    // Show edit listing form
    public function edit(Listing $listing)
    {
        if (!$listing) {
            abort(404);
        }
        return view('listings/edit', ['listing' => $listing]);
    }

    // Update listing on form submit
    public function update(Request $request, Listing $listing)
    {
        if (!$listing) {
            return abort(404);
        }

        // Make sure logged in user is owner of post
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized action');
        }

        $formFields = $request->validate([
            'title' => 'required',
            'company' => 'required',
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required',
        ]);

        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $listing->update($formFields);

        return back()->with('message', 'Listing updated successfully');
    }

    // Delete a listing
    public function destroy(Listing $listing)
    {
        if (!$listing) {
            return abort(404);
        }

        // Make sure logged in user is owner of post
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized action');
        }

        $listing->delete();

        return redirect('/')->with('message', 'Listing deleted successfully');
    }

    // Manage Listings
    public function manage()
    {
        return view('listings/manage', ['listings' => auth()->user()->listings()->get()]);
    }

}
