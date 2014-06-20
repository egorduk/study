<?php

namespace Acme\AuthBundle\Form;

use Helper\Helper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Util\StringUtils;
use Doctrine\ORM\EntityRepository;


class AuthorRegForm extends AbstractType
{
    private static $kernel;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldLogin', 'text', array('label'=>'Логин', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Ваш логин должен состоять только из латинских букв', 'size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите логин...')))
                ->add('fieldPass', 'password', array('label'=>'Пароль', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Ваш пароль должен состоять только из латинских букв и цифр', 'size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите пароль...')))
                ->add('fieldPassApprove', 'password', array('label'=>'Подтвердите пароль', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Введите Ваш пароль еще раз', 'size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите повторно пароль...')))
                ->add('fieldEmail', 'text', array('label'=>'Email', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Введите Ваш email', 'size' => 20, 'maxlength' => 25, 'placeholder' => 'Введите Email...')))
                ->add('fieldMobilePhone', 'text', array('label'=>'Мобильный телефон', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Введите номер Вашего мобильного телефона', 'size' => 22, 'maxlength' => 14, 'placeholder' => 'Введите номер...')))
                ->add('fieldSkype', 'text', array('label'=>'Skype', 'required' => false, 'attr' => array('class' => 'form-control', 'title' => 'Введите Ваш Skype', 'size' => 22, 'maxlength' => 20, 'placeholder' => 'Введите Skype...')))
                ->add('fieldIcq', 'text', array('label'=>'Icq', 'required' => false, 'attr' => array('class' => 'form-control', 'title' => 'Введите Ваш Icq', 'size' => 20, 'maxlength' => 10, 'placeholder' => 'Введите Icq...')))
                ->add('checkAgreeRules', 'checkbox', array('label'=>' ', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Если Вы согласны с правилами, то установите тут флажок', 'class' => '')))
                ->add('reg', 'submit', array('label'=>'Регистрация', 'attr' => array('class' => 'hidden')))
                ->add('reset', 'reset', array('label'=>'Очистить', 'attr' => array('class' => 'hidden')))
                ->add('fieldUsername', 'text', array('label'=>'Имя', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Ваше имя должно состоять только из русских букв', 'size' => 30, 'maxlength' => 20, 'placeholder' => 'Введите имя...')))
                ->add('fieldSurname', 'text', array('label'=>'Фамилия', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Ваша фамилия должна состоять только из русских букв', 'size' => 30, 'maxlength' => 20, 'placeholder' => 'Введите фамилию...')))
                ->add('fieldLastname', 'text', array('label'=>'Отчество', 'required' => true, 'attr' => array('class' => 'form-control', 'title' => 'Ваше отчество должно состоять только из русских букв', 'size' => 30, 'maxlength' => 20, 'placeholder' => 'Введите отчество...')))
               /* ->add('selectorCountry', 'genemu_jqueryselect2_entity', array(
                'mapped'   => false,
                'class' => 'Acme\AuthBundle\Entity\Country',
                'property' => 'code'
            ))*/
            ->add('selectorCountry', 'choice', array(
                'mapped'   => false,
                'choices' => $this->buildChoices(),
            ));

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

                if (Helper::isExistsUserByLogin($newLogin))
                {
                    $form->get('fieldLogin')->addError(new FormError('Такой логин уже используется!'));
                }
            }

            if ($data->getEmail() !== null)
            {
                $newEmail = $form->get('fieldEmail')->getData();

                if(Helper::isExistsUserByEmail($newEmail))
                {
                    $form->get('fieldEmail')->addError(new FormError('Такой Email уже используется!'));
                }
            }
        });
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

    public function getName()
    {
        return 'formReg';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        /*$resolver->setDefaults(array(
            'data_class'      => 'Acme\AuthBundle\Entity\AuthorFormValidate',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'task_item',
        ));*/
    }
}
