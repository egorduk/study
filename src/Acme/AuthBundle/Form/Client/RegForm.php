<?php

namespace Acme\AuthBundle\Form\Client;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Tests\Fixtures\Entity;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\LengthValidator;
use Symfony\Component\Validator\Constraints\Collection;


class RegForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $collectionConstraint = new Collection(array(
            'name' => new Length(5),
            'email' => new Email(array('message' => 'Invalid email address')),
        ));

        /*$builder->add('fieldNick', 'text', array('label'=>'Ник:', 'required' => true, 'attr' => array('size' => 15, 'placeholder' => 'Введите ник...')))
                ->add('fieldLogin', 'text', array('label'=>'Логин:', 'required' => true, 'attr' => array('size' => 15, 'placeholder' => 'Введите логин...')))
                ->add('fieldPass', 'text', array('label'=>'Пароль:', 'required' => true, 'attr' => array('size' => 15, 'placeholder' => 'Введите пароль...')))
                ->add('fieldPassApprove', 'text', array('label'=>'Подтвердите пароль:', 'required' => true, 'attr' => array('size' => 15, 'placeholder' => 'Введите повторно пароль...')))
                ->add('fieldEmail', 'text', array('label'=>'Email:', 'required' => true, 'attr' => array('size' => 15, 'placeholder' => 'Введите Email...')))
                ->add('reg', 'submit', array('label'=>'Зарегистрироваться', 'attr' => array('class' => 'btn btn-success')));*/

        $form = $this->createFormBuilder(null, array(
            'validation_constraint' => $collectionConstraint,
        ))->add('email', 'email');
        $builder->add('email', $form)
                ->add('reg', 'submit', array('label'=>'Зарегистрироваться', 'attr' => array('class' => 'btn btn-success')));
        //$builder['validation_constraint'] = $collectionConstraint;
        /*$builder = $this->createFormBuilder(null, array(
            'validation_constraint' => $collectionConstraint,
        ))->add('email', 'email');*/


    }

    public function getName()
    {
        return 'formReg';
    }
}
