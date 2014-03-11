<?php

namespace Acme\AuthBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Tests\Fixtures\Entity;


class LoginForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldLogin', 'text', array('label'=>'Логин:', 'required' => true, 'attr' => array('size' => 15, 'placeholder' => 'Введите логин...')))
                ->add('fieldPass', 'text', array('label'=>'Пароль:', 'required' => true, 'attr' => array('size' => 15, 'placeholder' => 'Введите пароль...')))
                ->add('enter', 'submit', array('label'=>'Войти', 'attr' => array('class' => 'btn btn-success')))
                ->add('reg', 'submit', array('label'=>'Регистрация', 'attr' => array('class' => 'btn btn-success')));
    }

    public function getName()
    {
        return 'formLogin';
    }
}
