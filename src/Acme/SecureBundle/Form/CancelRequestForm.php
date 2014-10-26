<?php

namespace Acme\SecureBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class CancelRequestForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fieldComment', 'textarea', array('label' => 'Комментарий', 'required' => false, 'attr' => array('class' => 'form-control', 'maxlength' => 255, 'placeholder' => 'Введите комментарий')))
            ->add('fieldIsTogetherApply', 'checkbox', array('label' => 'По обоюдному согласию с заказчиком ', 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('fieldPercent', 'choice', array(
                'label' => 'Оценка выполненной работы',
                'mapped' => false,
                'required' => false,
                'choices' => $this->buildChoices(),
                'invalid_message' => 'Ошибка!',
                //'constraints' => new NotBlank(array('message' => 'Ошибка!')),
                'empty_value' => ''
            ))
            ->add('create', 'button', array('attr' => array('class' => 'hidden')));
           // ->add('cancel', 'button', array('attr' => array('class' => 'hidden')));

        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $event) {
            $form = $event->getForm();
            $fieldPercent = $form->get('fieldPercent')->getData();
            $fieldIsTogetherApply = $form->get('fieldIsTogetherApply')->getData();
            if (($fieldIsTogetherApply && !empty($fieldPercent)) || (!$fieldIsTogetherApply && empty($fieldPercent))) {
                $form->get('fieldPercent')->addError(new FormError('Ошибка!'));
            }
        });
    }

    protected function buildChoices() {
        $choices = [];
        for ($i = 0; $i < 110; $i+=10) {
            $choices[$i] = $i . '%';
        }
        return $choices;
    }

    public function getName() {
        return 'formCancelRequest';
    }
}
