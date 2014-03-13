<?php

namespace Acme\AuthBundle\Form\Client;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Tests\Fixtures\Entity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;


class LoginForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldLogin', 'text', array('label'=>'Логин:', 'required' => true, 'attr' => array('size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите логин...')))
                ->add('fieldPass', 'password', array('label'=>'Пароль:', 'required' => true, 'attr' => array('size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите пароль...')))
                ->add('enter', 'submit', array('label'=>'Войти', 'attr' => array('class' => 'btn btn-success')));
    }

    public function getName()
    {
        return 'formLogin';
    }
}
