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
        $builder->add('fieldTheme', 'text', array('label'=>'Тема задания:', 'required' => true, 'attr' => array('title' => 'Введите тему задания', 'size' => 30, 'maxlength' => 20, 'placeholder' => 'Введите название темы...')))
                ->add('fieldDescribe', 'text', array('label'=>'Описание задания:', 'required' => true, 'attr' => array('title' => 'Опишите требования к работе', 'size' => 30, 'maxlength' => 20, 'placeholder' => 'Введите требования...')))
                ->add('fieldDateExpire', 'text', array('label'=>'Выполнение до:', 'required' => true, 'attr' => array('title' => 'Укажите до какой даты выполняется задание', 'size' => 30, 'maxlength' => 20, 'placeholder' => '')))
                ->add('fieldOriginality', 'text', array('label'=>'Оригинальность:', 'required' => false, 'attr' => array('title' => 'Укажите процент оригинальности', 'size' => 30, 'maxlength' => 4, 'placeholder' => 'Введите процент...')))
                ->add('fieldCountSheet', 'text', array('label'=>'Количесто страниц:', 'required' => false, 'attr' => array('title' => 'Укажите объем задания', 'size' => 30, 'maxlength' => 3, 'placeholder' => 'Введите число страниц...')))
                ->add('selectorSubject', 'genemu_jqueryselect2_entity', array(
                    'mapped' => true,
                    'required' => true,
                    'label'=>'Предмет:',
                    'class' => 'Acme\SecureBundle\Entity\Subject',
                    'property' => 'name',
                    'empty_value'       => '',
                    'attr' => array(
                        'title' => 'Выберите предмет работы'
                    )
               // 'empty_data'        => null,

                /*'configs' => array(
                    'placeholder' => '123',
                )*/
                    //'data' => 11
                ))
                ->add('selectorTypeWork', 'genemu_jqueryselect2_entity', array(
                    'label'=>'Тип работы:',
                    'mapped'   => false,
                    'class' => 'Acme\SecureBundle\Entity\TypeOrder',
                    'property' => 'name'
                ))
                ->add('create', 'submit', array('label'=>'Создать', 'attr' => array('class' => 'btn btn-success')))
                ->add('reset', 'reset', array('label'=>'Очистить', 'attr' => array('class' => 'btn btn-success')));
    }

    public function getName()
    {
        return 'formCreateOrder';
    }

    protected function buildChoices()
    {
        /*$container = Helper::getContainer();
        $choices = [];
        $table2Repository = $container->get('doctrine')->getRepository('Acme\AuthBundle\Entity\Country');
        $table2Objects = $table2Repository->findAll();

        foreach ($table2Objects as $table2Obj) {
            $choices[$table2Obj->getCode()] = $table2Obj->getName();
        }

        return $choices;*/
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
