<?php

namespace Acme\SecureBundle\Form\Client;

use Helper\Helper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Util\StringUtils;


class CreateOrderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldTheme', 'text', array('label'=>'Тема задания:', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Введите тему задания', 'size' => 30, 'maxlength' => 20, 'placeholder' => 'Введите название темы...')))
                ->add('fieldDescribe', 'text', array('label'=>'Описание задания:', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Опишите требования к работе', 'size' => 30, 'maxlength' => 20, 'placeholder' => 'Введите требования...')))
                ->add('fieldDateExpire', 'text', array('label'=>'Выполнение до:', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Укажите до какой даты выполняется задание', 'size' => 30, 'maxlength' => 20, 'placeholder' => '')))
                ->add('fieldOriginality', 'text', array('label'=>'Оригинальность:', 'required' => false, 'attr' => array('class' => 'form-control', 'title' => 'Укажите процент оригинальности', 'size' => 3, 'maxlength' => 3, 'placeholder' => '')))
                ->add('fieldCountSheet', 'text', array('label'=>'Количесто страниц:', 'required' => false, 'attr' => array('class' => 'form-control', 'title' => 'Укажите объем задания', 'size' => 3, 'maxlength' => 3, 'placeholder' => 'Число...')))
                ->add('selectorSubject', 'genemu_jqueryselect2_entity', array(
                    'attr' => array(
                        'class' => 'required',
                        'title' => 'Внимание!',
                        //'data-placement' => "right",
                        'data-content' => "Вы должны выбрать предмет!",
                        'data-trigger' => 'hover',
                    ),
                    'mapped' => true,
                    'required' => true,
                    'label'=>'Предмет:',
                    'class' => 'Acme\SecureBundle\Entity\Subject',
                    'property' => 'child_name',
                    'empty_value' => '',
                    'group_by' => 'parent_name'
                ))
                ->add('selectorTypeOrder', 'genemu_jqueryselect2_entity', array(
                    'label'=>'Тип работы:',
                    'attr' => array(
                        'class' => 'required',
                        'title' => 'Внимание!',
                       // 'data-placement' => "right",
                        'data-content' => "Вы должны выбрать тип заказа!",
                        'data-trigger' => 'hover',
                    ),
                    'mapped'   => false,
                    'required' => false,
                    'class' => 'Acme\SecureBundle\Entity\TypeOrder',
                    'property' => 'name',
                    'empty_value' => '',
                ))
                ->add('create', 'submit', array('label'=>'Создать', 'attr' => array('class' => 'btn btn-success')))
                ->add('reset', 'reset', array('label'=>'Очистить', 'attr' => array('class' => 'btn btn-success')));

        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $event)
        {
            /*$form = $event->getForm();

            $selectedSubject = $form->get('selectorSubject')->getData();
            $selectedTypeOrder = $form->get('selectorTypeOrder')->getData();

            if (!$selectedSubject)
            {
                $form->get('selectorSubject')->addError(new FormError('Выберите предмет!'));
            }

            if (!$selectedTypeOrder)
            {
                $form->get('selectorTypeOrder')->addError(new FormError('Выберите тип заказа!'));
            }*/
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
