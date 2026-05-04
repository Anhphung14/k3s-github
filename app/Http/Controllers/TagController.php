<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;


class TagController extends Controller
{
	public function index()
	{
		$search = request()->query('search');
		$paginate = request()->query('paginate', 10);

		$query = Tag::query();
		if ($search) {
			$query->where('name', 'like', '%' . $search . '%');
		}
		$tags = $query->paginate($paginate)->appends(request()->query());

		return Inertia::render('tags/Index', [
			'tagData' => $tags,
			'filters' => [
				'search' => $search,
			],
		]);
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'name' => 'required|string|max:255',
			'slug' => 'nullable|string|max:255|unique:tags,slug',
			'description' => 'nullable|string',
			'color' => 'nullable|string|max:32',
			'status' => 'nullable|in:active,inactive',
		]);
		if (empty($data['slug'])) {
			$data['slug'] = \Str::slug($data['name']);
		}
		Tag::create([
			'name' => $data['name'],
			'slug' => $data['slug'],
			'description' => $data['description'] ?? null,
			'color' => $data['color'] ?? null,
			'status' => $data['status'] ?? null,
		]);

		return redirect()->route('tags.index')->with('success', 'Tag created successfully.');
	}

	public function show($id)
	{
		$tag = Tag::findOrFail($id);
		return response()->json($tag);
	}

	public function create()
	{
		return Inertia::render('tags/Form', [
			'tag_detail' => null,
			'tags' => Tag::all(),
		]);
	}

	public function edit($id)
	{
		$tag = Tag::findOrFail($id);
		return Inertia::render('tags/Form', [
			'tag_detail' => $tag,
			'tags' => Tag::all(),
		]);
	}

	public function update(Request $request, $id)
	{
		$tag = Tag::findOrFail($id);

		$data = $request->validate([
			'name' => 'required|string|max:255|unique:tags,name,' . $id,
			'slug' => 'nullable|string|max:255|unique:tags,slug,' . $id,
			'description' => 'nullable|string',
			'color' => 'nullable|string|max:32',
			'status' => 'nullable|in:active,inactive',
		]);
		if (empty($data['slug'])) {
			$data['slug'] = \Str::slug($data['name']);
		}

		$tag->update([
			'name' => $data['name'],
			'slug' => $data['slug'],
			'description' => $data['description'] ?? null,
			'color' => $data['color'] ?? null,
			'status' => $data['status'] ?? null,
		]);

		return redirect()->route('tags.index')->with('success', 'Tag updated successfully.');
	}

	public function destroy($id)
	{
		$tag = Tag::findOrFail($id);
		$tag->delete();

		return response()->json(null, 204);
	}
}
