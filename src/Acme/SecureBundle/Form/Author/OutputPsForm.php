<?php

namespace Acme\SecureBundle\Form\Author;

use Helper\Helper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class OutputPsForm extends AbstractType
{
    public $countUserPs = 0;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fieldSum', 'text', array('label' => 'Сумма', 'required' => true, 'attr' => array('class' => 'form-control', 'size' => 20, 'maxlength' => 7, 'placeholder' => 'Введите сумму')))
            ->add('fieldComment', 'text', array('label' => 'Комментарий', 'required' => false, 'attr' => array('class' => 'form-control', 'size' => 20, 'maxlength' => 50, 'placeholder' => 'Введите комментарий')))
            ->add('fieldType', 'choice', array(
                'label' => 'Номер кошелька',
                'mapped' => false,
                'required' => true,
                'choices' => $this->buildChoices($options),
                'invalid_message' => 'Ошибка!'
            ))
            ->add('output', 'button', array('attr' => array('class' => 'hidden')));
    }

    protected function buildChoices($options) {
        $container = Helper::getContainer();
        $choices = [];
        $user = $options['data']->getUser();
        $em = $container->get('doctrine')->getManager();
        $userPs = $em->getRepository('Acme\SecureBundle\Entity\UserPs')->findByUser($user);
        $this->countUserPs = count($userPs);
        foreach ($userPs as $ps) {
            $name = $ps->getName();
            $num = $ps->getNum();
            $type = $ps->getTypePs()->getCode();
            $choices[$ps->getId()] = $name . '|' . $num . '|' . $type;
        }
        return $choices;
    }

    public function getName() {
        return 'formOutputPs';
    }

    public function getCountUserPs() {
        return $this->countUserPs;
    }
}
