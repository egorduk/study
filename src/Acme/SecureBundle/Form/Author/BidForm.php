<?php

namespace Acme\SecureBundle\Form\Author;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class BidForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldSum', 'text', array('required' => true, 'label'=>'Сумма:', 'data' => $options['data']->fieldSum, 'attr' => array('class' => 'form-control', 'title' => 'Введите сумму за выполнение заказа', 'size' => 30, 'maxlength' => 6, 'placeholder' => 'Введите сумму')))
                ->add('fieldDay', 'text', array('required' => true, 'label'=>'Количество дней:', 'data' => $options['data']->fieldDay, 'attr' => array('class' => 'form-control', 'title' => 'Введите количество дней на выполнение заказа', 'size' => 30, 'maxlength' => 3, 'placeholder' => 'Введите количество дней')))
                ->add('fieldComment', 'text', array('required' => false, 'label'=>'Комментарий:', 'data' => $options['data']->fieldComment, 'attr' => array('class' => 'form-control', 'title' => 'Введите комментарий для заказчика', 'size' => 30, 'maxlength' => 100, 'placeholder' => 'Введите комментарий')))
                ->add('isClientDate', 'checkbox', array('label'=>'В срок', 'data' => boolval($options['data']->is_client_date), 'required' => false, 'attr' => array('class' => '', 'title' => 'Установите флажок, если Вы согласны со сроком заказчика')))
                ->add('bid', 'button', array('label'=>'', 'attr' => array('class' => 'hidden')));
                //->add('reset', 'reset', array('label'=>'', 'attr' => array('class' => 'hidden')));
    }

    public function getName()
    {
        return 'formBid';
    }
}
