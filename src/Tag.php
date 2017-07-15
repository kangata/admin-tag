<?php

namespace QuetzalArc\Admin\Tag;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public static function nestedOption($selectedParent = null, $parentId = null, $level = 0)
    {
        $tags = Tag::select('id', 'name')
            ->where('parent_id', $parentId)
            ->orderBy('name', 'asc')
            ->get();

        $el = '';

        $index = str_repeat('**', $level);

        foreach ($tags as $tag) {
            if ($tag->id == $selectedParent) {
                $el .= '<option value="'.$tag->id.'" selected>'.$index.' '.$tag->name.'</option>';
            } else {
                $el .= '<option value="'.$tag->id.'">'.$index.' '.$tag->name.'</option>';
            }

            if ($tag->has('children')) {
                $el .= self::nestedOption($selectedParent, $tag->id, $level + 1);
            }
        }

        return $el;
    }

    public static function nestedCheckbox($parentId = null, $lists = null)
    {
        $tags = Tag::select('id', 'name')
            ->where('parent_id', $parentId)
            ->orderBy('name', 'asc')
            ->get();

        $el = '';

        foreach ($tags as $tag) {
            $filtered = collect($lists)->filter(function ($value, $key) use ($tag) {
                return $value->id == $tag->id;
            })->count();

            $checked = ($filtered == 0 )? '' : 'checked';

            $el .= '<li>';
            
            $el .= '<label><input class="uk-checkbox" type="checkbox" name="tags[]" value="'.$tag->id.'" '.$checked.'> '.$tag->name.'</label>';

            if ($tag->has('children')){
                $el .= '<ul>';
                    $el .= $tag->nestedCheckbox($tag->id, $lists);
                $el .= '</ul>';
            }

            $el .= '</li>';
        }

        return $el;
    }

    public function isChecked($lists = null)
    {
        if (is_null($lists)) return false;

        if (in_array($this->id, $lists)) return true;

        return false;
    }

    public function children()
    {
        return $this->hasMany('QuetzalArc\Admin\Tag\Tag', 'parent_id', 'id');
    }
}
