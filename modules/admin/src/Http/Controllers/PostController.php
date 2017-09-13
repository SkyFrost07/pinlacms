<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PostType;
use App\Models\Tax;
use App\User;
use App\Exceptions\DbException;
use Illuminate\Validation\ValidationException;

class PostController extends Controller {

    protected $post;
    protected $tax;
    protected $user;

    public function __construct(PostType $post, Tax $tax, User $user) {
        $this->post = $post;
        $this->tax = $tax;
        $this->user = $user;
    }

    public function index(Request $request) {
        $items = $this->post->getData('post', $request->all());
        return view('manage.post.index', ['items' => $items]);
    }

    public function create() {
        canAccess('publish_posts');

        $cats = $this->tax->getData('cat', [
            'orderby' => 'name',
            'order' => 'asc',
            'per_page' => -1,
            'fields' => ['taxs.id', 'taxs.parent_id', 'td.name']]
        );
        $tags = $this->tax->getData('tag', [
            'orderby' => 'name',
            'order' => 'asc',
            'per_page' => -1,
            'fields' => ['taxs.id', 'td.name']]
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
        return view('manage.post.create', compact('cats', 'tags', 'users'));
    }

    public function store(Request $request) {
        canAccess('publish_posts');

        try {
            $this->post->insertData($request->all(), 'post');
            return redirect()->back()->with('succ_mess', trans('manage.store_success'));
        } catch (ValidationException $ex) {
            return redirect()->back()->withInput()->withErrors($ex->validator);
        } catch (DbException $ex) {
            return redirect()->back()->withInput()->with('error_mess', $ex->getError());
        }
    }

    public function edit($id, Request $request) {
        canAccess('edit_my_post', $this->post->get_author_id($id));

        $lang = current_locale();
        if ($request->has('lang')) {
            $lang = $request->get('lang');
        }
        $cats = $this->tax->getData('cat', [
            'orderby' => 'name',
            'order' => 'asc',
            'per_page' => -1,
            'fields' => ['taxs.id', 'taxs.parent_id', 'td.name']
        ]);
        $tags = $this->tax->getData('tag', [
            'orderby' => 'name',
            'order' => 'asc',
            'per_page' => -1,
            'fields' => ['taxs.id', 'td.name']
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
        $item = $this->post->findByLang($id, ['posts.*', 'pd.*'], $lang);
        $curr_cats = $item->cats->lists('id')->toArray();
        $curr_tags = $item->tags->lists('id')->toArray();
        return view('manage.post.edit', compact('item', 'cats', 'tags', 'users', 'curr_cats', 'curr_tags', 'lang'));
    }

    public function update($id, Request $request) {
        canAccess('edit_my_post', $this->post->get_author_id($id));
        try {
            $this->post->updateData($id, $request->all());
            return redirect()->back()->with('succ_mess', trans('manage.update_success'));
        } catch (ValidationException $ex) {
            return redirect()->back()->withInput()->withErrors($ex->validator);
        }
    }

    public function destroy($id) {
        canAccess('remove_my_post', $this->media->get_author_id($id));
        
        if (!$this->post->changeStatus($id, 0)) {
            return redirect()->back()->with('error_mess', trans('manage.no_item'));
        }
        return redirect()->back()->with('succ_mess', trans('manage.destroy_success'));
    }

    public function multiAction(Request $request) {
        if(!cando('remove_other_posts')){
            return redirect()->back()->withInput()->with('error_mess', trans('auth.authorize'));
        }
        try {
            $this->post->actions($request);
            return redirect()->back()->withInput()->with('succ_mess', trans('message.action_success'));
        } catch (\Exception $ex) {
            return redirect()->back()->withInput()->with('error_mess', $ex->getMessage());
        }
    }

}
