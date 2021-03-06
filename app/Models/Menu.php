<?php

namespace App\Models;

use Admin\Facades\AdConst;
use App\Models\BaseModel;
use App\Helper\CacheFunc;

class Menu extends BaseModel {

    protected $table = 'menus';
    protected $fillable = ['group_id', 'parent_id', 'menu_type', 'type_id', 'icon', 'open_type', 'order', 'status'];
    public $timestamps = false;
    
    public static function isUseSoftDelete() {
        return false;
    }

    public static function joinLang($lang = null) {
        if (!$lang) {
            $lang = currentLocale();
        }
        return self::from(self::getTableName() . ' as menus')
                ->join('menu_desc as md', 'menus.id', '=', 'md.menu_id')
                        ->where('md.lang_code', '=', $lang);
    }

    public function langs() {
        return $this->belongsToMany('\App\Models\Lang', 'menu_desc', 'menu_id', 'lang_code');
    }

    public function group() {
        return $this->belongsTo('App\Models\MenuCat', 'group_id', 'id');
    }

    public function getObject() {
        switch ($this->menu_type) {
            case AdConst::MENU_TYPE_CAT:
            case AdConst::MENU_TYPE_TAX:
            case AdConst::MENU_TYPE_ALBUM:
                $object = self::join('tax_desc as td', 'menus.type_id', '=', 'td.tax_id')
                        ->where('td.lang_code', '=', currentLocale())
                        ->where('td.tax_id', '=', $this->type_id)
                        ->first(['td.name as title', 'td.slug']);
                break;
            case AdConst::MENU_TYPE_POST:
            case AdConst::MENU_TYPE_PAGE:
                $object = self::join('post_desc as pd', 'menus.type_id', '=', 'pd.post_id')
                        ->where('pd.lang_code', '=', currentLocale())
                        ->where('pd.post_id', '=', $this->type_id)
                        ->first(['pd.title', 'pd.slug']);
                break;
            case 0:
            default:
                $object = null;
                break;
        }
        return $object;
    }

    public function getItemRoute() {
        $route = null;
        switch ($this->menu_type) {
            case AdConst::MENU_TYPE_TAX:
                $route = 'tag.view';
                break;
            case AdConst::MENU_TYPE_CAT:
                $route = 'cat.view';
                break;
            case AdConst::MENU_TYPE_POST:
                $route = 'post.view';
                break;
            case AdConst::MENU_TYPE_PAGE:
                $route = 'page.view';
                break;
            case AdConst::MENU_TYPE_ALBUM:
                $route = 'album.view';
                break;
            case 0:
            default:
                break;
        }
        return 'front::' . $route;
    }

    public function str_status() {
        if ($this->status == 1) {
            return trans('manage.active');
        }
        return trans('manage.disable');
    }

    public function str_open_type() {
        if ($this->open_type) {
            return trans('manage.newtab_tab');
        }
        return trans('manage.current_tab');
    }

    public static function getData($args = []) {
        $opts = [
            'fields' => ['menus.*', 'md.*'],
            'group_id' => -1,
            'orderby' => 'order',
            'order' => 'asc',
            'per_page' => AdConst::PER_PAGE,
            'exclude' => [],
            'lang' => currentLocale(),
            'with_target' => false,
            'filters' => []
        ];

        $opts = array_merge($opts, $args);
        $lang = $opts['lang'];
        $result = self::joinLang($lang)
                ->whereNotNull('md.title');
        if ($opts['exclude']) {
            $result->whereNotIn('menus.id', $opts['exclude']);
        }
        if ($opts['filters']) {
            self::filterData($result, $opts['filters']);
        }
        if ($opts['group_id'] > -1) {
            $result->where('group_id', $opts['group_id']);
        }
        if ($opts['with_target']) {
            //tax
            $result->leftJoin('tax_desc', function ($join) use ($lang) {
                $join->on('menus.type_id', '=', 'tax_desc.tax_id')
                        ->where('tax_desc.lang_code', '=', $lang)
                        ->whereIn('menus.menu_type', [AdConst::MENU_TYPE_CAT, AdConst::MENU_TYPE_TAX, AdConst::MENU_TYPE_ALBUM]);
            });
            //post
            $result->leftJoin('post_desc', function ($join) use ($lang) {
               $join->on('menus.type_id', '=', 'post_desc.post_id')
                       ->whereIn('menus.menu_type', [AdConst::MENU_TYPE_POST, AdConst::MENU_TYPE_PAGE])
                       ->where('post_desc.lang_code', '=', $lang);
            });
        }
        
        $result->select($opts['fields'])
                ->orderBy($opts['orderby'], $opts['order']);

        if ($opts['per_page'] > -1) {
            return $result->paginate($opts['per_page']);
        }
        return $result->get();
    }

