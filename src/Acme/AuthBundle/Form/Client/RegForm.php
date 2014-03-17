<?php

namespace Acme\AuthBundle\Form\Client;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;


class RegForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldLogin', 'text', array('label'=>'Логин:', 'required' => true, 'attr' => array('title' => 'Ваш логин должен состоять только из латинских букв', 'size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите логин...')))
                ->add('fieldPass', 'text', array('label'=>'Пароль:', 'required' => true, 'attr' => array('title' => 'Ваш пароль должен состоять только из латинских букв и цифр', 'size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите пароль...')))
                ->add('fieldPassApprove', 'text', array('label'=>'Подтвердите пароль:', 'required' => true, 'attr' => array('title' => 'Введите Ваш пароль еще раз', 'size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите повторно пароль...')))
                ->add('fieldEmail', 'text', array('label'=>'Email:', 'required' => true, 'attr' => array('title' => 'Введите Ваш email', 'size' => 20, 'maxlength' => 20, 'placeholder' => 'Введите Email...')))
                ->add('checkAgreeRules', 'checkbox', array('label'=>' ', 'required' => true, 'attr' => array('title' => 'Если Вы согласны с правилами, то установите тут флажок', 'class' => '')))
                ->add('reg', 'submit', array('label'=>'Зарегистрироваться', 'attr' => array('class' => 'btn btn-success')))
                ->add('reset', 'reset', array('label'=>'Очистить', 'attr' => array('class' => 'btn btn-success')))
            ->add('current_password', 'password', array(
                'label' => 'form.current_password',
                'translation_domain' => 'FOSUserBundle',
                'mapped' => false,
                'required' => false,
            ))
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'first_name' => 'new_password',
                'second_name' => 'new_password_confirmation',
                'required' => false,
            ));

        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $event)
        {
                $form = $event->getForm();
                $data = $event->getData();

                //if ($data->getPlainPassword() !== null)
                {
                    $plainCurrentPassword = $form->get('current_password')->getData();
                    $plainNewPass = $form->get('plainPassword')->getData();
                    //$user = $this->securityContext->getToken()->getUser();
                    //$encoder = $this->encoder->getEncoder($user);
                    //$encoded = $encoder->encodePassword($plainCurrentPassword, $user->getSalt());

                    if ($plainNewPass != $plainCurrentPassword)
                    {
                        $form->addError(new FormError('Wrong_current_password'));
                    }
                }
            });

    }

    public function getName()
    {
        return 'formReg';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => 'Acme\AuthBundle\Entity\ClientFormValidate',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'task_item',
        ));
    }
}
