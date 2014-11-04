<?php

namespace Acme\SecureBundle\Form\Author;

use Helper\Helper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class AuthorCreatePsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fieldNum', 'text', array('label' => 'Номер кошелька', 'required' => true, 'attr' => array('class' => 'form-control', 'size' => 20, 'maxlength' => 20, 'placeholder' => 'Введите номер кошелька')))
            ->add('fieldName', 'text', array('label' => 'Название кошелька', 'required' => false, 'attr' => array('class' => 'form-control', 'size' => 20, 'maxlength' => 20, 'placeholder' => 'Введите название кошелька')))
            ->add('fieldType', 'choice', array(
                'label' => 'Тип системы',
                'mapped' => false,
                'required' => true,
                'choices' => $this->buildChoices(),
                'invalid_message' => 'Ошибка!'
                //'expanded' => true
            ))
            ->add('fieldHiddenPsId', 'hidden', array('attr' => array('class' => 'hidden hidden-ps-id')))
            ->add('add', 'submit', array('attr' => array('class' => 'hidden')))
            ->add('change', 'submit', array('attr' => array('class' => 'hidden')))
            ->add('reset', 'reset', array('attr' => array('class' => 'hidden')));
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
}