    public static function insertData($data) {
        if (!isset($data['order'])) {
            $data['order'] = self::max('order') + 1;
        }

        $item = self::create($data);

        $link = isset($data['link']) ? $data['link'] : null;
            
        $langs = getLangs(['fields' => ['code']]);
        
        foreach ($langs as $lang) {
            if (!$link) {
                $link = self::getLinkByType($data['menu_type'], $data['id'], $lang->code);
            }
            
            $lang_data = [
                'title' => isset($data['title']) ? $data['title'] : $data['name'],
                'link' => $link
            ];
            $item->langs()->attach($lang->code, $lang_data);
        }
    }
    
    public static function getLinkByType($type, $typeId, $langCode)
    {
        $link = null;
        switch ($type) {
            case AdConst::MENU_TYPE_TAX:
                $tax = Tax::findByLang($typeId, ['td.slug', 'taxs.id'], $langCode);
                if ($tax) {
                    $link = route('front::tag.view', ['id' => $tax->id, 'slug' => $tax->slug]);
                }
                break;
            case AdConst::MENU_TYPE_CAT:
                $tax = Tax::findByLang($typeId, ['td.slug', 'taxs.id'], $langCode);
                if ($tax) {
                    $link = route('front::cat.view', ['id' => $tax->id, 'slug' => $tax->slug]);
                }
                break;
            case AdConst::MENU_TYPE_ALBUM:
                $tax = Tax::findByLang($typeId, ['td.slug', 'taxs.id'], $langCode);
                if ($tax) {
                    $link = route('front::album.view', ['id' => $tax->id, 'slug' => $tax->slug]);
                }
                break;
            case AdConst::MENU_TYPE_POST:
                $post = PostType::findByLang($typeId, ['posts.id', 'pd.slug'], $langCode);
                if ($post) {
                    $link = route('front::post.view', ['id' => $post->id, 'slug' => $post->slug]);
                }
                break;
            case AdConst::MENU_TYPE_PAGE:
                $post = PostType::findByLang($typeId, ['posts.id', 'pd.slug'], $langCode);
                if ($post) {
                    $link = route('front::page.view', ['id' => $post->id, 'slug' => $post->slug]);
                }
                break;
            case 0:
            default:
                break;
        }
        return $link;
    }

    public static function findCustom($id, $fields = ['md.*'], $lang = null) {
        return self::joinLang($lang)
                        ->findOrFail($id, $fields);
    }

    public static function updateData($id, $data) {

        $item = self::findOrFail($id);
        $fillable = $item->getFillable();
        $fill_data = array_only($data, $fillable);
        $item->update($fill_data);

        $lang_data = $data['locale'];
        $link = isset($lang_data['link']) ? $lang_data['link'] : null;
        if (!$link) {
            $link = self::getLinkByType($item->menu_type, $item->type_id, $data['lang']);
            $lang_data['link'] = $link;
        }
        $item->langs()->sync([$data['lang'] => $lang_data], false);
    }

    public static function updateOrder($id, $order, $parent = 0) {
        $item = self::find($id);
        if ($item) {
            $item->update(['order' => $order, 'parent_id' => $parent]);
        }
    }
    
    public function save(array $options = array()) {
        parent::save($options);
        CacheFunc::forgetMenus();
    }
    
    public function delete() {
        parent::delete();
        CacheFunc::forgetMenus();
    }

}
