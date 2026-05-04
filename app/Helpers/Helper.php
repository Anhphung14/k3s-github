<?php

namespace App\Helpers;

use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

class Helper
{
    public static function getMenuAdminPanel(): array
    {
        $user = Auth::user();
        $roleId = $user->roles()->value('id');

        $menus = Menu::with(['roles', 'children.roles'])
            ->whereNull('parent_id')
            ->whereHas('roles', fn($q) => $q->where('role_id', $roleId))
            ->orderBy('order')
            ->get();

        $menus->each(function ($menu) use ($roleId) {
            $menu->setRelation(
                'children',
                $menu->children->filter(fn($child) => $child->roles->pluck('id')->contains($roleId))
            );
        });

        return $menus
            ->map(fn($menu) => self::formatMenu($menu))
            ->all();
    }

    /**
     * Format một menu thành mảng
     *
     * @param Menu $menu
     * @return array
     */
    private static function formatMenu(Menu $menu): array
    {
        $children = $menu->children
            ->map(fn($child) => self::formatMenu($child))
            ->values()
            ->all();

        $data = [
            'name' => $menu->name,
        ];

        if ($menu->link) $data['link'] = $menu->link;
        if ($menu->is_heading) $data['heading'] = $menu->name;
        if ($menu->image) $data['icon'] = $menu->image;
        if ($menu->image_active) $data['icon_active'] = $menu->image_active;
        if (!empty($children)) {
            $data['children'] = $children;
        }

        return $data;
    }
}
