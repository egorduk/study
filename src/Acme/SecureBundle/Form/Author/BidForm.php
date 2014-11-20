<?php

namespace Acme\SecureBundle\Form\Author;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class BidForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fieldSum', 'text', array('required' => true, 'label'=>'Сумма'/*, 'data' => $options['data']->fieldSum*/, 'attr' => array('class' => 'form-control', 'maxlength' => 7, 'placeholder' => 'Введите сумму', 'data-toggle' => 'tooltip','data-placement' => 'top', 'data-trigger'=> 'manual', 'title' => 'Только числа!')))
                ->add('fieldDay', 'text', array('required' => true, 'label'=>'Количество дней',/* 'data' => $options['data']->fieldDay, */'attr' => array('class' => 'form-control', 'maxlength' => 3, 'placeholder' => 'Введите количество дней', 'data-toggle' => 'tooltip','data-placement' => 'top', 'data-trigger'=> 'manual', 'title' => 'Только числа!')))
                ->add('fieldComment', 'textarea', array('required' => false, 'label'=>'Комментарий',/* 'data' => $options['data']->fieldComment,*/ 'attr' => array('class' => 'form-control', 'title' => 'Введите комментарий для заказчика', 'size' => 30, 'maxlength' => 150, 'placeholder' => 'Введите комментарий')))
                ->add('isClientDate', 'checkbox', array('label'=>'В срок заказчика',/* 'data' => filter_var($options['data']->is_client_date, FILTER_VALIDATE_BOOLEAN),*/ 'required' => false, 'attr' => array('class' => '', 'title' => 'Установите флажок, если Вы согласны выполнить заказ в срок, указанный заказчиком')))
                ->add('bid', 'button', array('label'=>'', 'attr' => array('class' => 'hidden')))
                ->add('cancel', 'button', array('label'=>'', 'attr' => array('class' => 'hidden')));

        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $event) {
            $form = $event->getForm();
            $fieldIsClientDate = $form->get('isClientDate')->getData();
            $fieldDay = $form->get('fieldDay')->getData();
            if (isset($fieldIsClientDate) && $fieldIsClientDate != null && $fieldDay != null && is_numeric($fieldDay)) {
                $form->get('fieldDay')->addError(new FormError('Ошибка!'));
            }
        });
    }

    public function getName() {
        return 'formBid';
    }
}
