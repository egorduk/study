<?php

namespace Acme\AuthBundle\Form\Client;

use Helper\Helper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Util\StringUtils;
require_once '..\src\Acme\AuthBundle\Lib\recaptchalib.php';


class RegForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldLogin', 'text', array('label'=>'Логин:', 'required' => true, 'data' => $options['data']->fieldLogin, 'attr' => array('title' => 'Ваш логин должен состоять только из латинских букв', 'size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите логин...')))
                ->add('fieldPass', 'password', array('label'=>'Пароль:', 'required' => true, 'attr' => array('title' => 'Ваш пароль должен состоять только из латинских букв и цифр', 'size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите пароль...')))
                ->add('fieldPassApprove', 'password', array('label'=>'Подтвердите пароль:', 'required' => true, 'attr' => array('title' => 'Введите Ваш пароль еще раз', 'size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите пароль...')))
                ->add('fieldEmail', 'text', array('label'=>'Email:', 'required' => true, 'data' => $options['data']->fieldEmail, 'attr' => array('title' => 'Введите Ваш email', 'size' => 20, 'maxlength' => 25, 'placeholder' => 'Введите Email...')))
                ->add('checkAgreeRules', 'checkbox', array('label'=>' ', 'required' => true, 'attr' => array('title' => 'Если Вы согласны с правилами, то установите тут флажок', 'class' => '')))
                ->add('reg', 'submit', array('label'=>'Зарегистрироваться', 'attr' => array('class' => 'btn btn-success')))
                ->add('reset', 'reset', array('label'=>'Очистить', 'attr' => array('class' => 'btn btn-success')));

        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $event)
        {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data->getPassword() !== null && $data->getApprovePassword() != null)
            {
                $newPassword = $form->get('fieldPass')->getData();
                $approvePassword = $form->get('fieldPassApprove')->getData();

                if (!StringUtils::equals($newPassword, $approvePassword))
                {
                    $form->get('fieldPassApprove')->addError(new FormError('Введенные пароли не совпадают!'));
                    $form->get('fieldPass')->addError(new FormError('Введенные пароли не совпадают!'));
                }
            }

            if ($data->getLogin() !== null)
            {
                $newLogin = $form->get('fieldLogin')->getData();

                if(Helper::isExistsUserLogin($newLogin))
                {
                    $form->get('fieldLogin')->addError(new FormError('Такой логин уже используется!'));
                }
            }

            if ($data->getEmail() !== null)
            {
                $newEmail = $form->get('fieldEmail')->getData();

                if(Helper::isExistsUserEmail($newEmail))
                {
                    $form->get('fieldEmail')->addError(new FormError('Такой Email уже используется!'));
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
        /*$resolver->setDefaults(array(
            'data_class'      => 'Acme\AuthBundle\Entity\ClientFormValidate',
            'data_class'      => 'Acme\AuthBundle\Entity\User',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'task_item',
        ));*/
    }
}
