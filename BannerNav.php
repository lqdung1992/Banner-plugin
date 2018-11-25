<?php


namespace Plugin\BannerSimple;

use Eccube\Common\EccubeNav;

class BannerNav implements EccubeNav
{
    public static function getNav()
    {
        return [
            'content' => [
                'children' => [
                    'banner_sample' => [
                        'name' => 'banner.nav',
                        'url' => 'admin_plugin_banner_simple',
                    ],
                ],
            ],
        ];
    }
}
