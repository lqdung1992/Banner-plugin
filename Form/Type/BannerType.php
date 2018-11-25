<?php
/**
 * Created by lqdung1992.
 * Date: 3/1/2018
 * Time: 12:54 PM
 */
namespace Plugin\BannerSimple\Form\Type;

use Plugin\BannerSimple\Entity\Banner;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
            ->add('type', ChoiceType::class, array(
                'label' => trans('banner.type.label'),
                'expanded' => true,
                'choices'  => array_flip(array(
                    Banner::BANNER => trans('banner.type.banner'),
                    Banner::SLIDER => trans('banner.type.slider'),
                )),
            ))
            ->add('file_name', FileType::class, array(
                'label' => trans('banner.type.file'),
                'multiple' => true,
                'required' => false,
                'mapped' => false,
            ))
            ->add('images', CollectionType::class, array(
                'entry_type' => HiddenType::class,
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('add_images', CollectionType::class, array(
                'entry_type' => HiddenType::class,
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('delete_images', CollectionType::class, array(
                'entry_type' => HiddenType::class,
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('links', CollectionType::class, array(
                'label' => 'banner.type.link',
                'entry_type' => UrlType::class,
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'entry_options'  => array(
                    'attr' => array(
                        'placeholder' => 'banner.type.link.placeholder',
                        'pattern' => 'https?://.+',
                        'data-fv-uri' => 'true',
                    ),
                ),
            ))
            ->add('big', CollectionType::class, array(
                'label' => 'banner.type.big',
                'entry_type' => ChoiceType::class,
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'entry_options' => array(
                    'choices' => array_flip(array(
                        Banner::IS_BIG => trans('banner.file.big.big'),
                        Banner::IS_SMALL => trans('banner.file.big.small'),
                    )),
                    'placeholder' => false,
                ),
            ))
            ->add('target', CollectionType::class, array(
                'label' => trans('banner.type.target'),
                'entry_type' => CheckboxType::class,
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'entry_options' => array(),
            ));
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
    public function getBlockPrefix()
    {
        return 'admin_plugin_banner_simple';
    }
}
