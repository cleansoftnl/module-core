<?php

namespace App\Modules\Core\Database\Seeders;

use Carbon\Carbon;
use App\Modules\Core\Models\Navigation;
use App\Modules\Core\Models\NavigationLink;
use Illuminate\Database\Seeder;

class NavTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        with(new NavigationLink())->truncate();
        with(new Navigation())->truncate();

        $navs = [
            [
                'name' => 'main-menu',
                'class' => 'nav navbar-nav',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($navs as $r) {
            with(new Navigation())->fill($r)->save();
        }

        $navLinks = [
            [
                'navigation_id' => 1,
                'title' => 'Home',
                'url' => null,
                'route' => 'pxcms.pages.home',
                'class' => null,
                'blank' => 0,
                'order' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ];

        foreach ($navLinks as $r) {
            with(new NavigationLink())->fill($r)->save();
        }
    }
}
