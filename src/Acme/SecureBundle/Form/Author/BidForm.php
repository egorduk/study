<?php

namespace Acme\SecureBundle\Form\Author;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class BidForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldSum', 'text', array('required' => true, 'label'=>'Сумма', 'data' => $options['data']->fieldSum, 'attr' => array('class' => 'form-control', 'maxlength' => 6, 'placeholder' => 'Введите сумму', 'data-toggle' => 'tooltip','data-placement' => 'top', 'data-trigger'=> 'manual', 'title' => 'Только числа!')))
                ->add('fieldDay', 'text', array('required' => true, 'label'=>'Количество дней', 'data' => $options['data']->fieldDay, 'attr' => array('class' => 'form-control', 'maxlength' => 3, 'placeholder' => 'Введите количество дней', 'data-toggle' => 'tooltip','data-placement' => 'top', 'data-trigger'=> 'manual', 'title' => 'Только числа!')))
                ->add('fieldComment', 'text', array('required' => false, 'label'=>'Комментарий', 'data' => $options['data']->fieldComment, 'attr' => array('class' => 'form-control', 'title' => 'Введите комментарий для заказчика', 'size' => 30, 'maxlength' => 50, 'placeholder' => 'Введите комментарий')))
                ->add('isClientDate', 'checkbox', array('label'=>'В срок заказчика', 'data' => /*boolval*/filter_var($options['data']->is_client_date, FILTER_VALIDATE_BOOLEAN), 'required' => false, 'attr' => array('class' => '', 'title' => 'Установите флажок, если Вы согласны выполнить в указанный срок заказчика')))
                ->add('bid', 'button', array('label'=>'', 'attr' => array('class' => 'hidden')));
                //->add('reset', 'reset', array('label'=>'', 'attr' => array('class' => 'hidden')));
    }

    public function getName()
    {
        return 'formBid';
    }
}
