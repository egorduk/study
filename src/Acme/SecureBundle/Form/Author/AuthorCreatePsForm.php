<?php

namespace Acme\SecureBundle\Form\Author;

use Helper\Helper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Util\StringUtils;


class AuthorCreatePsForm extends AbstractType
{
    private static $kernel;
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fieldNum', 'text', array('label' => 'Номер кошелька', 'required' => true, 'attr' => array('class' => 'form-control', 'size' => 20, 'maxlength' => 12, 'placeholder' => 'Введите номер кошелька...')))
                ->add('add', 'submit', array('attr' => array('class' => 'hidden')))
                ->add('reset', 'reset', array('attr' => array('class' => 'hidden')));
               /* ->add('selectorCountry', 'genemu_jqueryselect2_entity', array(
                'mapped'   => false,
                'class' => 'Acme\AuthBundle\Entity\Country',
                'property' => 'code'
            ))*/
            /*->add('selectorCountry', 'choice', array(
                'mapped'   => false,
                'choices' => $this->buildChoices(),
            ))*/;

        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $event) {
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

    protected function buildChoices() {
        /*$container = Helper::getContainer();
        $choices = [];
        $table2Repository = $container->get('doctrine')->getRepository('Acme\AuthBundle\Entity\Country');
        $table2Objects = $table2Repository->findAll();

        foreach ($table2Objects as $table2Obj) {
            $choices[$table2Obj->getCode()] = $table2Obj->getName();
        }

        return $choices;*/
    }

    public function getName() {
        return 'formCreatePs';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        /*$resolver->setDefaults(array(
            'data_class'      => 'Acme\AuthBundle\Entity\AuthorFormValidate',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'task_item',
        ));*/
    }
}
