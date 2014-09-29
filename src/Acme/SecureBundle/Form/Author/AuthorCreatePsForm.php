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
                ->add('fieldType', 'choice', array(
                    'label' => 'Тип системы: ',
                    //'data' => $options['data']->selectorCountry,
                    'mapped' => false,
                    'choices' => $this->buildChoices()
                ))
                ->add('add', 'submit', array('attr' => array('class' => 'hidden')))
                ->add('reset', 'reset', array('attr' => array('class' => 'hidden')));

        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
           // var_dump($data);die;
            if (empty($data->getType())) {
                $form->get('fieldType')->addError(new FormError('Ошибка!'));
            }
        });
    }

    protected function buildChoices() {
        $container = Helper::getContainer();
        $choices = [];
        $table2Repository = $container->get('doctrine')->getRepository('Acme\SecureBundle\Entity\TypePs');
        $table2Objects = $table2Repository->findAll();
        foreach ($table2Objects as $table2Obj) {
            $choices[$table2Obj->getCode()] = $table2Obj->getName();
        }
        return $choices;
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
