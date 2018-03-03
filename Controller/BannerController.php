<?php
namespace Plugin\BannerSimple\Controller;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Plugin\BannerSimple\Entity\Banner;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * BannerSimple controller
 */
class BannerController extends AbstractController
{
    /**
     * BannerSimple management。
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $type = $request->get('type', Banner::BANNER);
        $banners = $app['plugin.banner_simple.repository.banner']->findBy(array('type' => $type), array('rank' => 'ASC'));
        /** @var FormBuilder $builder */
        $builder = $app['form.factory']->createBuilder('admin_plugin_banner_simple');
        $form = $builder->getForm();
        $images = array();
        $links = array();
        $big = array();
        $target = array();
        /** @var Banner $banner */
        foreach ($banners as $banner) {
            $images[] = $banner->getFileName();
            $links[] = $banner->getLink();
            $big[] = $banner->getBig();
            $target[] = (bool)$banner->getTarget();
        }
        $form['images']->setData($images);
        $form['links']->setData($links);
        $form['type']->setData($type);
        $form['big']->setData($big);
        $form['target']->setData($target);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $add_images = $form->get('add_images')->getData();
                $old_images = $form->get('images')->getData();
                $links = $form->get('links')->getData();
                $bigs = $form->get('big')->getData();
                $targets = $form->get('target')->getData();
                foreach ($add_images as $key => $add_image) {
                    $Banner = new Banner();
                    $Banner
                        ->setFileName($add_image)
                        ->setRank(1)
                        ->setType($type)
                        ->setLink($links[$key]);
                    if (isset($targets[$key])) {
                        $Banner->setTarget($targets[$key]);
                    }
                    if ($type == Banner::BANNER) {
                        $Banner->setBig($bigs[$key]);
                    }
                    $app['orm.em']->persist($Banner);

                    $file = new File($app['config']['image_temp_realdir'].'/'.$add_image);
                    $file->move($app['config']['image_save_realdir']);
                }

                $delete_images = $form->get('delete_images')->getData();
                foreach ($delete_images as $delete_image) {
                    $Banner = $app['plugin.banner_simple.repository.banner']->findOneBy(array('file_name' => $delete_image));
                    if ($Banner instanceof Banner) {
                        $app['orm.em']->remove($Banner);

                    }
                    if (!empty($delete_image) && file_exists($app['config']['image_save_realdir'].'/'.$delete_image)) {
                        $fs = new Filesystem();
                        $fs->remove($app['config']['image_save_realdir'].'/'.$delete_image);
                    }
                }

                if (!empty($old_images)) {
                    foreach ($old_images as $key => $old_image) {
                        /** @var Banner $Banner */
                        $Banner = $app['plugin.banner_simple.repository.banner']->findOneBy(array('file_name' => $old_image));
                        if ($Banner) {
                            $Banner->setLink($links[$key]);
                            if (isset($targets[$key])) {
                                $Banner->setTarget($targets[$key]);
                            }
                            if ($type == Banner::BANNER) {
                                $Banner->setBig($bigs[$key]);
                            }
                            $app['orm.em']->persist($Banner);
                        }
                    }
                }
                $app['orm.em']->flush();

                $ranks = $request->get('rank_images');
                if ($ranks) {
                    foreach ($ranks as $key => $rank) {
                        list($filename, $rank_val) = explode('//', $rank);
                        unset($banner);
                        $banner = $app['plugin.banner_simple.repository.banner']->findOneBy(array('file_name' => $filename, 'type' => $type));
                        if ($banner) {
                            $banner->setRank($rank_val);
                            $app['orm.em']->persist($banner);
                        }
                    }
                }

                $app['orm.em']->flush();
                $app->addSuccess("admin.plugin.banner.success", 'admin');

                return $app->redirect($app->url('admin_plugin_banner_simple', array('type' => $type)));
            }
            $app->addError("admin.plugin.banner.error", 'admin');
        }

        return $app->render('BannerSimple/Resource/template/admin/banner.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Add image for banner
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addImage(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('リクエストが不正です');
        }

        $images = $request->files->get('admin_plugin_banner_simple');

        $files = array();
        if (count($images) > 0) {
            foreach ($images as $img) {
                foreach ($img as $image) {
                    $mimeType = $image->getMimeType();
                    if (0 !== strpos($mimeType, 'image')) {
                        throw new UnsupportedMediaTypeHttpException('ファイル形式が不正です');
                    }

                    $extension = $image->getClientOriginalExtension();
                    $filename = date('mdHis').uniqid('_').'.'.$extension;
                    $image->move($app['config']['image_temp_realdir'], $filename);
                    $files[] = $filename;
                }
            }
        }

        return $app->json(array('files' => $files), 200);
    }
}
