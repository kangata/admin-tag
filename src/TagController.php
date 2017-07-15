<?php

namespace QuetzalArc\Admin\Tag;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use QuetzalArc\Admin\Tag\Tag;

class TagController extends Controller
{
    protected $request;

    protected $search = [
        'query' => '',
        'sort' => ['name', 'asc']
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        if (!is_null($this->request->input('query'))){
            $this->search = array_set($this->search, 'query', $this->request->input('query'));
        }

        if (!is_null($this->request->input('sort'))) {
            $this->search = array_set($this->search, 'sort', $this->request->input('sort'));
        }

        $tags = Tag::where(function ($query) {
            return $query->where('name', 'like', '%'.$this->search['query'].'%')
                ->orwhere('description', 'like', '%'.$this->search['query'].'%');
        })
        ->orderBy($this->search['sort'][0], $this->search['sort'][1])
        ->paginate(25);

        $tagsList = Tag::select('id', 'name')
            ->where('parent_id', null)
            ->orderBy('name', 'asc')
            ->get();

        $tags->appends($this->search);

        return view('admin-tag::tag', compact(
            'tags', 'tagsList'
        ));
    }

    public function store()
    {
        $rules = [
            'name' => 'required|max:255'
        ];

        $this->validate($this->request, $rules);

        $tag = new Tag;
        $tag->name = $this->request->name;
        $tag->slug = (empty($this->request->slug)) ? str_slug($this->request->name) : $this->request->slug;
        $tag->parent_id = ($this->request->parent == 0) ? null : $this->request->parent;
        $tag->description = (empty($this->request->description)) ? null : $this->request->description;
        $tag->save();

        session()->flash('success', 'New Tag saved.');

        return redirect('/admin/tags');
    }

    public function edit($id)
    {
        $tag = Tag::find($id);

        if (is_null($tag)) {
            session()->flash('error', 'Tag not found.');

            return redirect('/admin/tags');
        }

        $tagsList = Tag::select('id', 'name')
            ->where('id', '!=', $id)
            ->where('parent_id', null)
            ->orderBy('name', 'asc')
            ->get();

        return view('admin-tag::tag_edit', compact(
            'tag', 'tagsList'
        ));
    }

    public function update($id)
    {
        $rules = [
            'name' => 'required|max:255'
        ];

        $this->validate($this->request, $rules);

        $tag = Tag::find($id);
        
        if (is_null($tag)) {
            session()->flash('error', 'Tag not found.');

            return redirect('/admin/tags');
        }

        $tag->name = $this->request->name;
        $tag->slug = $this->request->slug;
        $tag->parent_id = ($this->request->parent == 0) ? null : $this->request->parent;
        $tag->description = $this->request->description;
        $tag->save();

        session()->flash('success', 'Tag updated.');

        return redirect('/admin/tags');
    }

    public function delete($id)
    {
        $tag = Tag::find($id);

        if (is_null($tag)) {
            session()->flash('error', 'Tag not found.');

            return redirect('/admin/tags');
        }

        $tag->children()->update([
            'parent_id' => null
        ]);

        $tag->delete();

        session()->flash('success', 'Tag deleted.');

        return redirect('/admin/tags');
    }
}
