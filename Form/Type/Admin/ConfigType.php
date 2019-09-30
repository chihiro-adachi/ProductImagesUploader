<?php

namespace Plugin\ProductImagesUploader\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('image_file', FileType::class, [
            'constraints' => [
                new NotBlank(['message' => 'ファイルを選択してください。']),
                new File([
                    'mimeTypes' => ['application/zip'],
                    'mimeTypesMessage' => 'zipファイルをアップロードしてください。',
                ]),
            ],
        ]);
    }
}
