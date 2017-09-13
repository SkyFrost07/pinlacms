<?php

namespace Admin\Seeds;

use Admin\Seeds\BaseSeeder;
use App\Models\Cap;
use Admin\Facades\AdView\AdView;

class CapSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkSeeder()) {
            return;
        }
        // name => [[role_ids]]
        $capOther = AdView::CAP_OTHER;
        $capSelf = AdView::CAP_SELF;
        $caps = [
            'view_post' => [1 => $capOther, 2 => $capOther, 3 => $capOther],
            'publish_post' => [1 => $capOther, 2 => $capOther, 3 => $capOther],
            'edit_post' => [1 => $capOther, 2 => $capOther, 3 => $capSelf],
            'remove_post' => [1 => $capOther, 2 => $capOther, 3 => $capSelf],
            
            'view_user' => [1 => $capOther, 2 => $capSelf, 3 => $capSelf],
            'publish_user' => [1 => $capOther],
            'edit_user' => [1 => $capOther, 2 => $capSelf, 3 => $capSelf],
            'remove_user' => [1 => $capOther],
            
            'view_comment' => [1 => $capOther, 2 => $capOther, 3 => $capOther],
            'publish_comment' => [1 => $capOther, 2 => $capOther, 3 => $capOther],
            'edit_comment' => [1 => $capOther, 2 => $capOther, 3 => $capSelf],
            'remove_comment' => [1 => $capOther, 2 => $capOther, 3 => $capSelf],
            
            'view_file' => [1 => $capOther, 2 => $capOther, 3 => $capSelf],
            'publish_file' => [1 => $capOther, 2 => $capOther, 3 => $capOther],
            'edit_file' => [1 => $capOther, 2 => $capOther, 3 => $capSelf],
            'remove_file' => [1 => $capOther, 2 => $capOther, 3 => $capSelf],
            
            'manage_roles' => [1 => $capOther],
            'manage_cap' => [1 => $capOther],
            'accept_manage' => [1 => $capOther, 2 => $capOther],
            'manage_langs' => [1 => $capOther], 
            'manage_cats' => [1 => $capOther],
            'manage_tags' => [1 => $capOther, 2 => $capOther],
            'manage_menus' => [1 => $capOther],
            'manage_options' => [1 => $capOther]
        ];
        
        foreach ($caps as $cap => $arrLevel) {
            $data = ['name' => $cap, 'label' => $this->toLabel($cap)];
            $capItem = Cap::create($data);
            $roleAttrs = [];
            foreach ($arrLevel as $roleId => $level) {
                $roleAttrs[$roleId] = ['level' => $level];
            }
            $capItem->roles()->attach($roleAttrs);
        }
        
        $this->insertSeeder();
    }
    
    public function toLabel($cap) {
        $arrWords = explode('_', $cap);
        $arrLabel = [];
        foreach ($arrWords as $word) {
            array_push($arrLabel, ucfirst($word));
        }
        return implode(' ', $arrLabel);
    }
}
