<?php
/**
 * Created by lqdung1992.
 * Date: 3/1/2018
 * Time: 12:54 PM
 */
namespace Plugin\BannerSimple\Controller\Block;

use Eccube\Application;
use Plugin\BannerSimple\Entity\Banner;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BannerController
 * @package Plugin\BannerSimple\Controller\Block
 */
class BannerController
{
    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $arrBanner = $app['plugin.banner_simple.repository.banner']->findBy(array('type' => Banner::BANNER));
        $arrSlider = $app['plugin.banner_simple.repository.banner']->findBy(array('type' => Banner::SLIDER));
        return $app->render('Block/banner.twig', array(
            'banners' => $arrBanner,
            'sliders' => $arrSlider,
        ));
    }
}
