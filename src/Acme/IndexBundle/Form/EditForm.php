<?php

namespace Acme\RssBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Tests\Fixtures\Entity;


class EditForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fieldName', 'text', array('label'=>'Name:', 'required' => true, 'attr' => array('size' => 30), 'data' => $options['data']['name']))
                ->add('fieldUrl', 'text', array('label'=>'Url:', 'required' => true, 'attr' => array('size' => 50), 'data' => $options['data']['url']))
                ->add('fieldSourceId', 'hidden', array('data' => $options['data']['sourceId']))
                ->add('Edit', 'submit', array(
                'attr' => array('class' => 'btn btn-success')));
    }

    public function getName()
    {
        return 'formEdit';
    }
}
