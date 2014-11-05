<?php

namespace Acme\SecureBundle\Form\Author;

use Helper\Helper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class OutputPsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fieldSum', 'text', array('label' => 'Сумма', 'required' => true, 'attr' => array('class' => 'form-control', 'size' => 20, 'maxlength' => 7, 'placeholder' => 'Введите сумму')))
            ->add('fieldComment', 'text', array('label' => 'Комментарий', 'required' => false, 'attr' => array('class' => 'form-control', 'size' => 20, 'maxlength' => 50, 'placeholder' => 'Введите комментарий')))
            ->add('fieldType', 'choice', array(
                'label' => 'Кошелек',
                'mapped' => false,
                'required' => true,
                'choices' => $this->buildChoices(),
                'invalid_message' => 'Ошибка!'
            ))
            //->add('fieldHiddenPsId', 'hidden', array('attr' => array('class' => 'hidden hidden-ps-id')))
            ->add('output', 'button', array('attr' => array('class' => 'hidden')));
    }

    protected function buildChoices() {
        $container = Helper::getContainer();
        $choices = [];
        $userId = '2';
        $em = $container->get('doctrine')->getManager();
        $userPs = $em->getRepository('Acme\SecureBundle\Entity\UserPs')->findByUser($userId);
        foreach ($userPs as $ps) {
            $name = $ps->getName();
            $num = $ps->getNum();
            $type = $ps->getType()->getCode();
            $strName = $name . '|';
            $strType = '|' . $type;
            $choices[$ps->getId()] = (!empty($name) ? $strName : '') . $num . $strType;
        }
        return $choices;
    }

    public function getName() {
        return 'formOutputPs';
    }
}
