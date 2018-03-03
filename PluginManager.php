<?php
namespace Plugin\BannerSimple;

use Eccube\Application;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\PageLayout;
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class PluginManager.
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * @param $config
     * @param $app
     * @return bool
     */
    public function install($config, $app)
    {
        $file = new Filesystem();
        try {
            $file->copy($app['config']['plugin_realdir']. '/BannerSimple/Resource/template/default/Block/banner.twig',
                $app['config']['template_realdir']. '/Block/banner.twig', true);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $config
     * @param $app
     * @return bool
     */
    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code'], 0);

        $file = new Filesystem();
        try {
            $file->remove($app['config']['template_realdir']. '/Block/banner.twig');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param array       $config
     * @param Application $app
     */
    public function enable($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);

        // Create block
        $Block = new \Eccube\Entity\Block();
        $Block->setFileName('banner_simple');
        $Block->setName('バナー/スライダー');
        $Block->setLogicFlg(1);
        $Block->setDeletableFlg(0);
        $DeviceType = $app['eccube.repository.master.device_type']->find(DeviceType::DEVICE_TYPE_PC);
        $Block->setDeviceType($DeviceType);
        $app['orm.em']->persist($Block);
        $app['orm.em']->flush();

        /** @var PageLayout $PageTop */
        $PageTop = $app['eccube.repository.page_layout']->find(1);
        // Create block position
        $BlockPos = new BlockPosition();
        $BlockPos->setBlock($Block)
            ->setBlockId($Block->getId())
            ->setPageLayout($PageTop)
            ->setPageId($PageTop->getId())
            ->setTargetId(5)
            ->setBlockRow(1)
            ->setAnywhere(0);

        $app['orm.em']->persist($BlockPos);
        $Block->addBlockPosition($BlockPos);
        $app['orm.em']->flush();
    }

    /**
     * @param array       $config
     * @param Application $app
     */
    public function disable($config, $app)
    {
        $Block = $app['eccube.repository.block']->findOneBy(array('file_name' => 'banner_simple'));
        if($Block){
            $BlockPositions = $app['orm.em']
                ->getRepository('Eccube\Entity\BlockPosition')
                ->findBy(array('Block' => $Block));
            foreach($BlockPositions as $BlockPosition){
                $app['orm.em']->remove($BlockPosition);
            }
            $app['orm.em']->remove($Block);
        }
        $app['orm.em']->flush();
    }

    /**
     * @param $config
     * @param $app
     * @return bool
     */
    public function update($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);

        $file = new Filesystem();
        try {
            $file->copy($app['config']['plugin_realdir']. '/BannerSimple/Resource/template/default/Block/banner.twig',
                $app['config']['template_realdir']. '/Block/banner.twig', true);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
