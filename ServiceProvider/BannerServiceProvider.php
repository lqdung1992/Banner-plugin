<?php
/**
 * Created by lqdung1992.
 * Date: 3/1/2018
 * Time: 12:54 PM
 */

namespace Plugin\Banner\ServiceProvider;

use Plugin\Banner\Form\Type\BannerType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Eccube\Common\Constant;

/**
 * Class BannerServiceProvider.
 */
class BannerServiceProvider implements ServiceProviderInterface
{
    /**
     * @param BaseApplication $app
     */
    public function register(BaseApplication $app)
    {
        $app['plugin.banner.repository.banner'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Banner\Entity\Banner');
        });

        // admin screen controller
        $admin = $app['controllers_factory'];
        // 強制SSL
        if ($app['config']['force_ssl'] == Constant::ENABLED) {
            $admin->requireHttps();
        }

        $admin->match('/plugin/banner', '\\Plugin\\Banner\\Controller\\BannerController::index')
            ->bind('admin_plugin_banner');
        $admin->match('/plugin/banner/image/add', '\\Plugin\\Banner\\Controller\\BannerController::addImage')
            ->bind('admin_plugin_banner_image_add');

        $app->mount('/'.trim($app['config']['admin_route'], '/').'/', $admin);

        $app['form.types'] = $app->share($app->extend('form.types', function ($types) {
            $types[] = new BannerType();

            return $types;
        }));

        $file = __DIR__.'/../Resource/locale/message.'.$app['locale'].'.yml';
        $app['translator']->addResource('yaml', $file, $app['locale']);

        $app['config'] = $app->share($app->extend('config', function ($config) {
            $addNavi['id'] = 'banner';
            $addNavi['name'] = 'バナー/スライダー';
            $addNavi['url'] = 'admin_plugin_banner';

            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ('content' == $val['id']) {
                    $nav[$key]['child'][] = $addNavi;
                }
            }

            $config['nav'] = $nav;

            return $config;
        }));

    }

    /**
     * @param BaseApplication $app
     */
    public function boot(BaseApplication $app)
    {
    }
}
