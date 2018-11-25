<?php
/**
 * Created by lqdung1992.
 * Date: 3/1/2018
 * Time: 12:54 PM
 */
namespace Plugin\BannerSimple\Controller;

use Eccube\Controller\AbstractController;
use Plugin\BannerSimple\Entity\Banner;
use Plugin\BannerSimple\Form\Type\BannerType;
use Plugin\BannerSimple\Repository\BannerRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BannerSimple controller
 */
class BannerController extends AbstractController
{
    /** @var BannerRepository */
    private $bannerSampleRepo;

    /** @var string */
    private $bannerDir = '/Banner';

    /**
     * BannerController constructor.
     * @param BannerRepository $bannerSampleRepo
     */
    public function __construct(BannerRepository $bannerSampleRepo)
    {
        $this->bannerSampleRepo = $bannerSampleRepo;
    }

    /**
     * BannerSimple management。
     *
     * @param Request $request
     * @param int $type
     * @return array|RedirectResponse
     * @Route("/%eccube_admin_route%/plugin/banner_simple", name="admin_plugin_banner")
     * @Route("/%eccube_admin_route%/plugin/banner_simple/{type}", name="admin_plugin_banner_simple", requirements={"type" = "\d+"})
     * @Template("@BannerSimple/admin/banner.twig")
     */
    public function index(Request $request, $type = Banner::BANNER)
    {
        $banners = $this->bannerSampleRepo->findBy(array('type' => $type), array('sort_no' => 'ASC'));
        /** @var FormBuilder $builder */
        $builder = $this->formFactory->createBuilder(BannerType::class);
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
                        ->setSortno(1)
                        ->setType($type)
                        ->setLink($links[$key]);
                    if (isset($targets[$key])) {
                        $Banner->setTarget($targets[$key]);
                    }
                    if ($type == Banner::BANNER && !empty($bigs[$key])) {
                        $Banner->setBig($bigs[$key]);
                    }
                    $this->entityManager->persist($Banner);

                    $file = new File($this->eccubeConfig->get('eccube_temp_image_dir').'/'.$add_image);
                    $file->move($this->eccubeConfig->get('eccube_save_image_dir').$this->bannerDir);
                }

                $delete_images = $form->get('delete_images')->getData();
                foreach ($delete_images as $delete_image) {
                    $Banner = $this->bannerSampleRepo->findOneBy(array('file_name' => $delete_image));
                    if ($Banner) {
                        $this->entityManager->remove($Banner);
                    }
                    if (!empty($delete_image)
                        && file_exists($fileImage = $this->eccubeConfig->get('eccube_save_image_dir').$this->bannerDir.'/'.$delete_image)) {
                        $fs = new Filesystem();
                        $fs->remove($fileImage);
                    }
                }

                if (!empty($old_images)) {
                    foreach ($old_images as $key => $old_image) {
                        /** @var Banner $Banner */
                        $Banner = $this->bannerSampleRepo->findOneBy(array('file_name' => $old_image));
                        if ($Banner) {
                            $Banner->setLink($links[$key]);
                            if (isset($targets[$key])) {
                                $Banner->setTarget($targets[$key]);
                            }
                            if ($type == Banner::BANNER && !empty($bigs[$key])) {
                                $Banner->setBig($bigs[$key]);
                            }
                            $this->entityManager->persist($Banner);
                        }
                    }
                }
                $this->entityManager->flush();

                $ranks = $request->get('rank_images');
                if ($ranks) {
                    foreach ($ranks as $key => $rank) {
                        list($filename, $sort_no) = explode('//', $rank);
                        unset($banner);
                        $banner = $this->bannerSampleRepo->findOneBy(array('file_name' => $filename, 'type' => $type));
                        if ($banner) {
                            $banner->setSortno($sort_no);
                            $this->entityManager->persist($banner);
                        }
                    }
                }

                $this->entityManager->flush();
                $this->addSuccess("admin.common.save_complete", 'admin');

                return $this->redirectToRoute('admin_plugin_banner_simple', array('type' => $type));
            }
            $this->addError("admin.common.save_error", 'admin');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Add image for banner
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/%eccube_admin_route%/plugin/banner_simple/addImage", name="admin_plugin_banner_simple_image_add")
     */
    public function addImage(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('リクエストが不正です');
        }

        $images = $request->files->get('admin_plugin_banner_simple');

        $allowExtensions = ['gif', 'jpg', 'jpeg', 'png'];

        $files = array();
        if (count($images) > 0) {
            foreach ($images as $img) {
                /** @var UploadedFile $image */
                foreach ($img as $image) {
                    $mimeType = $image->getMimeType();
                    if (0 !== strpos($mimeType, 'image')) {
                        throw new UnsupportedMediaTypeHttpException('ファイル形式が不正です');
                    }

                    $extension = $image->getClientOriginalExtension();
                    if (!in_array($extension, $allowExtensions)) {
                        throw new UnsupportedMediaTypeHttpException();
                    }

                    $filename = date('mdHis').uniqid('_').'.'.$extension;
                    $image->move($this->eccubeConfig->get('eccube_temp_image_dir'), $filename);
                    $files[] = $filename;
                }
            }
        }

        return $this->json(array('files' => $files), 200);
    }

    /**
     * change top page
     *
     * @param Request $request
     * @param boolean $remove
     * @return RedirectResponse
     * @Route("/%eccube_admin_route%/plugin/banner_simple/change/{remove}", name="admin_plugin_banner_simple_top_change")
     */
    public function changeTopPage(Request $request, $remove = false)
    {
        $file = new Filesystem();

        if ($remove) {
            $originFile = __DIR__ . '/../Resource/template/default/index.twig';
            $targetFile = $this->eccubeConfig->get('eccube_theme_front_dir') . '/index.twig';
            $file->copy($originFile, $targetFile, true);
        } else {
            $fileName = $this->eccubeConfig->get('eccube_theme_front_dir') . '/index.twig';
            if (file_exists($fileName)) {
                $file->remove($fileName);
            }
        }
        $this->addSuccess("admin.common.save_complete", 'admin');

        return $this->redirectToRoute('admin_plugin_banner');
    }
}
