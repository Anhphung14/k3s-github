<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $paginate = $request->query('paginate', 10);
        $author = $request->query('author',[]);
        $categories = $request->query('categories', []);
        $status = $request->query('status', []);
        $created_from = $request->query('created_from');
        $created_to = $request->query('created_to');
        
        $query = Post::with('categories', 'tags', 'author');

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($author) {
            $query->where('author_id', $author);
        }

        if (!empty($categories)) {
            $query->whereHas('categories', function ($q) use ($categories) {
                $q->whereIn('categories.id', (array)$categories);
            });
        }

        if (!empty($status)) {
            $query->whereIn('status', (array)$status);
        }
        
        if ($created_from && $created_to && $created_from === $created_to) {
            $query->whereDate('created_at', $created_from);
        } else {
            if ($created_from) {
                $query->whereDate('created_at', '>=', $created_from);
            }
            if ($created_to) {
                $query->whereDate('created_at', '<=', $created_to);
            }
        }


        $posts = $query->paginate($paginate)->appends($request->query());

        $posts->getCollection()->transform(function ($post) {
            return [
                ...$post->toArray(),
                'categories' => $post->categories->map(fn($c) => $c->name)->join(', '),
                'tags' => $post->tags->map(fn($t) => [
                    'name' => $t->name,
                    'color' => $t->color,
                ])->toArray(),
                'author' => $post->author ? $post->author->name : null,
                'created_at' => $post->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $post->updated_at->format('Y-m-d H:i:s'),
                ];
        });

        return Inertia::render('posts/Index', [
            'postData' => $posts,
            'filters' => [
                'search' => $search,
                'author' => $author,
                'categories' => $categories,
                'status' => $status,
                'created_from' => $created_from,
                'created_to' => $created_to,
            ],
            'authors' => \App\Models\User::select('id', 'name')->get(),
            'categories' => Category::select('id', 'name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('posts/Form', [
            'post_detail' => null,
            'categories' => Category::all(),
            'tags' => Tag::all(),
            'authors' => User::select('id', 'name')->get(),
            'author' => auth()->user() ? auth()->user()->name : '',
        ]);
    }

    public function edit($id)
    {
        $post = Post::with('tags', 'categories')->findOrFail($id);
        return Inertia::render('posts/Form', [
            'post_detail' => $post,
            'categories' => Category::all(),
            'tags' => Tag::all(),
            'authors' => User::select('id', 'name')->get(),
            'author' => $post->author ? $post->author->name : '',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'content' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'categories' => 'array',
            'categories.*' => 'integer|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'integer|exists:tags,id',
            'thumbnail' => 'nullable|string|max:255',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = \Str::slug($data['title']);
        }

        $data['author_id'] = auth()->id();

        unset($data['category_id']);

        if ($request->hasFile('image')) {
            $files = $request->file('image');
            if (is_array($files)) {
                $file = $files[0];
            } else {
                $file = $files;
            }
            $data['thumbnail'] = $file->store('thumbnails', 'public');
        } elseif ($request->filled('thumbnail')) {
            $data['thumbnail'] = $request->input('thumbnail');
        }

        $post = Post::create($data);
        if (!empty($data['categories'])) {
            $post->categories()->sync($data['categories']);
        }
        if (!empty($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }
        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $id,
            'content' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'categories' => 'array',
            'categories.*' => 'integer|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'integer|exists:tags,id',
            'thumbnail' => 'nullable|string|max:255',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = \Str::slug($data['title']);
        }

        unset($data['category_id']);

        if ($request->hasFile('image')) {
            $files = $request->file('image');
            if (is_array($files)) {
                $file = $files[0];
            } else {
                $file = $files;
            }
            $data['thumbnail'] = $file->store('thumbnails', 'public');
        } elseif ($request->filled('thumbnail')) {
            $data['thumbnail'] = $request->input('thumbnail');
        }

        $post->update($data);
        if (isset($data['categories'])) {
            $post->categories()->sync($data['categories']);
        }
        if (isset($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }
        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy($id)
    {
        Post::findOrFail($id)->delete();
        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }

    public function show($slug)
    {
        $post = Post::with('category', 'author')->where('slug', $slug)->firstOrFail();
        return Inertia::render('posts/Show', [
            'post' => [
                ...$post->toArray(),
                'category' => $post->category ? $post->category->name : null,
                'author' => $post->author ? $post->author->name : null,
                'created_at' => $post->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $post->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:posts,id',
            'status' => 'required|in:draft,published',
        ]);
        Post::whereIn('id', $data['ids'])->update(['status' => $data['status']]);
        return redirect()->route('posts.index')->with('success', 'Status updated for selected posts.');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:posts,id',
        ]);
        Post::whereIn('id', $data['ids'])->delete();
        return redirect()->route('posts.index')->with('success', 'Selected posts deleted.');
    }
}
