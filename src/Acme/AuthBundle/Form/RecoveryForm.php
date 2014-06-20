<?php

namespace Acme\AuthBundle\Form;

use Helper\Helper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;


class RecoveryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldEmail', 'text', array('label'=>'Email', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Введите Ваш email', 'size' => 20, 'maxlength' => 25, 'placeholder' => 'Введите Email...')))
                ->add('recovery', 'submit', array('label'=>'Восстановить пароль', 'attr' => array('class' => 'hidden')));

        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $event) {
            $form = $event->getForm();
            $email = $form->get('fieldEmail')->getData();
            if ($email != null) {
                $emailConstraint = new EmailConstraint();
                $container = Helper::getContainer();
                $errors = $container->get('validator')->validateValue($email, $emailConstraint);
                if (count($errors)) {
                    $form->get('fieldEmail')->addError(new FormError('Email введен неправильно!'));
                }
            }
        });
    }

    public function getName()
    {
        return 'formRecovery';
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
