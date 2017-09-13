<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\User;
use App\Models\Tax;
use App\Models\Media;

class MediaController extends Controller
{
    protected $media;
    protected $user;

    public function __construct(Media $media, Tax $album, User $user) {
        $this->media = $media;
        $this->album = $album;
        $this->user = $user;
    }

    public function index(Request $request) {
        $items = $this->media->getData($request->all());
        return view('manage.media.index', ['items' => $items]);
    }

    public function create() {
        canAccess('publish_posts');

        $albums = $this->album->getData('album', [
            'orderby' => 'name',
            'order' => 'asc',
            'per_page' => -1,
            'fields' => ['taxs.id', 'taxs.parent_id', 'td.name']]
        );
        $users = null;
        if (cando('manage_posts')) {
            $users = $this->user->getData([
                'orderby' => 'name',
                'order' => 'asc',
                'pre_page' => -1,
                'fields' => ['id', 'name']]
            );
        }
        return view('manage.media.create', compact('albums', 'users'));
    }

    public function store(Request $request) {
        canAccess('publish_posts');
        
        try {
            $this->media->insertData($request->all());
            return redirect()->back()->with('succ_mess', trans('manage.store_success'));
        } catch (ValidationException $ex) {
            return redirect()->back()->withInput()->withErrors($ex->validator);
        } catch (DbException $ex) {
            return redirect()->back()->withInput()->with('error_mess', $ex->getError());
        }
    }

    public function edit($id, Request $request) {
        canAccess('edit_my_post', $this->media->get_author_id($id));

        $lang = current_locale();
        if ($request->has('lang')) {
            $lang = $request->get('lang');
        }
        $albums = $this->album->getData('album', [
            'orderby' => 'name',
            'order' => 'asc',
            'per_page' => -1,
            'fields' => ['taxs.id', 'taxs.parent_id', 'td.name']
        ]);
        $users = null;
        if (cando('manage_posts')) {
            $users = $this->user->getData([
                'orderby' => 'name',
                'order' => 'asc',
                'per_page' => 20,
                'fields' => ['name', 'id']
            ])->lists('name', 'id')->toArray();
        }
        $item = $this->media->findByLang($id, ['medias.*', 'md.*'], $lang);
        $curr_albums = $item->albums->lists('id')->toArray();
        return view('manage.media.edit', compact('item', 'albums', 'users', 'curr_albums', 'lang'));
    }

    public function update($id, Request $request) {
        canAccess('edit_my_post', $this->media->get_author_id($id));
        try {
            $this->media->updateData($id, $request->all());
            return redirect()->back()->with('succ_mess', trans('manage.update_success'));
        } catch (ValidationException $ex) {
            return redirect()->back()->withInput()->withErrors($ex->validator);
        }
    }

    public function destroy($id) {
        canAccess('remove_my_post', $this->media->get_author_id($id));
        if (!$this->media->changeStatus($id, 0)) {
            return redirect()->back()->with('error_mess', trans('manage.no_item'));
        }
        return redirect()->back()->with('succ_mess', trans('manage.destroy_success'));
    }

    public function multiAction(Request $request) {
        try {
            $this->media->actions($request);
            return redirect()->back()->withInput()->with('succ_mess', trans('message.action_success'));
        } catch (\Exception $ex) {
            return redirect()->back()->withInput()->with('error_mess', $ex->getMessage());
        }
    }
}
