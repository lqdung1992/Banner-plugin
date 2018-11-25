<?php
/**
 * Created by lqdung1992.
 * Date: 3/1/2018
 * Time: 12:54 PM
 */
namespace Plugin\BannerSimple;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Layout;
use Eccube\Entity\Master\DeviceType;
use Eccube\Plugin\AbstractPluginManager;
use Eccube\Repository\BlockPositionRepository;
use Eccube\Repository\BlockRepository;
use Eccube\Repository\LayoutRepository;
use Eccube\Repository\Master\DeviceTypeRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class PluginManager.
 */
class PluginManager extends AbstractPluginManager
{
    private $originalDir = '/BannerSimple/Resource/template/default/Block/';
    private $fileNames = ['banner_simple', 'slider_simple'];
    private $targetDir = '/Block/';
    private $extension = '.twig';

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @return bool|void
     */
    public function install(array $config, ContainerInterface $container)
    {
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @return bool|void
     */
    public function uninstall(array $config, ContainerInterface $container)
    {
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function enable(array $config, ContainerInterface $container)
    {
        $this->copyBlock($container);
        $this->installBlockData($container);
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function disable(array $config, ContainerInterface $container)
    {
        $this->removeBlockData($container);
        $this->removeBlock($container);
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @return bool|void
     */
    public function update(array $config, ContainerInterface $container)
    {
        $this->copyBlock($container);
        $this->removeBlockData($container);
        $this->installBlockData($container);
    }

    /**
     * @param ContainerInterface $container
     */
    protected function copyBlock(ContainerInterface $container)
    {
        $file = new Filesystem();
        $eccubeConfig = $container->get(EccubeConfig::class);
        foreach ($this->fileNames as $fileName) {
            $file->copy($eccubeConfig->get('plugin_realdir').$this->originalDir . $fileName . $this->extension,
                $eccubeConfig->get('eccube_theme_front_dir').$this->targetDir . $fileName . $this->extension, true);
        }
    }

    /**
     * @param ContainerInterface $container
     */
    protected function removeBlock(ContainerInterface $container): void
    {
        $file = new Filesystem();
        $eccubeConfig = $container->get(EccubeConfig::class);
        foreach ($this->fileNames as $fileName) {
            $file->remove($eccubeConfig->get('eccube_theme_front_dir') . $this->targetDir . $fileName . $this->extension);
        }
    }

    /**
     * @param ContainerInterface $container
     */
    protected function removeBlockData(ContainerInterface $container): void
    {
        $blockRepo = $container->get(BlockRepository::class);
        $em = $container->get('doctrine.orm.entity_manager');
        foreach ($this->fileNames as $fileName) {
            $Block = $blockRepo->findOneBy(array('file_name' => $fileName));
            if ($Block) {
                $BlockPositions = $em
                    ->getRepository('Eccube\Entity\BlockPosition')
                    ->findBy(array('Block' => $Block));
                foreach ($BlockPositions as $BlockPosition) {
                    $em->remove($BlockPosition);
                }
                $em->remove($Block);
            }
        }
        $em->flush();
    }

    /**
     * @param ContainerInterface $container
     */
    protected function installBlockData(ContainerInterface $container): void
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $blockRepo = $container->get(BlockRepository::class);
        // Create block
        foreach ($this->fileNames as $fileName) {
            $Block = $blockRepo->findOneBy(array('file_name' => $fileName));
            if ($Block) {
                continue;
            }

            $Block = new \Eccube\Entity\Block();
            $Block->setFileName($fileName);
            $Block->setName(str_replace('_', '', ucwords($fileName, '_')));
            $Block->setUseController(false);
            $Block->setDeletable(false);
            $DeviceType = $container->get(DeviceTypeRepository::class)->find(DeviceType::DEVICE_TYPE_PC);
            $Block->setDeviceType($DeviceType);
            $em->persist($Block);
            $em->flush($Block);

            /** @var Layout $layout */
            $layout = $container->get(LayoutRepository::class)->find(Layout::DEFAULT_LAYOUT_TOP_PAGE);
            // Create block position
            $BlockPos = new BlockPosition();
            $BlockPos->setBlock($Block)
                ->setBlockId($Block->getId())
                ->setLayout($layout)
                ->setLayoutId($layout->getId())
                ->setSection(Layout::TARGET_ID_MAIN_TOP)
                ->setBlockRow(1);

            $blockOtherPos = $container->get(BlockPositionRepository::class)->findBy(
                ['section' => Layout::TARGET_ID_MAIN_TOP, 'layout_id' => Layout::DEFAULT_LAYOUT_TOP_PAGE],
                ['block_row' => 'DESC']
            );
            if ($blockOtherPos) {
                /** @var BlockPosition $blockOtherPo */
                foreach ($blockOtherPos as $blockOtherPo) {
                    $blockOtherPo->setBlockRow($blockOtherPo->getBlockRow() + 1);
                    $em->persist($blockOtherPo);
                }
            }

            $em->persist($BlockPos);
            $Block->addBlockPosition($BlockPos);
        }

        $em->flush();
    }
}
