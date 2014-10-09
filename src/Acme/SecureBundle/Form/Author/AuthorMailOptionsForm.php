<?php

namespace Acme\SecureBundle\Form\Author;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class AuthorMailOptionsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fieldOptions', 'choice', array(
                'label' => 'Присылать на почту',
                'data' => $options['data'],
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'choices' => $this->buildChoices(),
                'invalid_message' => 'Ошибка!'
               // 'error_bubbling' => true
            ))
            ->add('save', 'submit', array('attr' => array('class' => 'hidden')));
    }

    protected function buildChoices() {
        $choices = [];
        $choices['no'] = 'Новые заказы';
        $choices['cr'] = 'Ответы в чате';
        return $choices;
    }

    public function getName() {
        return 'formMailOptions';
    }
}
