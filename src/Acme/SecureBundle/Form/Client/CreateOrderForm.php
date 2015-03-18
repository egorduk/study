<?php

namespace Acme\SecureBundle\Form\Client;

use Helper\Helper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class CreateOrderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldTheme', 'text', array('label'=>'Тема задания', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Введите тему задания', 'size' => 30, 'maxlength' => 50, 'placeholder' => 'Введите название темы...')))
                ->add('fieldTask', 'genemu_tinymce', array('label'=>'Описание задания', 'required' => false, 'attr' => array('class' => 'form-control', 'placeholder' => 'test')))
                ->add('fieldDateExpire', 'text', array('label'=>'Выполнение до', 'required' => true, 'attr' => array('class' => 'form-control', 'readonly' => false, 'title' => 'Выберите дату выполнения задания', 'size' => 10, 'maxlength' => 20, 'placeholder' => 'Нажмите сюда...')))
                ->add('fieldOriginality', 'text', array('label'=>'Процент оригинальности', 'required' => false, 'attr' => array('class' => 'form-control', 'title' => 'Укажите процент оригинальности', 'maxlength' => 3, 'placeholder' => 'Введите процент оригинальности...')))
                ->add('fieldCountSheet', 'text', array('label'=>'Количесто страниц', 'required' => false, 'attr' => array('class' => 'form-control', 'title' => 'Укажите объем задания', 'maxlength' => 3, 'placeholder' => 'Введите число страниц...')))
                ->add('selectorSubject', 'genemu_jqueryselect2_entity', array(
                    'attr' => array(
                        //'class' => 'form-control',
                        'title' => 'Внимание!',
                        'data-content' => "Вы должны выбрать предмет!",
                        'data-trigger' => 'hover',
                    ),
                    'mapped' => true,
                    'required' => true,
                    'label'=>'Предмет:',
                    'class' => 'Acme\SecureBundle\Entity\SubjectOrder',
                    'property' => 'child_name',
                    'empty_value' => '',
                    'group_by' => 'parent_name'
                ))
                ->add('selectorTypeOrder', 'genemu_jqueryselect2_entity', array(
                    'label'=>'Тип работы:',
                    'attr' => array(
                        //'class' => 'form-control',
                        'title' => 'Внимание!',
                        'data-content' => "Вы должны выбрать тип заказа!",
                        'data-trigger' => 'hover',
                    ),
                    'mapped'   => false,
                    'required' => false,
                    'class' => 'Acme\SecureBundle\Entity\TypeOrder',
                    'property' => 'name',
                    'empty_value' => '',
                ))
                ->add('create', 'submit', array('label'=>'Создать', 'attr' => array('class' => 'hidden')))
                ->add('reset', 'reset', array('label'=>'Очистить', 'attr' => array('class' => 'hidden')));

        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $event)
        {
            $form = $event->getForm();
            $task = $form->get('fieldTask')->getData();
            $task = strip_tags($task);

            if ($task != null) {
                $task = Helper::convertFromUtfToCp($task);

                if (strlen($task) < 5) {
                    $form->get('fieldTask')->addError(new FormError('Введите описание более конкретно!'));
                }
            }
        });
    }

    public function getName()
    {
        return 'formCreateOrder';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        /*$resolver->setDefaults(array(
            'data_class'      => 'Acme\AuthBundle\Entity\ClientFormValidate',
            'data_class'      => 'Acme\AuthBundle\Entity\User',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'task_item',
        ));*/
    }
}
