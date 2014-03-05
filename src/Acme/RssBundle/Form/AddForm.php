<?php

namespace Acme\RssBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Tests\Fixtures\Entity;


class AddForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldName', 'text', array('label'=>'Name:', 'required' => true, 'attr' => array('size' => 30)))
                ->add('fieldUrl', 'text', array('label'=>'Url:', 'required' => true, 'attr' => array('size' => 50)))
                ->add('Add', 'submit', array(
                'attr' => array('class' => 'btn btn-success')));
    }

    public function getName()
    {
        return 'formAdd';
    }
}
