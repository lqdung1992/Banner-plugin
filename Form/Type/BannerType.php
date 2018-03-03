<?php
namespace Plugin\BannerSimple\Form\Type;

use Plugin\BannerSimple\Entity\Banner;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BannerType.
 */
class BannerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                'label' => 'タイプ',
                'expanded' => true,
                'choices'  => array(
                    Banner::BANNER => 'バナー',
                    Banner::SLIDER => 'スライダー',
                ),
            ))
            ->add('file_name', 'file', array(
                'label' => 'バナー/スライダー',
                'multiple' => true,
                'required' => false,
                'mapped' => false,
            ))
            ->add('images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('add_images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('delete_images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('links', 'collection', array(
                'label' => 'バナーリンク/スライダリンク',
                'type' => 'url',
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'options'  => array(
                    'attr' => array(
                        'placeholder' => 'URLを入力してください',
                        'pattern' => 'https?://.+',
                        'data-fv-uri' => 'true',
                    ),
                ),
            ))
            ->add('big', 'collection', array(
                'label' => '大きなバナー',
                'type' => 'choice',
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'options' => array(
                    'choices' => array(
                        Banner::IS_SMALL => '小さい',
                        Banner::IS_BIG => '大',
                    ),
                ),
            ))
            ->add('target', 'collection', array(
                'label' => '別ウィンドウを開く',
                'type' => 'checkbox',
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_plugin_banner_simple';
    }
}
