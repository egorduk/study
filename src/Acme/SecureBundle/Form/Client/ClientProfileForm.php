<?php

namespace Acme\SecureBundle\Form\Client;

use Helper\Helper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Util\StringUtils;


class ClientProfileForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldUsername', 'text', array('label'=>'Имя', 'required' => false, 'data' => $options['data']->fieldUsername, 'attr' => array('class' => 'form-control', 'title' => 'Ваше имя должно состоять только из русских букв', 'size' => 30, 'maxlength' => 20, 'placeholder' => 'Введите имя...')))
                ->add('fieldSurname', 'text', array('label'=>'Фамилия', 'required' => false, 'data' => $options['data']->fieldSurname, 'attr' => array('class' => 'form-control', 'title' => 'Ваша фамилия должна состоять только из русских букв', 'size' => 30, 'maxlength' => 20, 'placeholder' => 'Введите фамилию...')))
                ->add('fieldLastname', 'text', array('label'=>'Отчество', 'required' => false, 'data' => $options['data']->fieldLastname, 'attr' => array('class' => 'form-control', 'title' => 'Ваше отчество должно состоять только из русских букв', 'size' => 30, 'maxlength' => 20, 'placeholder' => 'Введите отчество...')))
                ->add('fieldMobilePhone', 'text', array('label'=>'Мобильный телефон', 'required' => false, 'data' => $options['data']->fieldMobilePhone, 'attr' => array('class' => 'form-control', 'title' => 'Введите номер Вашего мобильного телефона', 'size' => 22, 'maxlength' => 14, 'placeholder' => 'Введите номер...')))
                ->add('fieldStaticPhone', 'text', array('label'=>'Стационарный телефон', 'required' => false, 'data' => $options['data']->fieldStaticPhone, 'attr' => array('class' => 'form-control', 'title' => 'Введите номер Вашего стационарного телефона', 'size' => 22, 'maxlength' => 14, 'placeholder' => 'Введите номер...')))
                ->add('fieldSkype', 'text', array('label'=>'Skype', 'required' => false, 'data' => $options['data']->fieldSkype, 'attr' => array('class' => 'form-control', 'title' => 'Введите Ваш Skype', 'size' => 22, 'maxlength' => 20, 'placeholder' => 'Введите Skype...')))
                ->add('fieldIcq', 'text', array('label'=>'Icq', 'required' => false, 'data' => $options['data']->fieldIcq, 'attr' => array('class' => 'form-control', 'title' => 'Введите Ваш Icq', 'size' => 20, 'maxlength' => 10, 'placeholder' => 'Введите Icq...')))
                ->add('selectorCountry', 'choice', array(
                    'label'=>'Страна',
                    'data' => $options['data']->selectorCountry,
                    'mapped'   => false,
                    'choices' => $this->buildChoices(),
                ))
                ->add('save', 'submit', array('label'=>'Сохранить', 'attr' => array('class' => 'hidden')))
                ->add('reset', 'reset', array('label'=>'Очистить', 'attr' => array('class' => 'hidden')));
    }

    public function getName()
    {
        return 'formProfile';
    }

    protected function buildChoices()
    {
        $container = Helper::getContainer();
        $choices = [];
        $table2Repository = $container->get('doctrine')->getRepository('Acme\AuthBundle\Entity\Country');
        $table2Objects = $table2Repository->findAll();
        foreach ($table2Objects as $table2Obj) {
            $choices[$table2Obj->getCode()] = $table2Obj->getName();
        }
        return $choices;
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
