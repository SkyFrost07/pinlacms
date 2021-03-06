<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Admin\Facades\AdConst;

class Comment extends BaseModel {

    protected $table = 'comments';
    protected $fillable = ['post_id', 'author_email', 'author_name', 'author_id', 'author_ip', 'content', 'status', 'agent', 'parent_id'];
    protected $capCreate = 'publish_comment';
    protected $capEdit = 'edit_comment';
    protected $capRemove = 'remove_comment';

    use SoftDeletes;
    
    public static function isUseSoftDelete() {
        return true;
    }
    
    public  function post() {
        return $this->belongsTo('\App\Models\PostType', 'post_id', 'id');
    }
    
    public function getPost($lang=null){
        $lang = $lang ? $lang : currentLocale();
        return $this->post()
                ->join('post_desc as pd', 'posts.id', '=', 'pd.post_id')
                ->where('pd.lang_code', '=', $lang)
                ->select('pd.title', 'pd.slug', 'pd.post_id');
    }
    
    public function str_status(){
        if($this->status == 1){
            return trans('manage.enable');
        }
        return trans('manage.disable');
    }
    
    public function author()
    {
        return $this->belongsTo('\App\User', 'author_id');
    }
    
    public function childs()
    {
        return $this->hasMany('\App\Models\Comment', 'parent_id');
    }
    
    public function parentItem()
    {
        return $this->belongsTo('\App\Models\Comment', 'parent_id');
    }
    
    public static function rules($update = false) {
        return [
            'content' => 'required',
            'post_id' => 'required'
        ];
    }

    public static function getData($args = []) {
        $opts = [
            'fields' => ['*'],
            'status' => [AdConst::STT_PUBLISH],
            'orderby' => 'created_at',
            'order' => 'asc',
            'per_page' => AdConst::PER_PAGE,
            'exclude_key' => 'id',
            'exclude' => [],
            'filters' => [],
            'post_id' => null,
            'author_id' => null
        ];
        $opts = array_merge($opts, $args);
        
        $result = self::select($opts['fields'])
                ->with(['author'])
                ->withCount('childs');
        
        if ($opts['status']) {
            if (!is_array($opts['status'])) {
                $opts['status'] = [$opts['status']];
            }
            if ($opts['status'][0] == AdConst::STT_TRASH) {
                $result->onlyTrashed();
            } else {
                $result->whereIn('status', $opts['status']);
            }
        }
        
        if ($opts['post_id']) {
            $result->where('post_id', $opts['post_id']);
        }
        
        if (isset($opts['parent_id'])) {
            if ($opts['parent_id']) {
                $result->where('parent_id', $opts['parent_id']);
            } else {
                $result->whereNull('parent_id');
            }
        }

        if ($opts['author_id']) {
            $result->where('author_id', $opts['author_id']);
        }
        
        if ($opts['exclude']) {
            $result->whereNotIn($opts['exclude_key'], $opts['exclude']);
        }
        
        if ($opts['filters']) {
            self::filterData($result, $opts['filters']);
        }
        
        $result->orderBy($opts['orderby'], $opts['order']);

        if ($opts['per_page'] > -1) {
            return $result->paginate($opts['per_page']);
        }
        return $result->get();
    }

    public static function insertData($data) {
        self::validator($data, self::rules());
        
        if (isset($data['author_id'])) {
            $author = User::find($data['author_id']);
            if($author){
                $data['author_email'] = $author->email;
                $data['author_name'] = $author->name;
            }
        }else{
            $user = auth()->user();
            $data['author_id'] = $user->id;
            $data['author_email'] = $user->email;
            $data['author_name'] = $user->name;
        }
        if(!isset($data['status'])){
            $data['status'] = 1;
        }
        $data['agent'] = request()->header('User-Agent');
        $data['author_ip'] = request()->ip();
        if (!isset($data['parent_id']) || !$data['parent_id']) {
            $data['parent_id'] = null;
        }
        $item = self::create($data);
        $item->post()->increment('comment_count');
        return $item;
    }

    public static function updateData($id, $data) {
        self::validator($data, self::rules($id));

        if (isset($data['time'])) {
            $time = $data['time'];
            $date = date('Y-m-d', strtotime($time['year'] . '-' . $time['month'] . '-' . $time['day']));
            $data['created_at'] = $date;
        }
        if (isset($data['author_id'])) {
            $author = User::find($data['author_id']);
            if($author){
                $data['author_email'] = $author->email;
                $data['author_name'] = $author->name;
            }
        }
        if (!isset($data['parent_id']) || !$data['parent_id']) {
            $data['parent_id'] = null;
        }
        $item = self::findOrFail($id);
        $fillable = $item->getFillable();
        $data = array_only($data, $fillable);
        return $item->update($data);
    }
    
    public static function forceDeleteData($ids) {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $items = self::withTrashed()
                ->whereIn('id', $ids)->get();
        if (!$items->isEmpty()) {
            foreach ($items as $item) {
                $item->post()->decrement('comment_count');
                $item->forceDelete();
            }
        }
    }
}
